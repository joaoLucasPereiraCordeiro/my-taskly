<?php

namespace App\Models;
use App\Models\Subtask;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;


class Task extends Model
{

    protected $fillable = [
        'title',
        'description',
        'completed',
        'completed_at',
        'due_date',
        'user_id', // 👈 precisa disso para salvar corretamente
    ];


    protected $dates = ['created_at', 'updated_at', 'completed_at', 'due_date'];


    protected $casts = [
        'completed' => 'boolean',
        'completed_at' => 'datetime',
        'due_date' => 'date', // importante para converter automaticamente
    ];

    // FORMATANDO PARA ENVIAR AO FRONT COMO "dd/mm/yyyy"
    public function getDueDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d/m/Y') : null;
    }

    public function setDueDateAttribute($value)
    {
        if ($value) {
            try {
                // tenta criar a data no formato Y-m-d
                $date = Carbon::createFromFormat('Y-m-d', $value);
            } catch (\Exception $e) {
                // se der erro, tenta no formato d/m/Y
                $date = Carbon::createFromFormat('d/m/Y', $value);
            }
            $this->attributes['due_date'] = $date->format('Y-m-d');
        } else {
            $this->attributes['due_date'] = null;
        }
    }

    public function subtasks()
    {
        return $this->hasMany(SubTask::class);
    }

    public function user()
{
    return $this->belongsTo(User::class);
}
    
    
}
