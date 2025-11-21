<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VoiceTaskController extends Controller
{
    public function store(Request $request)
    {
        try {
            $text = strtolower($request->input('text', ''));

            if (trim($text) === '') {
                return response()->json([
                    'error' => 'Nenhum texto recebido',
                    'title' => '',
                    'description' => '',
                    'subtasks' => [],
                    'due_date' => ''
                ], 422);
            }

            Log::info('🎤 Texto recebido do front', ['text' => $text]);

            $prompt = "
Você é um assistente que transforma comandos de voz em JSON.
Sempre retorne o seguinte formato:

{
  \"title\": \"\",
  \"description\": \"\",
  \"subtasks\": [],
  \"due_date\": \"\"
}

Regras IMPORTANTES:
- Nunca invente nada.
- Só preencha subtarefas se o usuário disser explicitamente.
- Sempre retorne JSON válido.
- Mantenha a data exatamente como foi dita.

Texto: \"$text\"
";

            // 🧠 Chamada ao OpenAI
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'Responda SOMENTE JSON válido, sem explicações.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            $raw = $response['choices'][0]['message']['content'] ?? '{}';
            Log::info('📨 Resposta bruta da IA', ['raw' => $raw]);

            $data = json_decode($raw, true);

            // Garantia de estrutura
            $data = array_merge([
                'title' => '',
                'description' => '',
                'subtasks' => [],
                'due_date' => ''
            ], is_array($data) ? $data : []);

            // ==========================
            // 🔍 PROCESSAMENTO DE DATAS
            // ==========================
            $today = Carbon::today();
            $dueDate = null;

            $textoData = strtolower($data['due_date'] ?? $text);

            // hoje / amanhã / depois de amanhã
            if (str_contains($textoData, 'hoje')) {
                $dueDate = $today;
            } elseif (str_contains($textoData, 'amanhã') || str_contains($textoData, 'amanha')) {
                $dueDate = $today->copy()->addDay();
            } elseif (str_contains($textoData, 'depois de amanhã') || str_contains($textoData, 'depois de amanha')) {
                $dueDate = $today->copy()->addDays(2);
            }

            // Dias da semana
            if (!$dueDate) {
                $map = [
                    'domingo' => Carbon::SUNDAY,
                    'segunda' => Carbon::MONDAY,
                    'segunda-feira' => Carbon::MONDAY,
                    'terça' => Carbon::TUESDAY,
                    'terca' => Carbon::TUESDAY,
                    'terça-feira' => Carbon::TUESDAY,
                    'quarta' => Carbon::WEDNESDAY,
                    'quarta-feira' => Carbon::WEDNESDAY,
                    'quinta' => Carbon::THURSDAY,
                    'quinta-feira' => Carbon::THURSDAY,
                    'sexta' => Carbon::FRIDAY,
                    'sexta-feira' => Carbon::FRIDAY,
                    'sábado' => Carbon::SATURDAY,
                    'sabado' => Carbon::SATURDAY,
                ];

                foreach ($map as $nome => $valor) {
                    if (str_contains($textoData, $nome)) {
                        $diff = $valor - $today->dayOfWeek;
                        if ($diff <= 0) $diff += 7;
                        $dueDate = $today->copy()->addDays($diff);
                        break;
                    }
                }
            }

            // “dia 15”
            if (!$dueDate && preg_match('/dia (\d{1,2})/', $textoData, $m)) {
                $d = (int)$m[1];
                $target = Carbon::create($today->year, $today->month, $d);
                if ($target->isPast()) $target->addMonth();
                $dueDate = $target;
            }

            // Formato 10/01/2030
            if (!$dueDate && preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $textoData, $m)) {
                $dueDate = Carbon::create($m[3], $m[2], $m[1]);
            }

            if ($dueDate && $dueDate->isPast()) {
                $dueDate = $today;
            }

            // ==========================
            // 🔤 AJUSTES FINAIS
            // ==========================
            $cap = fn ($t) => $t ? mb_strtoupper(mb_substr($t, 0, 1)) . mb_substr($t, 1) : '';

            return response()->json([
                'title' => $cap($data['title']),
                'description' => $cap($data['description']),
                'subtasks' => array_map($cap, $data['subtasks']),
                'due_date' => $dueDate ? $dueDate->format('Y-m-d') : ''
            ]);

        } catch (\Throwable $e) {
            Log::error('❌ ERRO NO VoiceTaskController', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile()
            ]);

            return response()->json([
                'error' => 'Erro ao processar comando de voz',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
