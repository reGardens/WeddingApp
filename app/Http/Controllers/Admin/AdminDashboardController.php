<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wedding;
use App\Models\CustomTemplate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard with stats.
     */
    public function index(): Response
    {
        $totalUsers = User::count();
        $totalRoles = Role::count();
        $totalWeddings = Wedding::count();
        $activeUsers = User::has('weddings')->count();
        $totalCustomTemplates = CustomTemplate::count();

        $recentWeddings = Wedding::with(['user', 'couple', 'events'])
            ->withCount(['guests', 'rsvps'])
            ->latest()
            ->get()
            ->map(function ($wedding) {
                return [
                    'id' => $wedding->id,
                    'slug' => $wedding->slug,
                    'label' => $wedding->label,
                    'created_at' => $wedding->created_at->toISOString(),
                    'guests_count' => $wedding->guests_count,
                    'rsvps_count' => $wedding->rsvps_count,
                    'user' => $wedding->user ? [
                        'name' => $wedding->user->name,
                        'email' => $wedding->user->email,
                        'allowed_features' => $wedding->user->allowed_features,
                    ] : null,
                    'couple' => $wedding->couple ? [
                        'groom_full_name' => $wedding->couple->groom_full_name,
                        'groom_nickname' => $wedding->couple->groom_nickname,
                        'bride_full_name' => $wedding->couple->bride_full_name,
                        'bride_nickname' => $wedding->couple->bride_nickname,
                    ] : null,
                    'events' => $wedding->events->map(fn($e) => [
                        'id' => $e->id,
                        'name' => $e->name,
                        'date' => $e->date,
                        'start_time' => $e->start_time,
                        'location_name' => $e->location_name,
                    ]),
                ];
            });

        $recentUsers = User::with('roles')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'created_at' => $u->created_at ? $u->created_at->toISOString() : null,
                    'roles' => $u->getRoleNames(),
                ];
            });

        return Inertia::render('Cms/Dashboard', [
            'totalUsers' => $totalUsers,
            'totalRoles' => $totalRoles,
            'totalWeddings' => $totalWeddings,
            'activeUsers' => $activeUsers,
            'totalCustomTemplates' => $totalCustomTemplates,
            'recentWeddings' => $recentWeddings,
            'recentUsers' => $recentUsers,
        ]);
    }
}
