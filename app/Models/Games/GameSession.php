<?php

namespace App\Models\Games;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class GameSession extends Model

{
    protected $table = 'game_sessions';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function game()
    {
        return $this->belongsTo(Game::class, 'type');
    }

    public function getGameTitle()
    {
        $statuses = [
            'chests' => 'Сундуки',
            'wheel' => 'Колесо фартуны',
        ];

        return $statuses[$this->game] ?? '-';
    }

    public function getStatus()
    {
        $statuses = [
            'started' => 'Начата',
            'win' => 'Выигрыш',
            'fail' => 'Проигры',
            'aborted' => 'Прервана',
        ];

        return $statuses[$this->status] ?? '-';
    }
}
