<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $dateFormat = 'U';

    public function getThumbnailAttribute()
    {
        if($this->attributes['thumbnail'] == null)
        {
            return vsprintf('https://via.placeholder.com/500x300?text=%s', [
                "Sample Image"
            ]);
        }
    }

    public function getTotalPurchaseAttribute()
    {
       return Purchase::where('package_id', $this->id)->count();

    }
}
