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

        // 🧠 Prompt atualizado e mais restritivo
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
- Mantenha as palavras de data exatamente como ditas (ex: 'amanhã', 'sábado', 'dia 20').

Texto a processar: \"$text\"
";

        try {
            // 🔹 Chamada ao modelo GPT
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'Responda SOMENTE em JSON válido.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            $json = $response['choices'][0]['message']['content'] ?? '{}';
            $data = json_decode($json, true) ?? [];

            // 🔹 Interpretação da data em português
            $today = Carbon::today();
            $dueDate = null;
            $textoParaBuscar = strtolower($data['due_date'] ?? $text);

            if (str_contains($textoParaBuscar, 'hoje')) {
                $dueDate = $today;
            } elseif (str_contains($textoParaBuscar, 'amanhã') || str_contains($textoParaBuscar, 'amanha')) {
                $dueDate = $today->copy()->addDay();
            } elseif (str_contains($textoParaBuscar, 'depois de amanhã') || str_contains($textoParaBuscar, 'depois de amanha')) {
                $dueDate = $today->copy()->addDays(2);
            }

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

            // 📅 Interpretação de "dia 20" com ajuste de mês se necessário
            if (!$dueDate && preg_match('/dia (\d{1,2})/', $textoParaBuscar, $m)) {
                $dia = intval($m[1]);
                $mes = $today->month;
                $ano = $today->year;

                // Se o dia já passou, joga para o próximo mês
                $dataTentativa = Carbon::createFromDate($ano, $mes, $dia);
                if ($dataTentativa->isPast()) {
                    $dataTentativa->addMonth();
                }

                $dueDate = $dataTentativa;
            }

            // 📅 Interpretação de datas completas tipo "20/11/2025"
            if (!$dueDate && preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $textoParaBuscar, $m)) {
                $dueDate = Carbon::createFromDate($m[3], $m[2], $m[1]);
            }

            // Garante que nunca crie tarefa para o passado
            if ($dueDate && $dueDate->isPast()) {
                $dueDate = $today;
            }

// 🔤 Função auxiliar para capitalizar a primeira letra
$capitalize = function ($string) {
    $string = trim($string);
    return $string ? mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1) : '';
};

// 🔹 Montagem final dos dados com capitalização
$result = [
    'title' => $capitalize($data['title'] ?? '(sem título)'),
    'description' => $capitalize($data['description'] ?? ''),
    'subtasks' => isset($data['subtasks']) && is_array($data['subtasks'])
        ? array_map($capitalize, $data['subtasks'])
        : [],
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
