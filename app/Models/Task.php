<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ["name", "description", "status", "user_id"];

    /**
     * Get the task that owns the user
     */
    public function user()
    {
        return $this->belongsTo("App\Models\User", "foreign_key");
    }
}
