<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';
    public $timestamps = false;
    protected $fillable = [
        'type',                
        'transaction_datetime',
        'user_id',             
        'game_id',             
        'euros',               
        'payment_type',        
        'payment_reference',   
        'brain_coins',         
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

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
