<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoinPurchase extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'coin_transaction_id',
        'euros',
        'payment_type',
        'payment_reference',
        'purchase_datetime',
        'custom',
    ];

    protected $casts = [
        'purchase_datetime' => 'datetime',
        'custom' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coinTransaction()
    {
        return $this->belongsTo(CoinTransaction::class);
    }
}
