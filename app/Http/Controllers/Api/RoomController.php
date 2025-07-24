<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        return Room::with('roomType')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'external_id' => 'required|unique:rooms,external_id',
            'room_type_id' => 'required|exists:room_types,id',
            'number' => 'nullable|string',
        ]);

        return Room::create($data);
    }

    public function show(Room $room)
    {
        return $room->load('roomType');
    }

    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            'room_type_id' => 'sometimes|exists:room_types,id',
            'number' => 'nullable|string',
        ]);

        $room->update($data);

        return $room->load('roomType');
    }

    public function destroy(Room $room)
    {
        $room->delete();
        return response()->noContent();
    }
}
