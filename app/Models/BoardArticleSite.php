<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardArticleSite extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_article_id',
        'site_id',
    ];

    protected $dateFormat = 'U';

    public function boardarticle()
    {
        return $this->hasMany(BoardArticle::class);
    }

    public function site()
    {
        return $this->hasMany(Site::class);
    }
}
