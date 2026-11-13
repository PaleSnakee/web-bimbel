<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\LearningSchedule;
use App\Models\LearningSession;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function user(string $role, string $status = 'active'): User
    {
        return User::create([
            'name' => ucfirst($role) . ' ' . uniqid(),
            'email' => $role . '-' . uniqid() . '@example.test',
            'password' => Hash::make('password'),
            'role' => $role,
            'status' => $status,
        ]);
    }

    private function classRoom(): ClassRoom
    {
        return ClassRoom::create([
            'code' => 'KLS-' . uniqid(), 'name' => 'Kelas Uji', 'education_level' => 'SD',
            'grade_level' => '1', 'capacity' => 30, 'status' => 'active',
        ]);
    }

    private function tutor(): Tutor
    {
        return $this->user('tutor')->tutor()->create();
    }

    private function student(ClassRoom $classRoom, string $status = 'active'): Student
    {
        return $this->user('student', $status)->student()->create([
            'class_room_id' => $classRoom->id,
            'nis' => 'NIS-' . uniqid(),
            'education_level' => 'SD',
        ]);
    }

    private function learningSession(Tutor $tutor, ClassRoom $classRoom, string $status = 'scheduled'): LearningSession
    {
        $subject = Subject::create([
            'code' => 'SUB-' . uniqid(), 'name' => 'Matematika ' . uniqid(), 'education_level' => 'SD', 'status' => 'active',
        ]);
        $tutor->subjects()->attach($subject);
        $schedule = LearningSchedule::create([
            'class_room_id' => $classRoom->id, 'tutor_id' => $tutor->id, 'subject_id' => $subject->id,
            'day_of_week' => 'monday', 'start_time' => '09:00', 'end_time' => '11:00',
            'effective_from' => '2026-01-01', 'effective_until' => '2026-12-31', 'status' => 'active',
        ]);

        return LearningSession::create([
            'learning_schedule_id' => $schedule->id, 'session_date' => '2026-10-05',
            'start_time' => '09:00', 'end_time' => '11:00', 'status' => $status,
        ]);
    }

    private function payload(Student $student, string $status = 'present'): array
    {
        return ['attendances' => [[
            'student_id' => $student->id,
            'status' => $status,
            'notes' => 'Catatan uji',
        ]]];
    }

    public function test_admin_can_view_attendance_index(): void
    {
        $this->actingAs($this->user('admin'))
            ->get(route('admin.student-attendances.index'))
            ->assertOk()->assertSee('Absensi Siswa');
    }

    public function test_tutor_can_view_only_own_sessions(): void
    {
        $classRoom = $this->classRoom();
        $tutor = $this->tutor();
        $ownSession = $this->learningSession($tutor, $classRoom);
        $otherSession = $this->learningSession($this->tutor(), $this->classRoom());

        $this->actingAs($tutor->user)
            ->get(route('tutor.attendances.index'))
            ->assertOk()->assertSee($ownSession->learningSchedule->subject->name)
            ->assertDontSee($otherSession->learningSchedule->subject->name);
    }

    public function test_tutor_cannot_open_or_update_another_tutors_learningSession(): void
    {
        $owner = $this->tutor();
        $session = $this->learningSession($owner, $this->classRoom());
        $student = $this->student($session->learningSchedule->classRoom);
        $otherTutor = $this->tutor();

        $this->actingAs($otherTutor->user)
            ->get(route('tutor.attendances.edit', $session))
            ->assertNotFound();

        $this->actingAs($otherTutor->user)
            ->put(route('tutor.attendances.update', $session), $this->payload($student))
            ->assertNotFound();
    }

    public function test_tutor_can_create_and_update_attendance_with_recorder(): void
    {
        $tutor = $this->tutor();
        $session = $this->learningSession($tutor, $this->classRoom());
        $student = $this->student($session->learningSchedule->classRoom);

        $this->actingAs($tutor->user)
            ->put(route('tutor.attendances.update', $session), $this->payload($student))
            ->assertRedirect(route('tutor.attendances.index'));

        $this->assertDatabaseHas('student_attendances', [
            'learning_session_id' => $session->id, 'student_id' => $student->id,
            'status' => 'present', 'recorded_by' => $tutor->user_id,
        ]);

        $this->actingAs($tutor->user)
            ->put(route('tutor.attendances.update', $session), $this->payload($student, 'sick'))
            ->assertRedirect(route('tutor.attendances.index'));

        $this->assertSame(1, StudentAttendance::count());
        $this->assertDatabaseHas('student_attendances', ['student_id' => $student->id, 'status' => 'sick']);
    }

    public function test_admin_can_view_session_detail_filter_and_correct_attendance(): void
    {
        $admin = $this->user('admin');
        $tutor = $this->tutor();
        $session = $this->learningSession($tutor, $this->classRoom());
        $student = $this->student($session->learningSchedule->classRoom);
        StudentAttendance::create([
            'learning_session_id' => $session->id, 'student_id' => $student->id,
            'status' => 'present', 'recorded_by' => $tutor->user_id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.student-attendances.index', ['status' => 'present']))
            ->assertOk()->assertSee($student->nis);
        $this->actingAs($admin)
            ->get(route('admin.student-attendances.show', $session))
            ->assertOk()->assertSee('Detail Absensi Sesi')->assertSee('Hadir: 1');
        $this->actingAs($admin)
            ->put(route('admin.student-attendances.update', $session), $this->payload($student, 'permission'))
            ->assertRedirect(route('admin.student-attendances.show', $session));

        $this->assertDatabaseHas('student_attendances', ['student_id' => $student->id, 'status' => 'permission', 'recorded_by' => $admin->id]);
    }

    public function test_student_from_another_class_inactive_student_and_invalid_status_are_rejected(): void
    {
        $tutor = $this->tutor();
        $session = $this->learningSession($tutor, $this->classRoom());
        $otherStudent = $this->student($this->classRoom());
        $inactiveStudent = $this->student($session->learningSchedule->classRoom, 'inactive');

        $this->actingAs($tutor->user)
            ->put(route('tutor.attendances.update', $session), $this->payload($otherStudent))
            ->assertSessionHasErrors('attendances');
        $this->actingAs($tutor->user)
            ->put(route('tutor.attendances.update', $session), $this->payload($inactiveStudent))
            ->assertSessionHasErrors('attendances');
        $this->actingAs($tutor->user)
            ->put(route('tutor.attendances.update', $session), $this->payload($inactiveStudent, 'invalid'))
            ->assertSessionHasErrors('attendances.0.status');
    }

    public function test_cancelled_session_rejects_attendance_and_mass_save_is_atomic(): void
    {
        $tutor = $this->tutor();
        $cancelled = $this->learningSession($tutor, $this->classRoom(), 'cancelled');
        $student = $this->student($cancelled->learningSchedule->classRoom);

        $this->actingAs($tutor->user)
            ->put(route('tutor.attendances.update', $cancelled), $this->payload($student))
            ->assertSessionHasErrors('learning_session_id');

        $session = $this->learningSession($tutor, $this->classRoom());
        $validStudent = $this->student($session->learningSchedule->classRoom);
        $wrongStudent = $this->student($this->classRoom());
        $payload = ['attendances' => [
            ['student_id' => $validStudent->id, 'status' => 'present'],
            ['student_id' => $wrongStudent->id, 'status' => 'present'],
        ]];

        $this->actingAs($tutor->user)
            ->put(route('tutor.attendances.update', $session), $payload)
            ->assertSessionHasErrors('attendances');

        $this->assertDatabaseMissing('student_attendances', ['learning_session_id' => $session->id]);
    }

    public function test_duplicate_payload_and_unauthorized_roles_are_rejected(): void
    {
        $tutor = $this->tutor();
        $session = $this->learningSession($tutor, $this->classRoom());
        $student = $this->student($session->learningSchedule->classRoom);
        $payload = ['attendances' => [
            ['student_id' => $student->id, 'status' => 'present'],
            ['student_id' => $student->id, 'status' => 'absent'],
        ]];

        $this->actingAs($tutor->user)
            ->put(route('tutor.attendances.update', $session), $payload)
            ->assertSessionHasErrors('attendances');
        $this->assertSame(0, StudentAttendance::count());

        $this->actingAs($this->user('student'))
            ->get(route('admin.student-attendances.index'))
            ->assertRedirect('/login');
        $this->actingAs($this->user('admin'))
            ->get(route('tutor.attendances.index'))
            ->assertRedirect('/login');
    }
}
