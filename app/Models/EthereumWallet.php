<?php

namespace App\Models;

use App\Http\Controllers\GethController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EthereumWallet extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $dateFormat = 'U';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getGetBalanceAttribute()
    {
        $geth = new GethController();

        return $geth->fromWei(sprintf("%.0f", $geth->eth_getBalance($this->address, 'latest')));
    }
}
