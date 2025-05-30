<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('active', true)->where('blocked', false)->count(),
            'new_users_today' => User::whereDate('created_at', Carbon::today())->count(),
            'new_users_week' => User::where('created_at', '>=', Carbon::now()->subWeek())->count(),
            'new_users_month' => User::where('created_at', '>=', Carbon::now()->subMonth())->count(),
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'users_with_2fa' => User::whereNotNull('two_factor_confirmed_at')->count(),
            'blocked_users' => User::where('blocked', true)->count(),
        ];

        return response()->json($stats);
    }

    public function charts()
    {
        // User growth over last 30 days
        $userGrowth = User::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::parse($date)->format('d.m.');
            
            $count = $userGrowth->firstWhere('date', $date)?->count ?? 0;
            $data[] = $count;
        }

        // Role distribution
        $roleDistribution = Role::withCount('users')->get();
        
        $roleLabels = $roleDistribution->pluck('name')->toArray();
        $roleData = $roleDistribution->pluck('users_count')->toArray();

        return response()->json([
            'userGrowth' => [
                'labels' => $labels,
                'data' => $data,
            ],
            'roleDistribution' => [
                'labels' => $roleLabels,
                'data' => $roleData,
            ],
        ]);
    }
}