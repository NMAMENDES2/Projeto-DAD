<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'nickname'  => $this->nickname,
            'email'     => $this->email,
            'type'      => $this->type,
            'blocked'   => (bool) $this->blocked,

            // Foto — devolve NULL ou a URL completa
            'photo'     => $this->photo_avatar_filename 
                ? url('/api/photos/' . $this->photo_avatar_filename) 
                : null,

            // Saldo (útil no jogo Bisca)
            'coins_balance' => $this->coins_balance,

            // Dados extra em JSON (opcional)
            'custom'    => $this->custom ?? null,

            // Datas úteis
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
