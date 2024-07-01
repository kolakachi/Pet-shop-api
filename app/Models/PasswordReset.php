<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use HasFactory;

    public function setUpdatedAt($value)
    {
        // Do nothing
    }

    protected $fillable = [
        'email',
        'token',
    ];
}
