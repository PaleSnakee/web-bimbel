<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Tryout;
use App\Models\TryoutAttempt;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TryoutResultController extends Controller
{
    public function index(Request $request): View
    {
        $results = TryoutAttempt::query()
            ->with([
                'tryout:id,title,subject_id',
                'tryout.subject:id,code,name',
                'student:id,user_id,class_room_id,nis',
                'student.user:id,name',
                'student.classRoom:id,code,name',
            ])
            ->whereNotNull('graded_at')
            ->when($request->filled('tryout_id'), fn ($query) => $query->where('tryout_id', $request->integer('tryout_id')))
            ->when($request->filled('class_room_id'), fn ($query) => $query->whereHas('student', fn ($studentQuery) => $studentQuery->where('class_room_id', $request->integer('class_room_id'))))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->whereHas('student.user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
            })
            ->when($request->filled('min_score'), fn ($query) => $query->where('final_score', '>=', $request->input('min_score')))
            ->when($request->filled('max_score'), fn ($query) => $query->where('final_score', '<=', $request->input('max_score')))
            ->latest('graded_at')
            ->paginate(15)
            ->appends($request->query());

        return view('admin.tryout-results.index', [
            'results' => $results,
            'tryouts' => Tryout::query()->orderBy('title')->get(['id', 'title']),
            'classRooms' => ClassRoom::query()->orderBy('name')->get(['id', 'code', 'name']),
        ]);
    }
}