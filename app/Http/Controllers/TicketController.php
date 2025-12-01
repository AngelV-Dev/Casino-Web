<?php
// ============================================================
// ARCHIVO 1: app/Http/Controllers/TicketController.php
// (Para usuarios normales)
// ============================================================

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TicketController extends Controller
{
    /**
     * Ver mis tickets (usuario normal)
     */
    public function index()
    {
        $tickets = Auth::user()->tickets()
            ->with(['replies.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'tickets' => $tickets,
        ]);
    }

    /**
     * Crear nuevo ticket
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        $ticket = Ticket::create([
            'user_id' => Auth::id(),
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'open',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket creado exitosamente',
            'ticket' => $ticket->load(['replies.user']),
        ], 201);
    }

    /**
     * Ver un ticket específico
     */
    public function show(Ticket $ticket)
    {
        // Verificar que el usuario sea dueño del ticket o tenga permisos
        if ($ticket->user_id !== Auth::id() && !Auth::user()->hasAnyRole(['super_admin', 'admin', 'moderator', 'support'])) {
            abort(403, 'No tienes permiso para ver este ticket');
        }

        return response()->json([
            'ticket' => $ticket->load(['user', 'replies.user']),
        ]);
    }

    /**
     * Responder a un ticket (usuario puede responder a su propio ticket)
     */
    public function reply(Request $request, Ticket $ticket)
    {
        // Verificar que el usuario sea dueño del ticket o tenga permisos
        if ($ticket->user_id !== Auth::id() && !Auth::user()->hasAnyRole(['super_admin', 'admin', 'moderator', 'support'])) {
            abort(403, 'No tienes permiso para responder a este ticket');
        }

        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);

        // Si el ticket estaba cerrado, reabrirlo
        if ($ticket->isClosed()) {
            $ticket->reopen();
        }

        return response()->json([
            'success' => true,
            'message' => 'Respuesta enviada exitosamente',
            'reply' => $reply->load('user'),
        ]);
    }

    /**
     * Cerrar ticket (solo el dueño puede cerrar)
     */
    public function close(Ticket $ticket)
    {
        if ($ticket->user_id !== Auth::id() && !Auth::user()->hasAnyRole(['super_admin', 'admin', 'moderator', 'support'])) {
            abort(403, 'No tienes permiso para cerrar este ticket');
        }

        $ticket->close();

        return response()->json([
            'success' => true,
            'message' => 'Ticket cerrado exitosamente',
        ]);
    }
}
