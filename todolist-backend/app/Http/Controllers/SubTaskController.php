<?php

namespace App\Http\Controllers;

use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\Request;

class SubTaskController extends Controller
{
    public function index(Request $request, Task $task)
    {
        if ($task->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Acesso não autorizado à tarefa'], 403);
        }

        return response()->json($task->subtasks);
    }

    public function store(Request $request, Task $task)
    {
        if ($task->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Acesso não autorizado à tarefa'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $subtask = $task->subtasks()->create([
            'title' => $validated['title'],
            'completed' => false,
        ]);

        return response()->json($subtask, 201);
    }

    public function show(Request $request, Task $task, Subtask $subtask)
    {
        if ($task->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Acesso não autorizado à tarefa'], 403);
        }

        if ($subtask->task_id !== $task->id) {
            return response()->json(['error' => 'Subtarefa não pertence à tarefa.'], 403);
        }

        return response()->json($subtask);
    }

    public function update(Request $request, Task $task, Subtask $subtask)
    {
        if ($task->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Acesso não autorizado à tarefa'], 403);
        }

        if ($subtask->task_id !== $task->id) {
            return response()->json(['error' => 'Subtarefa não pertence à tarefa.'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'completed' => 'sometimes|boolean',
        ]);

        $subtask->update($validated);

        return response()->json($subtask);
    }

    public function updateWithoutTask(Request $request, Subtask $subtask)
    {
        if ($subtask->task->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Acesso não autorizado'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'completed' => 'sometimes|boolean',
        ]);

        $subtask->update($validated);

        // Verifica se todas as subtarefas da tarefa foram concluídas
        $task = $subtask->task; // Relação com a tarefa
        $allCompleted = $task->subtasks()->count() > 0 &&
                        $task->subtasks()->where('completed', false)->count() === 0;

        // Atualiza status da tarefa principal
        $task->update(['completed' => $allCompleted]);

        return response()->json([
            'subtask' => $subtask,
            'task' => $task,
        ]);
    }

    public function destroy(Request $request, Task $task, Subtask $subtask)
    {
        if ($task->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Acesso não autorizado à tarefa'], 403);
        }

        if ($subtask->task_id !== $task->id) {
            return response()->json(['error' => 'Subtarefa não pertence à tarefa.'], 403);
        }

        $subtask->delete();

        return response()->json(null, 204);
    }
}
