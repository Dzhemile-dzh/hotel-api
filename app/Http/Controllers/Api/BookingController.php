<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

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
     *     summary="Get all bookings (paginated, filterable by id, room type, and guest)",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=false,
     *         description="Filter by booking id (single or array)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="single_guest_id",
     *         in="query",
     *         required=false,
     *         description="Show only bookings for 'Single' rooms made by this guest id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of bookings"
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Booking::with(['room', 'roomType', 'guests']);

        // Apply filters using query scopes
        if ($request->has('id')) {
            $query->filterByIds($request->input('id'));
        }

        if ($request->has('single_guest_id')) {
            $query->singleRoomForGuest($request->input('single_guest_id'));
        }

        if ($request->has('status')) {
            $query->byStatus($request->input('status'));
        }

        $perPage = $request->input('per_page', 15);
        $bookings = $query->paginate($perPage);

        return BookingResource::collection($bookings);
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
    public function store(BookingRequest $request): BookingResource
    {
        $data = $request->validated();

        $booking = Booking::create([
            'external_id' => $data['external_id'],
            'room_id' => $data['room_id'],
            'room_type_id' => $data['room_type_id'],
            'arrival_date' => $data['arrival_date'],
            'departure_date' => $data['departure_date'],
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);

        if (isset($data['guest_ids'])) {
            $booking->guests()->sync($data['guest_ids']);
        }

        return new BookingResource($booking->load(['room', 'roomType', 'guests']));
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
    public function show(Booking $booking): BookingResource
    {
        return new BookingResource($booking->load(['room', 'roomType', 'guests']));
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
    public function update(BookingRequest $request, Booking $booking): BookingResource
    {
        $data = $request->validated();

        $booking->update($data);

        if (isset($data['guest_ids'])) {
            $booking->guests()->sync($data['guest_ids']);
        }

        return new BookingResource($booking->load(['room', 'roomType', 'guests']));
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
    public function destroy(Booking $booking): Response
    {
        $booking->delete();
        return response()->noContent();
    }
}
