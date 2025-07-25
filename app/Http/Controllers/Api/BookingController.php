<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Guest;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        return Booking::with(['room', 'roomType', 'guests'])->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'external_id' => 'required|string|unique:bookings,external_id',
            'room_id' => 'required|exists:rooms,id',
            'room_type_id' => 'required|exists:room_types,id',
            'arrival_date' => 'required|date',
            'departure_date' => 'required|date|after_or_equal:arrival_date',
            'status' => 'required|string',
            'notes' => 'nullable|string',
            'guest_ids' => 'array',
            'guest_ids.*' => 'exists:guests,id',
        ]);

        $booking = Booking::create([
            'external_id' => $data['external_id'],
            'room_id' => $data['room_id'],
            'room_type_id' => $data['room_type_id'],
            'arrival_date' => $data['arrival_date'],
            'departure_date' => $data['departure_date'],
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);

        $booking->guests()->sync($data['guest_ids'] ?? []);

        return response()->json($booking->load(['room', 'roomType', 'guests']), 201);
    }

    public function show(Booking $booking)
    {
        return $booking->load(['room', 'roomType', 'guests']);
    }

    public function update(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'room_id' => 'sometimes|exists:rooms,id',
            'room_type_id' => 'sometimes|exists:room_types,id',
            'arrival_date' => 'sometimes|date',
            'departure_date' => 'sometimes|date|after_or_equal:arrival_date',
            'status' => 'sometimes|string',
            'notes' => 'nullable|string',
            'guest_ids' => 'sometimes|array',
            'guest_ids.*' => 'exists:guests,id',
        ]);

        $booking->update($data);

        if (isset($data['guest_ids'])) {
            $booking->guests()->sync($data['guest_ids']);
        }

        return $booking->load(['room', 'roomType', 'guests']);
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();
        return response()->noContent();
    }
}
