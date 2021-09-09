<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLogAuth extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $dateFormat = 'U';
}
