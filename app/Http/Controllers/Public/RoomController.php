<?php

namespace App\Http\Controllers\Public;

use App\Enums\RoomStatus;
use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(Request $request): View
    {
        $rooms = Room::query()
            ->available()
            ->with(['roomType', 'property', 'building', 'floor', 'amenities'])
            ->when($request->filled('search'), fn ($query) => $query->search($request->string('search')))
            ->when($request->filled('room_type'), fn ($query) => $query->where('room_type_id', $request->integer('room_type')))
            ->orderBy('price')
            ->paginate(12)
            ->withQueryString();

        return view('public.rooms.index', [
            'rooms' => $rooms,
            'roomTypes' => RoomType::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function show(string $slug): View
    {
        $room = Room::query()
            ->where('slug', $slug)
            ->with(['roomType', 'property', 'building', 'floor', 'amenities', 'photos'])
            ->firstOrFail();

        abort_unless($room->status === RoomStatus::Available, 404);

        $otherRooms = Room::query()
            ->available()
            ->whereKeyNot($room->getKey())
            ->with(['roomType'])
            ->inRandomOrder()
            ->take(3)
            ->get();

        return view('public.rooms.show', [
            'room' => $room,
            'otherRooms' => $otherRooms,
        ]);
    }
}
