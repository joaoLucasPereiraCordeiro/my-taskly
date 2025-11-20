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

        Log::info('Texto recebido do front', ['text' => $text]);

        // Prompt restritivo para o GPT
        $prompt = "
Você é um assistente que transforma comandos de voz em JSON.
Extraia APENAS o que for claramente dito.  
Use sempre esta estrutura:
{
  \"title\": \"nome da tarefa\",
  \"description\": \"descrição (somente se o usuário disser explicitamente 'descrição', 'subtarefa' ou algo similar)\",
  \"subtasks\": [lista de subtarefas se o usuário disser 'subtarefa' ou 'tarefa secundária'],
  \"due_date\": \"data em texto, se mencionada (ex: 'amanhã', 'quinta-feira', 'dia 20', '20/11/2025')\"
}

Regras:
- Nunca invente dados.
- Sempre devolva os quatro campos, mesmo que vazios.
- Se o usuário disser apenas o título e data, retorne description vazio.
- Mantenha as palavras de data exatamente como ditas.

Texto a processar: \"$text\"
";

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'Responda SOMENTE em JSON válido.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            $json = $response['choices'][0]['message']['content'] ?? '{}';
            Log::info('Resposta bruta do OpenAI', ['response' => $json]);

            $data = json_decode($json, true);

            if (!is_array($data)) {
                $data = [
                    'title' => '',
                    'description' => '',
                    'subtasks' => [],
                    'due_date' => ''
                ];
            }

            if (!isset($data['subtasks']) || !is_array($data['subtasks'])) {
                $data['subtasks'] = [];
            }

            $today = Carbon::today();
            $dueDate = null;
            $textoParaBuscar = strtolower($data['due_date'] ?? $text);

            // Datas relativas
            if (str_contains($textoParaBuscar, 'hoje')) {
                $dueDate = $today;
            } elseif (str_contains($textoParaBuscar, 'amanhã') || str_contains($textoParaBuscar, 'amanha')) {
                $dueDate = $today->copy()->addDay();
            } elseif (str_contains($textoParaBuscar, 'depois de amanhã') || str_contains($textoParaBuscar, 'depois de amanha')) {
                $dueDate = $today->copy()->addDays(2);
            }

            // Dias da semana
            if (!$dueDate) {
                $diasSemana = [
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

                foreach ($diasSemana as $nome => $diaCarbon) {
                    if (str_contains($textoParaBuscar, $nome)) {
                        $hojeIndice = $today->dayOfWeek;
                        $diff = $diaCarbon - $hojeIndice;
                        if ($diff <= 0) $diff += 7;
                        $dueDate = $today->copy()->addDays($diff);
                        break;
                    }
                }
            }

            // "dia XX"
            if (!$dueDate && preg_match('/dia (\d{1,2})/', $textoParaBuscar, $m)) {
                $dia = intval($m[1]);
                $mes = $today->month;
                $ano = $today->year;

                $dataTentativa = Carbon::createFromDate($ano, $mes, $dia);
                if ($dataTentativa->isPast()) {
                    $dataTentativa->addMonth();
                }

                $dueDate = $dataTentativa;
            }

            // Datas completas dd/mm/yyyy
            if (!$dueDate && preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $textoParaBuscar, $m)) {
                $dueDate = Carbon::createFromDate($m[3], $m[2], $m[1]);
            }

            if ($dueDate && $dueDate->isPast()) {
                $dueDate = $today;
            }

            $capitalize = function ($string) {
                $string = trim($string);
                return $string ? mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1) : '';
            };

            $result = [
                'title' => $capitalize($data['title'] ?? '(sem título)'),
                'description' => $capitalize($data['description'] ?? ''),
                'subtasks' => array_map($capitalize, $data['subtasks']),
                'due_date' => $dueDate ? $dueDate->format('Y-m-d') : ''
            ];

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Erro ao processar comando de voz: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erro ao processar comando de voz',
                'message' => $e->getMessage(),
                'title' => '',
                'description' => '',
                'subtasks' => [],
                'due_date' => ''
            ], 500);
        }
    }
}
