<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoinTransactionType extends Model
{
    protected $fillable = ['name', 'type', 'custom'];
    
    protected $casts = [
        'custom' => 'array',
    ];

    public function coinTransactions()
    {
        return $this->hasMany(CoinTransaction::class);
    }
}
