<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Carbon\Carbon;

class AssistantController extends Controller
{
    public function getMessage(Request $request)
    {
        // Define fuso horário do Brasil
        date_default_timezone_set('America/Sao_Paulo');

        // Pega o usuário autenticado ou assume id 1 para teste
        $user = $request->user();
        $userId = $user ? $user->id : 1; 
        $nome = $user ? $user->name : "usuário";

        // Data de hoje (00:00 até 23:59)
        $hojeInicio = Carbon::today()->startOfDay();
        $hojeFim = Carbon::today()->endOfDay();

        // Buscar tarefas do usuário para hoje
        $tarefasHoje = Task::where('user_id', $userId)
            ->whereBetween('due_date', [$hojeInicio, $hojeFim])
            ->get();

        $total = $tarefasHoje->count();

        // Determinar saudação com base no horário atual
        $horaAtual = Carbon::now()->hour;
        if ($horaAtual >= 5 && $horaAtual < 12) {
            $saudacao = "Bom dia";
        } elseif ($horaAtual >= 12 && $horaAtual < 18) {
            $saudacao = "Boa tarde";
        } else {
            $saudacao = "Boa noite";
        }

     // Tarefas do usuário para hoje
$tarefasHoje = Task::where('user_id', $userId)
->whereBetween('due_date', [$hojeInicio, $hojeFim])
->get();

// Tarefas pendentes
$tarefasPendentes = $tarefasHoje->where('completed', false);

$totalPendentes = $tarefasPendentes->count();

// Monta a mensagem
if ($totalPendentes > 0) {
$mensagem = "{$saudacao}, {$nome}! Você tem {$totalPendentes} " .
    ($totalPendentes === 1 ? 'tarefa' : 'tarefas') .
    " para hoje";
} else {
$mensagem = "{$saudacao}, {$nome}! Você não tem tarefas para hoje 🎉. Que tal criar uma nova?";
}


        return response()->json([
            'message' => $mensagem,
            'tarefasHoje' => $tarefasHoje
        ]);
    }
}
