<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $dateFormat = 'U';

    /**
     * Get the profile photo URL attribute.
     *
     * @return string
     */
    public function getPhotoUrlAttribute()
    {
        if($this->attributes['photo_url'] == null)
        {
            return vsprintf('https://www.gravatar.com/avatar/%s.jpg?s=200&d=%s', [
                md5(strtolower($this->email)),
                $this->username ? urlencode("https://ui-avatars.com/api/$this->username") : 'mp',
            ]);
        }
    }

    public function boardarticles()
    {
        return $this->hasMany(BoardArticle::class);
    }

    public function sites()
    {
        return $this->hasMany(Site::class);
    }

    public function recommend()
    {
        return $this->belongsTo(User::class, 'group_id', 'id');
    }

    public function ethereum()
    {
        return $this->hasMany(EthereumWallet::class);
    }

    public function getLevelLabelAttribute()
    {
        return Level::where('level', $this->level)->first();
    }

    public function getPackageAttribute()
    {
        return Purchase::where('user_id', $this->id)->orderBy('id', 'desc')->first();
    }

    public function getPurchaseCountAttribute()
    {
        return Purchase::where('user_id', $this->id)->count();
    }
}
