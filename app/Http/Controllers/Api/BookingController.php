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
        return Booking::with(['room', 'guests'])->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'external_id' => 'required|string|unique:bookings,external_id',
            'room_id' => 'required|exists:rooms,id',
            'guest_ids' => 'array',
            'guest_ids.*' => 'exists:guests,id',
        ]);

        $booking = Booking::create([
            'external_id' => $data['external_id'],
            'room_id' => $data['room_id'],
        ]);

        $booking->guests()->sync($data['guest_ids'] ?? []);

        return response()->json($booking->load(['room', 'guests']), 201);
    }

    public function show(Booking $booking)
    {
        return $booking->load(['room', 'guests']);
    }

    public function update(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'room_id' => 'sometimes|exists:rooms,id',
            'guest_ids' => 'sometimes|array',
            'guest_ids.*' => 'exists:guests,id',
        ]);

        $booking->update($data);

        if (isset($data['guest_ids'])) {
            $booking->guests()->sync($data['guest_ids']);
        }

        return $booking->load(['room', 'guests']);
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();
        return response()->noContent();
    }
}
