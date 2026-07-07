<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TicketModel;
use App\Models\TicketReplyModel;

class TicketsController extends BaseController
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
        $statusFilter = $this->request->getGet('status');
        
        if ($statusFilter && $statusFilter !== 'all') {
            $tickets = $this->ticketModel->getTicketsWithUsers($statusFilter);
        } else {
            $tickets = $this->ticketModel->getTicketsWithUsers();
        }

        // KPIs
        $kpis = [
            'total' => $this->ticketModel->countAllResults(),
            'open' => $this->ticketModel->where('status', 'open')->countAllResults(),
            'in_progress' => $this->ticketModel->where('status', 'in_progress')->countAllResults(),
            'answered' => $this->ticketModel->where('status', 'answered')->countAllResults(),
            'closed' => $this->ticketModel->where('status', 'closed')->countAllResults(),
        ];

        return $this->renderView('admin/tickets/index', [
            'tickets' => $tickets, 
            'statusFilter' => $statusFilter,
            'kpis' => $kpis
        ]);
    }

    public function show($id)
    {
        $ticket = $this->ticketModel->select('tickets.*, users.name as user_name, users.email as user_email')
                                    ->join('users', 'users.id = tickets.user_id', 'left')
                                    ->where('tickets.id', $id)
                                    ->first();

        if (!$ticket) {
            return redirect()->to('/admin/tickets')->with('error', 'Ticket no encontrado.');
        }

        $replies = $this->ticketReplyModel->getRepliesWithSender($id);

        return $this->renderView('admin/tickets/show', ['ticket' => $ticket, 'replies' => $replies]);
    }

    public function reply($id)
    {
        $adminId = session()->get('user_id'); // Assuming admin is logged in with this session key
        
        $ticket = $this->ticketModel->find($id);
        
        if (!$ticket) {
            return redirect()->to('/admin/tickets')->with('error', 'Ticket no encontrado.');
        }

        $message = $this->request->getPost('message');

        if (empty($message)) {
            return redirect()->back()->with('error', 'El mensaje no puede estar vacío.');
        }

        $isPrivate = $this->request->getPost('is_private') ? 1 : 0;

        $attachmentPath = null;
        $file = $this->request->getFile('attachment');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/tickets', $newName);
            $attachmentPath = 'uploads/tickets/' . $newName;
        }

        $this->ticketReplyModel->insert([
            'ticket_id' => $id,
            'user_id' => $adminId,
            'is_admin' => 1,
            'message' => $message,
            'is_private' => $isPrivate,
            'attachment' => $attachmentPath
        ]);

        if (!$isPrivate) {
            $this->ticketModel->update($id, ['status' => 'answered']);
            
            // Send email to user
            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($ticket['user_id']);
            if ($user && !empty($user->email)) {
                $emailService = \Config\Services::email();
                $emailService->setFrom('no-reply@apiempresas.es', 'APIEmpresas Soporte');
                $emailService->setTo($user->email);
                $emailService->setSubject('Respuesta a tu ticket #' . $id);
                
                $body = "<h2>Soporte Técnico ha respondido a tu ticket (#$id)</h2>";
                $body .= "<p><strong>Asunto:</strong> " . esc($ticket['subject']) . "</p>";
                $body .= "<h3>Mensaje:</h3>";
                $body .= "<p>".nl2br(esc($message))."</p>";
                $body .= "<hr><p><a href='".site_url('tickets/'.$id)."'>Ver Ticket en tu Panel</a></p>";
                
                $emailService->setMessage($body);
                $emailService->setMailType('html');
                $emailService->send();
            }
        }

        return redirect()->back()->with('success', $isPrivate ? 'Nota interna guardada.' : 'Respuesta enviada.');
    }

    public function updateStatus($id)
    {
        $ticket = $this->ticketModel->find($id);
        
        if (!$ticket) {
            return redirect()->to('/admin/tickets')->with('error', 'Ticket no encontrado.');
        }

        $status = $this->request->getPost('status');
        $priority = $this->request->getPost('priority');

        $updateData = [];
        if (in_array($status, ['open', 'in_progress', 'answered', 'closed'])) {
            $updateData['status'] = $status;
        }
        if (in_array($priority, ['low', 'medium', 'high', 'urgent'])) {
            $updateData['priority'] = $priority;
        }

        if (!empty($updateData)) {
            $this->ticketModel->update($id, $updateData);

            if ($status === 'closed' && $ticket['status'] !== 'closed') {
                // Notificar al usuario que su ticket se ha cerrado
                $userModel = new \App\Models\UserModel();
                $user = $userModel->find($ticket['user_id']);
                if ($user && !empty($user->email)) {
                    $emailService = \Config\Services::email();
                    $emailService->setFrom('no-reply@apiempresas.es', 'APIEmpresas Soporte');
                    $emailService->setTo($user->email);
                    $emailService->setSubject('Tu ticket #' . $id . ' ha sido cerrado');
                    
                    $body = "<h2>Ticket Cerrado (#$id)</h2>";
                    $body .= "<p>Tu ticket con asunto <strong>" . esc($ticket['subject']) . "</strong> ha sido marcado como cerrado.</p>";
                    $body .= "<hr><p>Puedes valorar la atención recibida y ver los detalles haciendo clic aquí: <br><a href='".site_url('tickets/'.$id)."'>Ver Ticket y Valorar</a></p>";
                    
                    $emailService->setMessage($body);
                    $emailService->setMailType('html');
                    $emailService->send();
                }
            }

            return redirect()->back()->with('success', 'Ticket actualizado correctamente.');
        }

        return redirect()->back()->with('error', 'Datos no válidos.');
    }
}
