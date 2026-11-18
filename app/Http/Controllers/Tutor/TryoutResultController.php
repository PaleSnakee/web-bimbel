<?php

namespace App\Http\Controllers\Tutor;

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
        $tutor = $request->user()->tutor;
        abort_unless($tutor, 403);

        $results = TryoutAttempt::query()
            ->with([
                'tryout:id,title,subject_id,tutor_id',
                'tryout.subject:id,code,name',
                'student:id,user_id,class_room_id,nis',
                'student.user:id,name',
                'student.classRoom:id,code,name',
            ])
            ->whereNotNull('graded_at')
            ->whereHas('tryout', fn ($query) => $query->where('tutor_id', $tutor->id))
            ->when($request->filled('tryout_id'), fn ($query) => $query->where('tryout_id', $request->integer('tryout_id')))
            ->when($request->filled('class_room_id'), fn ($query) => $query->whereHas('student', fn ($studentQuery) => $studentQuery->where('class_room_id', $request->integer('class_room_id'))))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->whereHas('student.user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
            })
            ->latest('graded_at')
            ->paginate(15)
            ->appends($request->query());

        $tryouts = Tryout::query()
            ->where('tutor_id', $tutor->id)
            ->orderBy('title')
            ->get(['id', 'title']);

        $classRoomIds = $tutor->learningSchedules()->pluck('class_room_id')->unique();

        return view('tutor.tryout-results.index', [
            'results' => $results,
            'tryouts' => $tryouts,
            'classRooms' => ClassRoom::query()->whereIn('id', $classRoomIds)->orderBy('name')->get(['id', 'code', 'name']),
        ]);
    }
}