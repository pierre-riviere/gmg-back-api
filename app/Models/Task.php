<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    const rulesStore = [
        "name" => ["required", "max:255"],
        "description" => ["required", "max:255"],
        "status" => ["required", "max:255"],
        "user_id" => ["required", "exists:users,id"],
    ];

    const rulesUpdate = [
        "name" => ["filled", "max:255"],
        "description" => ["filled", "max:255"],
        "status" => ["filled", "max:255"],
        "user_id" => ["filled", "exists:users,id"],
    ];

    protected $fillable = ["name", "description", "status", "user_id"];

    /**
     * Get the task that owns the user
     */
    public function user()
    {
        return $this->belongsTo("App\Models\User", "foreign_key");
    }
}
