<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // <--- IMPORTANTE: Agregado para transacciones
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
        // Bloque de seguridad para atrapar el error
        try {
            $request->validate([
                'subject' => 'required|string|max:255',
                'message' => 'required|string|max:2000',
            ]);

            return DB::transaction(function () use ($request) {
                // 1. Crear Ticket
                $ticket = Ticket::create([
                    'user_id' => Auth::id(),
                    'subject' => $request->subject,
                    'message' => $request->message,
                    'status' => 'open',
                ]);

                // 2. Crear Respuesta
                TicketReply::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => Auth::id(),
                    'message' => $request->message,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Ticket creado exitosamente',
                    'ticket' => $ticket->load(['replies.user']),
                ], 201);
            });

        } catch (\Exception $e) {
            // AQUÍ ESTÁ EL TRUCO: Devolvemos el error exacto como JSON
            return response()->json([
                'success' => false,
                'error_real' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine()
            ], 500); // Mantenemos 500 para que salga rojo, pero con mensaje legible
        }
    }
    /**
     * Ver un ticket específico
     */
    public function show(Ticket $ticket)
    {
        // Verificar permisos
        if ($ticket->user_id !== Auth::id() && !Auth::user()->hasAnyRole(['super_admin', 'admin', 'moderator', 'support'])) {
            abort(403, 'No tienes permiso para ver este ticket');
        }

        return response()->json([
            'ticket' => $ticket->load(['user', 'replies.user']),
        ]);
    }

    /**
     * Responder a un ticket
     */
    public function reply(Request $request, Ticket $ticket)
    {
        // Verificar permisos
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

        // Si estaba cerrado, lo reabrimos
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
     * Cerrar ticket
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