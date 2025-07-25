<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/room-types",
     *     tags={"RoomTypes"},
     *     summary="Get all room types (paginated, filterable by id)",
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
     *         description="Filter by room type id (single or array)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of room types"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = RoomType::query();
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
     *     path="/api/room-types",
     *     tags={"RoomTypes"},
     *     summary="Create a new room type",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"external_id","name"},
     *             @OA\Property(property="external_id", type="string"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Room type created"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'external_id' => 'required|unique:room_types,external_id',
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        return RoomType::create($data);
    }

    /**
     * @OA\Get(
     *     path="/api/room-types/{id}",
     *     tags={"RoomTypes"},
     *     summary="Get a room type by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Room type details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Room type not found"
     *     )
     * )
     */
    public function show(RoomType $roomType)
    {
        return $roomType;
    }

    /**
     * @OA\Put(
     *     path="/api/room-types/{id}",
     *     tags={"RoomTypes"},
     *     summary="Update a room type",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Room type updated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Room type not found"
     *     )
     * )
     */
    public function update(Request $request, RoomType $roomType)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'description' => 'nullable|string',
        ]);

        $roomType->update($data);

        return $roomType;
    }

    /**
     * @OA\Delete(
     *     path="/api/room-types/{id}",
     *     tags={"RoomTypes"},
     *     summary="Delete a room type",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Room type deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Room type not found"
     *     )
     * )
     */
    public function destroy(RoomType $roomType)
    {
        $roomType->delete();
        return response()->noContent();
    }
}
