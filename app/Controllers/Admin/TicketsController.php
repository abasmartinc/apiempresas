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

        return view('admin/tickets/index', [
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

        return view('admin/tickets/show', ['ticket' => $ticket, 'replies' => $replies]);
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

        $this->ticketReplyModel->insert([
            'ticket_id' => $id,
            'user_id' => $adminId,
            'is_admin' => 1,
            'message' => $message,
        ]);

        $this->ticketModel->update($id, ['status' => 'answered']);

        return redirect()->back()->with('success', 'Respuesta enviada.');
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
            return redirect()->back()->with('success', 'Ticket actualizado correctamente.');
        }

        return redirect()->back()->with('error', 'Datos no válidos.');
    }
}
