<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoinTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'coin_transaction_type_id',
        'match_id',
        'game_id',
        'coins',
        'transaction_datetime',
        'custom',
    ];

    protected $casts = [
        'transaction_datetime' => 'datetime',
        'custom' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coinTransactionType()
    {
        return $this->belongsTo(CoinTransactionType::class);
    }

    public function coinPurchase()
    {
        return $this->hasOne(CoinPurchase::class);
    }
}
