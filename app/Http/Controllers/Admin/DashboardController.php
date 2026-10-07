<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $roleCounts = collect(['owner', 'admin', 'finance', 'technician', 'tenant'])
            ->mapWithKeys(fn (string $role): array => [$role => User::role($role)->count()]);

        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'roleCounts' => $roleCounts,
        ]);
    }
}
