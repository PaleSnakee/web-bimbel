<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\LearningSchedule;
use App\Models\LearningSession;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\Tryout;
use App\Models\TryoutAttempt;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TutorDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Carbon::setTestNow('2026-11-25 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function createTutor(string $status = 'active', string $name = 'Tutor Pengajar'): Tutor
    {
        $user = User::create([
            'name' => $name,
            'email' => 'tutor-' . uniqid() . '@example.test',
            'password' => Hash::make('password'),
            'role' => 'tutor',
            'status' => $status,
        ]);

        return $user->tutor()->create([
            'phone' => '081234567890',
            'gender' => 'male',
        ]);
    }

    private function createClassRoom(string $name = 'Kelas 6A'): ClassRoom
    {
        return ClassRoom::create([
            'code' => 'KLS-' . uniqid(),
            'name' => $name,
            'education_level' => 'SD',
            'grade_level' => '6',
            'capacity' => 30,
            'status' => 'active',
        ]);
    }

    private function createSubject(string $name = 'Matematika SD'): Subject
    {
        return Subject::create([
            'code' => 'SUB-' . uniqid(),
            'name' => $name,
            'education_level' => 'SD',
            'status' => 'active',
        ]);
    }

    private function createStudent(ClassRoom $classRoom, string $name = 'Siswa'): Student
    {
        $user = User::create([
            'name' => $name,
            'email' => 'student-' . uniqid() . '@example.test',
            'password' => Hash::make('password'),
            'role' => 'student',
            'status' => 'active',
        ]);

        return $user->student()->create([
            'class_room_id' => $classRoom->id,
            'nis' => 'NIS-' . uniqid(),
            'education_level' => 'SD',
        ]);
    }

    private function createSchedule(Tutor $tutor, ClassRoom $classRoom, Subject $subject, string $status = 'active'): LearningSchedule
    {
        return LearningSchedule::create([
            'class_room_id' => $classRoom->id,
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'day_of_week' => 'monday',
            'start_time' => '08:00',
            'end_time' => '09:30',
            'effective_from' => now()->subMonth(),
            'effective_until' => now()->addMonth(),
            'status' => $status,
        ]);
    }

    private function createSession(LearningSchedule $schedule, string $status = 'scheduled', ?string $date = null): LearningSession
    {
        return LearningSession::create([
            'learning_schedule_id' => $schedule->id,
            'session_date' => $date ?? now()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '09:30',
            'status' => $status,
            'topic' => 'Materi Pecahan',
        ]);
    }

    private function createTryout(Tutor $tutor, Subject $subject, ClassRoom $classRoom, string $status = 'published'): Tryout
    {
        $tryout = Tryout::create([
            'title' => 'TO Mandiri ' . uniqid(),
            'subject_id' => $subject->id,
            'tutor_id' => $tutor->id,
            'start_at' => now()->subHours(1),
            'end_at' => now()->addHours(2),
            'duration_minutes' => 60,
            'status' => $status,
        ]);

        $tryout->classRooms()->attach($classRoom);

        return $tryout;
    }

    public function test_guest_cannot_access_tutor_dashboard(): void
    {
        $this->get(route('tutor.dashboard'))->assertRedirect(route('login'));
    }

    public function test_admin_cannot_access_tutor_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin)->get(route('tutor.dashboard'))->assertRedirect('/login');
    }

    public function test_student_cannot_access_tutor_dashboard(): void
    {
        $classRoom = $this->createClassRoom();
        $student = $this->createStudent($classRoom);

        $this->actingAs($student->user)->get(route('tutor.dashboard'))->assertRedirect('/login');
    }

    public function test_parent_cannot_access_tutor_dashboard(): void
    {
        $parent = User::create([
            'name' => 'Parent User',
            'email' => 'parent@example.test',
            'password' => Hash::make('password'),
            'role' => 'parent',
            'status' => 'active',
        ]);

        $this->actingAs($parent)->get(route('tutor.dashboard'))->assertRedirect('/login');
    }

    public function test_active_tutor_can_access_dashboard(): void
    {
        $tutor = $this->createTutor('active', 'Pak Guru Hidayat');

        $response = $this->actingAs($tutor->user)->get(route('tutor.dashboard'));

        $response->assertOk()
            ->assertSee('El Renaissance')
            ->assertSee('Pak Guru Hidayat')
            ->assertSee('DASHBOARD TUTOR')
            ->assertSee('Total Jadwal Mengajar')
            ->assertSee('Total Sesi Pembelajaran')
            ->assertSee('Total Siswa yang Diajar')
            ->assertSee('Total Absensi Tercatat');
    }

    public function test_inactive_tutor_cannot_access_tutor_dashboard(): void
    {
        $tutor = $this->createTutor('inactive');

        $this->actingAs($tutor->user)->get(route('tutor.dashboard'))->assertRedirect('/login');
    }

    public function test_schedule_statistics_match_database(): void
    {
        $tutor = $this->createTutor('active');
        $classRoom = $this->createClassRoom();
        $subject = $this->createSubject();

        // 2 active schedules, 1 inactive schedule
        $this->createSchedule($tutor, $classRoom, $subject, 'active');
        $this->createSchedule($tutor, $classRoom, $subject, 'active');
        $this->createSchedule($tutor, $classRoom, $subject, 'inactive');

        $response = $this->actingAs($tutor->user)->get(route('tutor.dashboard'));

        $response->assertOk();
        $response->assertViewHas('statistics', function ($stats) {
            return $stats['active_schedules'] === 2;
        });
    }

    public function test_session_statistics_match_database(): void
    {
        $tutor = $this->createTutor('active');
        $classRoom = $this->createClassRoom();
        $subject = $this->createSubject();

        $schedule1 = $this->createSchedule($tutor, $classRoom, $subject, 'active');
        $schedule2 = $this->createSchedule($tutor, $classRoom, $subject, 'active');

        // Sesi: 2 completed, 1 scheduled, 1 cancelled with distinct dates
        $this->createSession($schedule1, 'completed', now()->subDays(2)->toDateString());
        $this->createSession($schedule1, 'completed', now()->subDays(1)->toDateString());
        $this->createSession($schedule2, 'scheduled', now()->toDateString());
        $this->createSession($schedule2, 'cancelled', now()->addDay()->toDateString());

        $response = $this->actingAs($tutor->user)->get(route('tutor.dashboard'));

        $response->assertOk();
        $response->assertViewHas('statistics', function ($stats) {
            return $stats['total_sessions'] === 4;
        });

        $response->assertViewHas('completedSessionsCount', 2)
            ->assertViewHas('scheduledSessionsCount', 1)
            ->assertViewHas('cancelledSessionsCount', 1);
    }

    public function test_total_students_taught_avoids_duplicate_counting(): void
    {
        $tutor = $this->createTutor('active');
        $classRoom = $this->createClassRoom();
        $subject1 = $this->createSubject('Matematika');
        $subject2 = $this->createSubject('Fisika');

        // Tutor mengajar 2 jadwal aktif di kelas yang sama
        $this->createSchedule($tutor, $classRoom, $subject1, 'active');
        $this->createSchedule($tutor, $classRoom, $subject2, 'active');

        // Kelas memiliki 3 siswa aktif
        $this->createStudent($classRoom, 'Siswa A');
        $this->createStudent($classRoom, 'Siswa B');
        $this->createStudent($classRoom, 'Siswa C');

        $response = $this->actingAs($tutor->user)->get(route('tutor.dashboard'));

        $response->assertOk();
        // Siswa tidak boleh terhitung 6 kali, harus tepat 3 siswa unik
        $response->assertViewHas('statistics', function ($stats) {
            return $stats['students_taught'] === 3;
        });
    }

    public function test_tutor_only_sees_own_schedules(): void
    {
        $tutorA = $this->createTutor('active', 'Tutor A');
        $tutorB = $this->createTutor('active', 'Tutor B');

        $classRoom = $this->createClassRoom();
        $subjectA = $this->createSubject('Aljabar Lanjutan');
        $subjectB = $this->createSubject('Biologi Sel');

        $this->createSchedule($tutorA, $classRoom, $subjectA, 'active');
        $this->createSchedule($tutorB, $classRoom, $subjectB, 'active');

        $response = $this->actingAs($tutorA->user)->get(route('tutor.dashboard'));

        $response->assertOk()
            ->assertSee('Aljabar Lanjutan')
            ->assertDontSee('Biologi Sel');
    }

    public function test_tutor_only_sees_own_sessions_and_attendances(): void
    {
        $tutorA = $this->createTutor('active', 'Tutor A');
        $tutorB = $this->createTutor('active', 'Tutor B');

        $classRoom = $this->createClassRoom();
        $subject = $this->createSubject('Kimia');

        $scheduleA = $this->createSchedule($tutorA, $classRoom, $subject, 'active');
        $scheduleB = $this->createSchedule($tutorB, $classRoom, $subject, 'active');

        $sessionA = LearningSession::create([
            'learning_schedule_id' => $scheduleA->id,
            'session_date' => now()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:30',
            'status' => 'completed',
            'topic' => 'Topik Khusus Sesi A',
        ]);

        $sessionB = LearningSession::create([
            'learning_schedule_id' => $scheduleB->id,
            'session_date' => now()->toDateString(),
            'start_time' => '13:00',
            'end_time' => '14:30',
            'status' => 'completed',
            'topic' => 'Topik Khusus Sesi B',
        ]);

        $student = $this->createStudent($classRoom);

        // Attendance for session A recorded by tutor A
        StudentAttendance::create([
            'learning_session_id' => $sessionA->id,
            'student_id' => $student->id,
            'status' => 'present',
            'recorded_by' => $tutorA->user->id,
        ]);

        // Attendance for session B recorded by tutor B
        StudentAttendance::create([
            'learning_session_id' => $sessionB->id,
            'student_id' => $student->id,
            'status' => 'present',
            'recorded_by' => $tutorB->user->id,
        ]);

        $response = $this->actingAs($tutorA->user)->get(route('tutor.dashboard'));

        $response->assertOk()
            ->assertSee('Topik Khusus Sesi A')
            ->assertDontSee('Topik Khusus Sesi B');

        // Recorded attendances by tutor A must be 1, not 2
        $response->assertViewHas('statistics', function ($stats) {
            return $stats['recorded_attendances'] === 1;
        });

        // Attendance summary for tutor A's sessions
        $response->assertViewHas('attendanceSummary', function ($summary) {
            return $summary['present'] === 1
                && $summary['total'] === 1;
        });
    }

    public function test_tutor_only_sees_own_tryouts(): void
    {
        $tutorA = $this->createTutor('active', 'Tutor A');
        $tutorB = $this->createTutor('active', 'Tutor B');

        $classRoom = $this->createClassRoom();
        $subject = $this->createSubject('Geografi');

        $tryoutA = $this->createTryout($tutorA, $subject, $classRoom, 'published');
        $tryoutB = $this->createTryout($tutorB, $subject, $classRoom, 'published');

        $student = $this->createStudent($classRoom);

        // Graded attempt for Tryout A
        TryoutAttempt::create([
            'tryout_id' => $tryoutA->id,
            'student_id' => $student->id,
            'started_at' => now()->subHour(),
            'expires_at' => now(),
            'submitted_at' => now(),
            'status' => 'submitted',
            'total_questions' => 10,
            'answered_questions' => 10,
            'correct_answers' => 8,
            'incorrect_answers' => 2,
            'unanswered_questions' => 0,
            'total_points' => 100,
            'earned_points' => 80,
            'final_score' => 80.00,
            'graded_at' => now(),
        ]);

        // Graded attempt for Tryout B
        TryoutAttempt::create([
            'tryout_id' => $tryoutB->id,
            'student_id' => $student->id,
            'started_at' => now()->subHour(),
            'expires_at' => now(),
            'submitted_at' => now(),
            'status' => 'submitted',
            'total_questions' => 10,
            'answered_questions' => 10,
            'correct_answers' => 10,
            'incorrect_answers' => 0,
            'unanswered_questions' => 0,
            'total_points' => 100,
            'earned_points' => 100,
            'final_score' => 100.00,
            'graded_at' => now(),
        ]);

        $response = $this->actingAs($tutorA->user)->get(route('tutor.dashboard'));

        $response->assertOk()
            ->assertSee($tryoutA->title)
            ->assertDontSee($tryoutB->title);

        $response->assertViewHas('tryoutStats', function ($stats) {
            return $stats['total'] === 1 && $stats['published'] === 1;
        });

        $response->assertViewHas('gradedAttemptsCount', 1)
            ->assertViewHas('averageTryoutScore', 80.0);
    }

    public function test_dashboard_displays_empty_state_when_tutor_has_no_schedules_or_activities(): void
    {
        $tutor = $this->createTutor('active');

        $response = $this->actingAs($tutor->user)->get(route('tutor.dashboard'));

        $response->assertOk()
            ->assertSee('Belum ada jadwal mengajar aktif')
            ->assertSee('Belum ada sesi pembelajaran')
            ->assertSee('Belum ada data absensi tercatat')
            ->assertSee('Belum ada pengerjaan Try Out yang dinilai');

        $response->assertViewHas('statistics', function ($stats) {
            return $stats['active_schedules'] === 0
                && $stats['total_sessions'] === 0
                && $stats['students_taught'] === 0
                && $stats['recorded_attendances'] === 0;
        });
    }

    public function test_dashboard_does_not_break_existing_tutor_features(): void
    {
        $tutor = $this->createTutor('active');
        $classRoom = $this->createClassRoom();
        $subject = $this->createSubject();
        $schedule = $this->createSchedule($tutor, $classRoom, $subject);
        $session = $this->createSession($schedule);
        $tryout = $this->createTryout($tutor, $subject, $classRoom);

        // Verify existing tutor routes still work without issues
        $this->actingAs($tutor->user)->get(route('tutor.attendances.index'))->assertOk();
        $this->actingAs($tutor->user)->get(route('tutor.attendances.edit', $session))->assertOk();
        $this->actingAs($tutor->user)->get(route('tutor.tryouts.index'))->assertOk();
        $this->actingAs($tutor->user)->get(route('tutor.tryouts.show', $tryout))->assertOk();
        $this->actingAs($tutor->user)->get(route('tutor.tryout-results.index'))->assertOk();
    }
}
