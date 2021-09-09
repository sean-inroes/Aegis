<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_id',
        'category',
        'status',
    ];

    protected $dateFormat = 'U';

    public function board()
    {
        return $this->belongsTo(Board::class);
    }
}
