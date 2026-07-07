<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TicketModel;
use App\Models\TicketReplyModel;

class Tickets extends BaseController
{
    protected $ticketModel;
    protected $ticketReplyModel;

    public function __construct()
    {
        $this->ticketModel = new TicketModel();
        $this->ticketReplyModel = new TicketReplyModel();
    }

    public function index()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/enter');
        }

        $tickets = $this->ticketModel->where('user_id', $userId)->orderBy('updated_at', 'DESC')->findAll();

        return $this->renderView('tickets/index', ['tickets' => $tickets]);
    }

    public function create()
    {
        if (!session()->get('user_id')) {
            return redirect()->to('/enter');
        }

        return $this->renderView('tickets/create');
    }

    public function store()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/enter');
        }

        $subject = $this->request->getPost('subject');
        $category = $this->request->getPost('category') ?: 'general';
        $priority = $this->request->getPost('priority');
        $message = $this->request->getPost('message');

        if (empty($subject) || empty($message)) {
            return redirect()->back()->with('error', 'El asunto y el mensaje son obligatorios.');
        }

        $ticketId = $this->ticketModel->insert([
            'user_id' => $userId,
            'subject' => $subject,
            'category' => $category,
            'status' => 'open',
            'priority' => in_array($priority, ['low', 'medium', 'high', 'urgent']) ? $priority : 'medium',
        ]);

        if ($ticketId) {
            $attachmentPath = null;
            $file = $this->request->getFile('attachment');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move(FCPATH . 'uploads/tickets', $newName);
                $attachmentPath = 'uploads/tickets/' . $newName;
            }

            $this->ticketReplyModel->insert([
                'ticket_id' => $ticketId,
                'user_id' => $userId,
                'is_admin' => 0,
                'message' => $message,
                'attachment' => $attachmentPath
            ]);

            // Notificar por correo al administrador
            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($userId);
            $userName = $user ? $user->name : 'Usuario Desconocido';
            $userEmail = $user ? $user->email : 'Sin email';

            $emailService = \Config\Services::email();
            $emailService->setFrom('no-reply@apiempresas.es', 'APIEmpresas Soporte');
            $emailService->setTo('soporte@apiempresas.es');
            $emailService->setSubject('Nuevo Ticket #'. $ticketId . ' - ' . $subject);
            
            $body = "<h2>Nuevo ticket de soporte (#$ticketId)</h2>";
            $body .= "<p><strong>Usuario:</strong> $userName ($userEmail)</p>";
            $body .= "<p><strong>Asunto:</strong> $subject</p>";
            $body .= "<p><strong>Prioridad:</strong> $priority</p>";
            $body .= "<p><strong>Categoría:</strong> $category</p>";
            $body .= "<h3>Mensaje:</h3>";
            $body .= "<p>".nl2br(esc($message))."</p>";
            $body .= "<hr><p><a href='".site_url('admin/tickets/'.$ticketId)."'>Ver Ticket en el Panel de Administración</a></p>";
            
            $emailService->setMessage($body);
            $emailService->setMailType('html');
            $emailService->send();

            return redirect()->to('/tickets/' . $ticketId)->with('success', 'Ticket creado correctamente.');
        }

        return redirect()->back()->with('error', 'Hubo un problema al crear el ticket.');
    }

    public function show($id)
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/enter');
        }

        $ticket = $this->ticketModel->where('id', $id)->where('user_id', $userId)->first();
        
        if (!$ticket) {
            return redirect()->to('/tickets')->with('error', 'Ticket no encontrado.');
        }

        $replies = $this->ticketReplyModel->getRepliesWithSender($id);

        return $this->renderView('tickets/show', ['ticket' => $ticket, 'replies' => $replies]);
    }

    public function reply($id)
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/enter');
        }

        $ticket = $this->ticketModel->where('id', $id)->where('user_id', $userId)->first();
        
        if (!$ticket) {
            return redirect()->to('/tickets')->with('error', 'Ticket no encontrado.');
        }

        if ($ticket['status'] === 'closed') {
            return redirect()->back()->with('error', 'No puedes responder a un ticket cerrado.');
        }

        $message = $this->request->getPost('message');

        if (empty($message)) {
            return redirect()->back()->with('error', 'El mensaje no puede estar vacío.');
        }

        $attachmentPath = null;
        $file = $this->request->getFile('attachment');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/tickets', $newName);
            $attachmentPath = 'uploads/tickets/' . $newName;
        }

        $this->ticketReplyModel->insert([
            'ticket_id' => $id,
            'user_id' => $userId,
            'is_admin' => 0,
            'message' => $message,
            'attachment' => $attachmentPath
        ]);

        $this->ticketModel->update($id, ['status' => 'open']);

        // Notificar al admin sobre la respuesta
        $emailService = \Config\Services::email();
        $emailService->setFrom('no-reply@apiempresas.es', 'APIEmpresas Sistema');
        $emailService->setTo('soporte@apiempresas.es'); // Or an env variable
        $emailService->setSubject('Nueva respuesta en Ticket #' . $id);
        
        $body = "<h2>El usuario ha respondido al ticket (#$id)</h2>";
        $body .= "<p><strong>Asunto:</strong> " . esc($ticket['subject']) . "</p>";
        $body .= "<h3>Mensaje del usuario:</h3>";
        $body .= "<p>".nl2br(esc($message))."</p>";
        $body .= "<hr><p><a href='".site_url('admin/tickets/'.$id)."'>Ver Ticket en el Panel de Admin</a></p>";
        
        $emailService->setMessage($body);
        $emailService->setMailType('html');
        $emailService->send();

        return redirect()->back()->with('success', 'Respuesta enviada.');
    }

    public function close($id)
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/enter');
        }

        $ticket = $this->ticketModel->where('id', $id)->where('user_id', $userId)->first();
        
        if (!$ticket) {
            return redirect()->to('/tickets')->with('error', 'Ticket no encontrado.');
        }

        $this->ticketModel->update($id, ['status' => 'closed']);

        return redirect()->back()->with('success', 'Ticket cerrado correctamente.');
    }

    public function rate($id)
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/enter');
        }

        $ticket = $this->ticketModel->where('id', $id)->where('user_id', $userId)->first();
        
        if (!$ticket) {
            return redirect()->to('/tickets')->with('error', 'Ticket no encontrado.');
        }

        $rating = $this->request->getPost('rating');

        if ($rating >= 1 && $rating <= 5) {
            $this->ticketModel->update($id, ['rating' => $rating]);
            return redirect()->back()->with('success', '¡Gracias por tu valoración!');
        }

        return redirect()->back()->with('error', 'Valoración no válida.');
    }

    public function updatePriority($id)
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/enter');
        }

        $ticket = $this->ticketModel->where('id', $id)->where('user_id', $userId)->first();
        
        if (!$ticket) {
            return redirect()->to('/tickets')->with('error', 'Ticket no encontrado.');
        }

        if ($ticket['status'] === 'closed') {
            return redirect()->back()->with('error', 'No puedes cambiar la prioridad de un ticket cerrado.');
        }

        $priority = $this->request->getPost('priority');
        if (in_array($priority, ['low', 'medium', 'high', 'urgent'])) {
            $this->ticketModel->update($id, ['priority' => $priority]);
            return redirect()->back()->with('success', 'Prioridad actualizada correctamente.');
        }

        return redirect()->back()->with('error', 'Prioridad no válida.');
    }
}
