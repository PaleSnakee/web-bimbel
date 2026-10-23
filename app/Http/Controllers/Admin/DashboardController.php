<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $roleCounts = User::query()
            ->selectRaw("role, COUNT(*) as total, SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_total")
            ->whereIn('role', ['student', 'tutor', 'parent'])
            ->groupBy('role')
            ->get()
            ->keyBy('role');

        $classCounts = ClassRoom::query()
            ->selectRaw("COUNT(*) as total, SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_total")
            ->first();

        $statistics = [
            'students' => (int) ($roleCounts->get('student')->total ?? 0),
            'active_students' => (int) ($roleCounts->get('student')->active_total ?? 0),
            'tutors' => (int) ($roleCounts->get('tutor')->total ?? 0),
            'active_tutors' => (int) ($roleCounts->get('tutor')->active_total ?? 0),
            'classes' => (int) ($classCounts->total ?? 0),
            'active_classes' => (int) ($classCounts->active_total ?? 0),
            'parents' => (int) ($roleCounts->get('parent')->total ?? 0),
            'active_parents' => (int) ($roleCounts->get('parent')->active_total ?? 0),
        ];

        $recentUsers = User::query()
            ->whereIn('role', ['student', 'tutor', 'parent'])
            ->latest()
            ->limit(5)
            ->get(['id', 'name', 'role', 'status', 'created_at']);

        return view('admin.dashboard', compact('statistics', 'recentUsers'));
    }
}
