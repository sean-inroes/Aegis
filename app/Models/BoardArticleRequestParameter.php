<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardArticleRequestParameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_article_request_id',
        'type',
        'value',
        'label',
        'name'
    ];

    protected $dateFormat = 'U';

    public function boardarticlerequest(){
        return $this->belongsTo(BoardArticleRequest::class, 'board_article_request_id' ,'id');
    }
}
