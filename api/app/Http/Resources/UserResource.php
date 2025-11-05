<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'nickname' => $this->nickname,
            'type' => $this->type,
            'brain_coins_balance' => $this->brain_coins_balance,
            'photo_filename' => $this->photo_filename,
            'photo_url' => $this->photo_filename 
                ? url('/api/photos/' . $this->photo_filename) 
                : null,
            'blocked' => $this->blocked,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
