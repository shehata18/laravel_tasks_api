<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use HasFactory;

    protected $fillable = ['task_id', 'user_id', 'name'];
    protected $appends = ['file_url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function getFileUrlAttribute()
    {
        return config('app.url') . Storage::url('tasks/' . $this->task_id . '/' . $this->name);
    }
}
