<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteCategoryTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'icon',
        'name',
    ];

    protected $dateFormat = 'U';
}
