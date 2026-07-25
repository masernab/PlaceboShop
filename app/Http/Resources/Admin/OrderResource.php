<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\OrderResource as ShopOrderResource;
use Illuminate\Http\Request;

class OrderResource extends ShopOrderResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'customer' => $this->whenLoaded('user', fn (): array => [
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
        ];
    }
}
