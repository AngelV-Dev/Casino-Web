<?php

// ============================================================
// ARCHIVO 2: app/Http/Controllers/Admin/TicketManagementController.php
// (Para staff: admin, moderator, support)
// ============================================================

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TicketManagementController extends Controller
{
    /**
     * Ver todos los tickets (staff)
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['user', 'replies.user']);

        // Filtros
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('subject', 'like', "%{$request->search}%")
                  ->orWhere('id', $request->search)
                  ->orWhereHas('user', function($q2) use ($request) {
                      $q2->where('name', 'like', "%{$request->search}%");
                  });
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'tickets' => $tickets,
        ]);
    }

    /**
     * Ver ticket específico (staff)
     */
    public function show(Ticket $ticket)
    {
        return response()->json([
            'ticket' => $ticket->load(['user', 'replies.user']),
        ]);
    }

    /**
     * Responder ticket (staff)
     */
    public function reply(Request $request, Ticket $ticket)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);

        // Marcar como "en progreso" si está abierto
        if ($ticket->isOpen()) {
            $ticket->markInProgress();
        }

        return response()->json([
            'success' => true,
            'message' => 'Respuesta enviada exitosamente',
            'reply' => $reply->load('user'),
        ]);
    }

    /**
     * Cambiar estado del ticket (staff)
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,closed',
        ]);

        $ticket->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado exitosamente',
        ]);
    }

    /**
     * Eliminar ticket (solo admin/super_admin)
     */
    public function destroy(Ticket $ticket)
    {
        if (!Auth::user()->hasAnyRole(['super_admin', 'admin'])) {
            abort(403, 'No tienes permiso para eliminar tickets');
        }

        $ticket->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ticket eliminado exitosamente',
        ]);
    }

    /**
     * Estadísticas de tickets (para dashboard)
     */
    public function statistics()
    {
        return response()->json([
            'total' => Ticket::count(),
            'open' => Ticket::where('status', 'open')->count(),
            'in_progress' => Ticket::where('status', 'in_progress')->count(),
            'closed' => Ticket::where('status', 'closed')->count(),
        ]);
    }
}
