<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $table = 'games';

    protected $fillable = [
        'created_user_id',
        'winner_user_id',
        'type',
        'status',
        'began_at',
        'ended_at',
        'total_time',
        'board_id',
        'custom',
    ];

    protected $casts = [
        'began_at' => 'datetime',
        'ended_at' => 'datetime',
        'custom' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

    public function players()
    {
        return $this->belongsToMany(User::class, 'multiplayer_games_played')
                    ->withPivot('player_won', 'pairs_discovered', 'custom');
    }

    public function createdUser()
    {
        return $this->belongsTo(User::class, 'created_user_id', 'id');
    }

    public function winnerUserId(){
        return $this->belongsTo(User::class,'winner_user_id','id');
    }

}
