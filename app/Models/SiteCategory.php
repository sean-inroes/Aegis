<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'type',
        'icon',
        'name',
        'content',
    ];

    protected $dateFormat = 'U';

    public function site(){
        return $this->belongsTo(Site::class);
    }
}
