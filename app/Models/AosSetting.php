<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AosSetting extends Model
{
    use HasFactory;

    protected $primaryKey = null;
    protected $guarded = [];

    public $incrementing = false;
    public $timestamps = false;
}