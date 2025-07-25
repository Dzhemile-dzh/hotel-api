<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'external_id' => $this->external_id,
            'arrival_date' => $this->arrival_date->format('Y-m-d'),
            'departure_date' => $this->departure_date->format('Y-m-d'),
            'status' => $this->status,
            'notes' => $this->notes,
            'room' => $this->whenLoaded('room', function () {
                return [
                    'id' => $this->room->id,
                    'number' => $this->room->number,
                    'floor' => $this->room->floor,
                ];
            }),
            'room_type' => $this->whenLoaded('roomType', function () {
                return [
                    'id' => $this->roomType->id,
                    'name' => $this->roomType->name,
                    'description' => $this->roomType->description,
                ];
            }),
            'guests' => $this->whenLoaded('guests', function () {
                return $this->guests->map(function ($guest) {
                    return [
                        'id' => $guest->id,
                        'first_name' => $guest->first_name,
                        'last_name' => $guest->last_name,
                        'email' => $guest->email,
                        'phone' => $guest->phone,
                    ];
                });
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
} 