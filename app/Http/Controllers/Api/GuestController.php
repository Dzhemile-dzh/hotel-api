<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/guests",
     *     tags={"Guests"},
     *     summary="Get all guests (paginated, filterable by id)",
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
     *         description="Filter by guest id (single or array)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of guests"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Guest::query();
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
     *     path="/api/guests",
     *     tags={"Guests"},
     *     summary="Create a new guest",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"external_id","first_name","last_name"},
     *             @OA\Property(property="external_id", type="string"),
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="phone", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Guest created"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'external_id' => 'required|unique:guests,external_id',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        return Guest::create($data);
    }

    /**
     * @OA\Get(
     *     path="/api/guests/{id}",
     *     tags={"Guests"},
     *     summary="Get a guest by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Guest details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Guest not found"
     *     )
     * )
     */
    public function show(Guest $guest)
    {
        return $guest;
    }

    /**
     * @OA\Put(
     *     path="/api/guests/{id}",
     *     tags={"Guests"},
     *     summary="Update a guest",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="phone", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Guest updated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Guest not found"
     *     )
     * )
     */
    public function update(Request $request, Guest $guest)
    {
        $data = $request->validate([
            'first_name' => 'sometimes|string',
            'last_name' => 'sometimes|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        $guest->update($data);

        return $guest;
    }

    /**
     * @OA\Delete(
     *     path="/api/guests/{id}",
     *     tags={"Guests"},
     *     summary="Delete a guest",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Guest deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Guest not found"
     *     )
     * )
     */
    public function destroy(Guest $guest)
    {
        $guest->delete();
        return response()->noContent();
    }
}
