<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/rooms",
     *     tags={"Rooms"},
     *     summary="Get all rooms (paginated, filterable by id)",
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
     *         description="Filter by room id (single or array)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of rooms"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Room::with('roomType');
        if ($request->has('id')) {
            $ids = $request->input('id');
            if (is_array($ids)) {
                $query->whereIn('id', $ids);
            } else {
                $query->where('id', $ids);
            }
        }
        $perPage = $request->input('per_page', 15);
        return $query->paginate($perPage);
    }

    /**
     * @OA\Post(
     *     path="/api/rooms",
     *     tags={"Rooms"},
     *     summary="Create a new room",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"external_id","room_type_id","number","floor"},
     *             @OA\Property(property="external_id", type="string"),
     *             @OA\Property(property="room_type_id", type="integer"),
     *             @OA\Property(property="number", type="string"),
     *             @OA\Property(property="floor", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Room created"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'external_id' => 'required|unique:rooms,external_id',
            'room_type_id' => 'required|exists:room_types,id',
            'number' => 'required|string',
            'floor' => 'required|integer',
        ]);

        return Room::create($data);
    }

    /**
     * @OA\Get(
     *     path="/api/rooms/{id}",
     *     tags={"Rooms"},
     *     summary="Get a room by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Room details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Room not found"
     *     )
     * )
     */
    public function show(Room $room)
    {
        return $room->load('roomType');
    }

    /**
     * @OA\Put(
     *     path="/api/rooms/{id}",
     *     tags={"Rooms"},
     *     summary="Update a room",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="room_type_id", type="integer"),
     *             @OA\Property(property="number", type="string"),
     *             @OA\Property(property="floor", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Room updated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Room not found"
     *     )
     * )
     */
    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            'room_type_id' => 'sometimes|exists:room_types,id',
            'number' => 'sometimes|string',
            'floor' => 'sometimes|integer',
        ]);

        $room->update($data);

        return $room->load('roomType');
    }

    /**
     * @OA\Delete(
     *     path="/api/rooms/{id}",
     *     tags={"Rooms"},
     *     summary="Delete a room",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Room deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Room not found"
     *     )
     * )
     */
    public function destroy(Room $room)
    {
        $room->delete();
        return response()->noContent();
    }
}
