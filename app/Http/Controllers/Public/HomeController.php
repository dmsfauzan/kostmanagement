<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featuredRooms = Room::query()
            ->available()
            ->with(['roomType', 'property', 'building', 'floor'])
            ->latest()
            ->take(6)
            ->get();

        $property = Property::query()->withCount(['rooms', 'buildings'])->first();
        $roomTypes = RoomType::query()->availableRooms()->latest()->get();

        return view('public.home', [
            'featuredRooms' => $featuredRooms,
            'property' => $property,
            'roomTypes' => $roomTypes,
        ]);
    }
}
