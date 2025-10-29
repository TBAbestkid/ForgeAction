<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'username' => 'required|string|max:50',
            'sala_id' => 'required|integer',
        ]);

        $data = [
            'sala_id' => $request->sala_id,
            'username' => $request->username,
            'message' => $request->message,
        ];

        // dispara o evento para a sala correspondente
        event(new MessageSent($data));

        Log::info('Mensagem enviada via evento:', $data);

        return response()->json(['status' => 'ok']);
    }

}
