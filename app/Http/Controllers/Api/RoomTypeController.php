<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    public function index()
    {
        return RoomType::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'external_id' => 'required|unique:room_types,external_id',
            'name' => 'required|string',
        ]);

        return RoomType::create($data);
    }

    public function show(RoomType $roomType)
    {
        return $roomType;
    }

    public function update(Request $request, RoomType $roomType)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
        ]);

        $roomType->update($data);

        return $roomType;
    }

    public function destroy(RoomType $roomType)
    {
        $roomType->delete();
        return response()->noContent();
    }
}
