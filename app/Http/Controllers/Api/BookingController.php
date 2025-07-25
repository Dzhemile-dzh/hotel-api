<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Guest;
use Illuminate\Http\Request;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0.0",
 *         title="Hotel API",
 *         description="API documentation for the Hotel Management System"
 *     )
 * )
 */
class BookingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/bookings",
     *     tags={"Bookings"},
     *     summary="Get all bookings",
     *     @OA\Response(
     *         response=200,
     *         description="List of bookings"
     *     )
     * )
     */
    public function index()
    {
        return Booking::with(['room', 'roomType', 'guests'])->get();
    }

    /**
     * @OA\Post(
     *     path="/api/bookings",
     *     tags={"Bookings"},
     *     summary="Create a new booking",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"external_id","room_id","room_type_id","arrival_date","departure_date","status"},
     *             @OA\Property(property="external_id", type="string"),
     *             @OA\Property(property="room_id", type="integer"),
     *             @OA\Property(property="room_type_id", type="integer"),
     *             @OA\Property(property="arrival_date", type="string", format="date"),
     *             @OA\Property(property="departure_date", type="string", format="date"),
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="notes", type="string"),
     *             @OA\Property(property="guest_ids", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Booking created"
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/bookings/{id}",
     *     tags={"Bookings"},
     *     summary="Get a booking by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found"
     *     )
     * )
     */
    public function show(Booking $booking)
    {
        return $booking->load(['room', 'roomType', 'guests']);
    }

    /**
     * @OA\Put(
     *     path="/api/bookings/{id}",
     *     tags={"Bookings"},
     *     summary="Update a booking",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="room_id", type="integer"),
     *             @OA\Property(property="room_type_id", type="integer"),
     *             @OA\Property(property="arrival_date", type="string", format="date"),
     *             @OA\Property(property="departure_date", type="string", format="date"),
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="notes", type="string"),
     *             @OA\Property(property="guest_ids", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking updated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found"
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/bookings/{id}",
     *     tags={"Bookings"},
     *     summary="Delete a booking",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Booking deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found"
     *     )
     * )
     */
    public function destroy(Booking $booking)
    {
        $booking->delete();
        return response()->noContent();
    }
}
