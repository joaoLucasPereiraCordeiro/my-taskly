<?php

use App\Http\Controllers\SubTaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\VoiceTaskController;
use App\Http\Controllers\AdminController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ROTAS PROTEGIDAS POR LOGIN
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // CRUD de tarefas
    Route::apiResource('tasks', TaskController::class);

    // Subtarefas
    Route::put('/subtasks/{subtask}', [SubTaskController::class, 'updateWithoutTask']);

    // Criação de tarefa por voz
    Route::post('/voice-task', [VoiceTaskController::class, 'store']);

    // Assistente (fala sobre tarefas do dia)
    Route::get('/assistant/message', [AssistantController::class, 'getMessage']);

    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::get('/admin/users', [AdminController::class, 'getAllUsers']);
        Route::get('/admin/stats', [AdminController::class, 'getStats']);
        Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser']); // rota para excluir
    });
    
});
