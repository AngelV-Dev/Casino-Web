<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketController extends Controller
{
    // Listar tickets asignados o todos según rol
    public function index()
    {
        $tickets = Ticket::query()
            ->when(auth()->user()->isSupport(), fn($q) => $q->where('assigned_to', auth()->id()))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Support/Tickets/Index', [
            'tickets' => $tickets,
        ]);
    }

    // Listar mis tickets (para soporte)
    public function myTickets()
    {
        $tickets = Ticket::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Support/Tickets/MyTickets', [
            'tickets' => $tickets,
        ]);
    }

    // Responder ticket
    public function reply(Request $request, Ticket $ticket)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $ticket->responses()->create([
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        return redirect()->back()->with('success', 'Respuesta enviada');
    }

    // Cerrar ticket
    public function close(Ticket $ticket)
    {
        if (!auth()->user()->can('close_tickets')) {
            abort(403, 'No tienes permiso para cerrar tickets');
        }

        $ticket->update(['status' => 'closed']);

        return redirect()->back()->with('success', 'Ticket cerrado');
    }
}
