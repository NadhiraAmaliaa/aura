<?php

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of an authenticated user.
 *
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'is_active' => (bool) $this->is_active,
            'intern' => $this->whenLoaded('intern', fn (): ?array => $this->intern === null ? null : [
                'id' => $this->intern->id,
                'nim' => $this->intern->nim,
                'phone' => $this->intern->phone,
                'status' => $this->intern->effectiveStatus(),
                'start_date' => $this->intern->start_date?->toDateString(),
                'end_date' => $this->intern->end_date?->toDateString(),
                'division_id' => $this->intern->division_id,
            ]),
        ];
    }
}
