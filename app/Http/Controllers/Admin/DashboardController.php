<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $statistics = [
            'students' => User::query()->where('role', 'student')->count(),
            'tutors' => User::query()->where('role', 'tutor')->count(),
            'classes' => null,
            'attendance' => null,
        ];

        return view('admin.dashboard', compact('statistics'));
    }
}
