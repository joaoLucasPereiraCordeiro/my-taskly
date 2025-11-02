<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->tasks()->with('subtasks')->latest()->get();
    }
    
    public function store(Request $request)
    {
        $data = $request->only(['title', 'description', 'completed', 'due_date']);
        
        $data['due_date'] = $this->parseDate($data['due_date']);
    
        if (!empty($data['due_date']) && !$data['due_date']) {
            return response()->json(['error' => 'Formato de data inválido. Use dd/mm/yyyy ou yyyy-mm-dd.'], 422);
        }
    
        // Validação com retorno dos dados validados
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'completed' => 'boolean',
            'due_date' => 'nullable|date_format:Y-m-d',
            'subtasks' => 'array',
            'subtasks.*.title' => 'required|string|max:255',
            'subtasks.*.completed' => 'boolean',
        ]);
    
        if (!isset($data['completed'])) {
            $data['completed'] = false;
        }
    
        $data['completed_at'] = $data['completed'] ? now() : null;
    
        $task = $request->user()->tasks()->create($data);
    
        // Cria as subtarefas, se houver
        if (!empty($validated['subtasks'])) {
            foreach ($validated['subtasks'] as $subtask) {
                $task->subtasks()->create([
                    'title' => $subtask['title'],
                    'completed' => $subtask['completed'] ?? false,
                ]);
            }
        }
    
        return response()->json($task->load('subtasks'), 201);
    }
    

    public function show(Task $task, Request $request)
    {
        if ($task->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Acesso não autorizado'], 403);
        }
    
        return response()->json($task->load('subtasks'));
    }
    

    
public function update(Request $request, Task $task)
{
    $data = $request->only(['title', 'description', 'completed', 'due_date', 'subtasks']);
    
    $data['due_date'] = $this->parseDate($data['due_date']);

    if (!empty($data['due_date']) && !$data['due_date']) {
        return response()->json(['error' => 'Formato de data inválido. Use dd/mm/yyyy ou yyyy-mm-dd.'], 422);
    }

    $request->merge($data);

    $request->validate([
        'title' => 'sometimes|required|string|max:255',
        'description' => 'sometimes|nullable|string',
        'completed' => 'sometimes|boolean',
        'due_date' => 'sometimes|nullable|date_format:Y-m-d',
        'subtasks' => 'sometimes|array',
        'subtasks.*.id' => 'sometimes|exists:subtasks,id',
        'subtasks.*.title' => 'required|string|max:255',
        'subtasks.*.completed' => 'boolean',
    ]);

    // Atualiza campos da tarefa
    if (isset($data['title'])) $task->title = $data['title'];
    if (array_key_exists('description', $data)) $task->description = $data['description'];
    if (isset($data['due_date'])) $task->due_date = $data['due_date'];
    if (isset($data['completed'])) {
        $task->completed = $data['completed'];
        $task->completed_at = $data['completed'] ? now() : null;
    }
    $task->save();

    // Se vier subtasks no request, atualiza elas:
    if (isset($data['subtasks'])) {
        $existingSubtaskIds = $task->subtasks()->pluck('id')->toArray();
        $receivedSubtaskIds = [];

        foreach ($data['subtasks'] as $subtaskData) {
            if (isset($subtaskData['id']) && in_array($subtaskData['id'], $existingSubtaskIds)) {
                // Atualiza subtarefa existente
                $subtask = $task->subtasks()->find($subtaskData['id']);
                $subtask->title = $subtaskData['title'];
                $subtask->completed = $subtaskData['completed'] ?? false;
                $subtask->save();
                $receivedSubtaskIds[] = $subtask->id;
            } else {
                // Cria nova subtarefa
                $newSubtask = $task->subtasks()->create([
                    'title' => $subtaskData['title'],
                    'completed' => $subtaskData['completed'] ?? false,
                ]);
                $receivedSubtaskIds[] = $newSubtask->id;
            }
        }

        // Remove subtarefas que não vieram na atualização (foram removidas)
        $toDelete = array_diff($existingSubtaskIds, $receivedSubtaskIds);
        if (!empty($toDelete)) {
            $task->subtasks()->whereIn('id', $toDelete)->delete();
        }
    }

    // Recarrega subtasks para retorno
    $task->load('subtasks');

    return response()->json($task);
}

    private function parseDate($date)
{
    if (empty($date)) return null;

    try {
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
            return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
        }

        return Carbon::parse($date)->format('Y-m-d');
    } catch (\Exception $e) {
        return null;
    }
}


public function destroy(Request $request, Task $task)
{
    if ($task->user_id !== $request->user()->id) {
        return response()->json(['error' => 'Acesso não autorizado'], 403);
    }

    $task->delete();

    return response()->json(['message' => 'Tarefa deletada com sucesso']);
}

}
