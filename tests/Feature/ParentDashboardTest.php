<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\Guardian;
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

class ParentDashboardTest extends TestCase
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

    private function createParent(string $status = 'active', string $name = 'Bambang Sudiro'): Guardian
    {
        $user = User::create([
            'name' => $name,
            'email' => 'parent-' . uniqid() . '@example.test',
            'password' => Hash::make('password'),
            'role' => 'parent',
            'status' => $status,
        ]);

        return $user->guardian()->create([
            'phone' => '08123456789',
            'address' => 'Jl. Merdeka No. 10',
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

    private function createStudent(?ClassRoom $classRoom = null, string $name = 'Siswa Satu'): Student
    {
        $user = User::create([
            'name' => $name,
            'email' => 'student-' . uniqid() . '@example.test',
            'password' => Hash::make('password'),
            'role' => 'student',
            'status' => 'active',
        ]);

        return $user->student()->create([
            'class_room_id' => $classRoom?->id,
            'nis' => 'NIS-' . uniqid(),
            'education_level' => 'SD',
        ]);
    }

    private function createTryout(ClassRoom $classRoom): Tryout
    {
        $subject = Subject::create([
            'code' => 'SUB-' . uniqid(),
            'name' => 'Matematika SD',
            'education_level' => 'SD',
            'status' => 'active',
        ]);

        $tryout = Tryout::create([
            'title' => 'TO Evaluasi ' . uniqid(),
            'subject_id' => $subject->id,
            'start_at' => now()->subHours(2),
            'end_at' => now()->addHours(2),
            'duration_minutes' => 60,
            'status' => 'published',
        ]);

        $tryout->classRooms()->attach($classRoom);

        return $tryout;
    }

    private function createAttendance(Student $student, ClassRoom $classRoom, string $status = 'present'): StudentAttendance
    {
        $tutorUser = User::create([
            'name' => 'Tutor ' . uniqid(),
            'email' => 'tutor-' . uniqid() . '@example.test',
            'password' => Hash::make('password'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $tutor = $tutorUser->tutor()->create([
            'phone' => '0899999999',
            'gender' => 'male',
        ]);

        $subject = Subject::create([
            'code' => 'SUB-' . uniqid(),
            'name' => 'Bahasa Indonesia',
            'education_level' => 'SD',
            'status' => 'active',
        ]);

        $schedule = LearningSchedule::create([
            'class_room_id' => $classRoom->id,
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'day_of_week' => 'wednesday',
            'start_time' => '08:00',
            'end_time' => '09:30',
            'effective_from' => now()->subMonth(),
            'effective_until' => now()->addMonth(),
            'status' => 'active',
        ]);

        $session = LearningSession::create([
            'learning_schedule_id' => $schedule->id,
            'session_date' => now()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '09:30',
            'status' => 'completed',
            'topic' => 'Menulis Karangan',
        ]);

        return StudentAttendance::create([
            'learning_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => $status,
            'notes' => 'Hadir tepat waktu',
        ]);
    }

    public function test_guest_cannot_access_parent_dashboard(): void
    {
        $this->get(route('parent.dashboard'))->assertRedirect(route('login'));
    }

    public function test_admin_role_cannot_access_parent_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin)->get(route('parent.dashboard'))->assertRedirect('/login');
    }

    public function test_tutor_role_cannot_access_parent_dashboard(): void
    {
        $tutor = User::create([
            'name' => 'Tutor User',
            'email' => 'tutor@example.test',
            'password' => Hash::make('password'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $this->actingAs($tutor)->get(route('parent.dashboard'))->assertRedirect('/login');
    }

    public function test_student_role_cannot_access_parent_dashboard(): void
    {
        $classRoom = $this->createClassRoom();
        $student = $this->createStudent($classRoom);

        $this->actingAs($student->user)->get(route('parent.dashboard'))->assertRedirect('/login');
    }

    public function test_active_parent_can_access_dashboard(): void
    {
        $guardian = $this->createParent('active', 'Ibu Nurhayati');

        $response = $this->actingAs($guardian->user)->get(route('parent.dashboard'));

        $response->assertOk()
            ->assertSee('El Renaissance')
            ->assertSee('Ibu Nurhayati')
            ->assertSee('DASHBOARD ORANG TUA')
            ->assertSee('Total Anak Terdaftar')
            ->assertSee('Try Out Diselesaikan')
            ->assertSee('Rata-rata Nilai Try Out')
            ->assertSee('Kehadiran Sesi Belajar');
    }

    public function test_inactive_parent_cannot_access_parent_dashboard(): void
    {
        $guardian = $this->createParent('inactive');

        $this->actingAs($guardian->user)->get(route('parent.dashboard'))->assertRedirect('/login');
    }

    public function test_parent_only_sees_connected_children(): void
    {
        $classRoom = $this->createClassRoom();
        $parentA = $this->createParent('active', 'Pak Joko');
        $parentB = $this->createParent('active', 'Pak Hendra');

        $childA = $this->createStudent($classRoom, 'Anak Kandung Joko');
        $childB = $this->createStudent($classRoom, 'Anak Orang Lain');

        // Connect childA to parentA
        $parentA->students()->attach($childA);
        // Connect childB to parentB
        $parentB->students()->attach($childB);

        $response = $this->actingAs($parentA->user)->get(route('parent.dashboard'));

        $response->assertOk()
            ->assertSee('Anak Kandung Joko')
            ->assertDontSee('Anak Orang Lain');
    }

    public function test_parent_cannot_view_other_parents_child_detail(): void
    {
        $classRoom = $this->createClassRoom();
        $parentA = $this->createParent('active', 'Pak Joko');
        $parentB = $this->createParent('active', 'Pak Hendra');

        $childA = $this->createStudent($classRoom, 'Anak Joko');
        $childB = $this->createStudent($classRoom, 'Anak Hendra');

        $parentA->students()->attach($childA);
        $parentB->students()->attach($childB);

        // Parent A can view child A
        $this->actingAs($parentA->user)
            ->get(route('parent.children.show', $childA))
            ->assertOk()
            ->assertSee('Anak Joko');

        // Parent A CANNOT view child B (should return 404)
        $this->actingAs($parentA->user)
            ->get(route('parent.children.show', $childB))
            ->assertNotFound();
    }

    public function test_parent_cannot_see_other_students_tryout_results(): void
    {
        $classRoom = $this->createClassRoom();
        $parentA = $this->createParent('active', 'Pak Joko');
        $parentB = $this->createParent('active', 'Pak Hendra');

        $childA = $this->createStudent($classRoom, 'Anak Joko');
        $childB = $this->createStudent($classRoom, 'Anak Hendra');

        $parentA->students()->attach($childA);
        $parentB->students()->attach($childB);

        $tryout = $this->createTryout($classRoom);

        // Attempt for Child A
        TryoutAttempt::create([
            'tryout_id' => $tryout->id,
            'student_id' => $childA->id,
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

        // Attempt for Child B
        TryoutAttempt::create([
            'tryout_id' => $tryout->id,
            'student_id' => $childB->id,
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

        // Parent A on tryouts list should see Child A's attempt, but NOT Child B
        $response = $this->actingAs($parentA->user)->get(route('parent.tryouts.index'));

        $response->assertOk()
            ->assertSee('Anak Joko')
            ->assertSee('80')
            ->assertDontSee('Anak Hendra');
    }

    public function test_parent_with_multiple_children_sees_all_children(): void
    {
        $classRoom = $this->createClassRoom();
        $parent = $this->createParent('active', 'Ibu Kartini');

        $child1 = $this->createStudent($classRoom, 'Anak Pertama Kartini');
        $child2 = $this->createStudent($classRoom, 'Anak Kedua Kartini');

        $parent->students()->attach([$child1->id, $child2->id]);

        $response = $this->actingAs($parent->user)->get(route('parent.dashboard'));

        $response->assertOk()
            ->assertSee('Anak Pertama Kartini')
            ->assertSee('Anak Kedua Kartini');

        $response->assertViewHas('statistics', function ($stats) {
            return $stats['total_children'] === 2;
        });
    }

    public function test_dashboard_displays_database_statistics_accurately(): void
    {
        $classRoom = $this->createClassRoom();
        $parent = $this->createParent('active', 'Ayah Budi');
        $child = $this->createStudent($classRoom, 'Budi Junior');

        $parent->students()->attach($child);

        $tryout1 = $this->createTryout($classRoom);
        $tryout2 = $this->createTryout($classRoom);

        // Child completed tryout 1 with score 80.00
        TryoutAttempt::create([
            'tryout_id' => $tryout1->id,
            'student_id' => $child->id,
            'started_at' => now()->subHours(2),
            'expires_at' => now()->subHour(),
            'submitted_at' => now()->subHour(),
            'status' => 'submitted',
            'total_questions' => 10,
            'answered_questions' => 10,
            'correct_answers' => 8,
            'incorrect_answers' => 2,
            'unanswered_questions' => 0,
            'total_points' => 100,
            'earned_points' => 80,
            'final_score' => 80.00,
            'graded_at' => now()->subHour(),
        ]);

        // Child completed tryout 2 with score 90.00
        TryoutAttempt::create([
            'tryout_id' => $tryout2->id,
            'student_id' => $child->id,
            'started_at' => now()->subHours(1),
            'expires_at' => now()->subMinutes(30),
            'submitted_at' => now()->subMinutes(30),
            'status' => 'submitted',
            'total_questions' => 10,
            'answered_questions' => 10,
            'correct_answers' => 9,
            'incorrect_answers' => 1,
            'unanswered_questions' => 0,
            'total_points' => 100,
            'earned_points' => 90,
            'final_score' => 90.00,
            'graded_at' => now()->subMinutes(30),
        ]);

        // 1 present attendance
        $this->createAttendance($child, $classRoom, 'present');

        $response = $this->actingAs($parent->user)->get(route('parent.dashboard'));

        $response->assertOk();

        $response->assertViewHas('statistics', function ($stats) {
            return $stats['total_children'] === 1
                && $stats['completed_tryouts'] === 2
                && $stats['average_score'] == 85.0
                && $stats['graded_attempts_count'] === 2
                && $stats['attendance_present'] === 1
                && $stats['attendance_total'] === 1;
        });
    }

    public function test_dashboard_displays_empty_state_when_parent_has_no_children(): void
    {
        $parent = $this->createParent('active', 'Pak Kosong');

        $response = $this->actingAs($parent->user)->get(route('parent.dashboard'));

        $response->assertOk()
            ->assertSee('Belum ada anak yang terhubung')
            ->assertSee('Belum ada riwayat hasil Try Out')
            ->assertSee('Belum ada catatan absensi');

        $response->assertViewHas('statistics', function ($stats) {
            return $stats['total_children'] === 0
                && $stats['completed_tryouts'] === 0
                && $stats['average_score'] === null
                && $stats['attendance_present'] === 0;
        });
    }

    public function test_parent_cannot_mutate_academic_data_via_dashboard_routes(): void
    {
        $classRoom = $this->createClassRoom();
        $parent = $this->createParent('active');
        $child = $this->createStudent($classRoom);
        $parent->students()->attach($child);

        // Attempt POST / PUT to child detail or tryout
        $this->actingAs($parent->user)
            ->post(route('parent.children.show', $child), ['score' => 100])
            ->assertStatus(405); // Method Not Allowed

        $this->actingAs($parent->user)
            ->put(route('parent.children.show', $child), ['score' => 100])
            ->assertStatus(405); // Method Not Allowed

        $this->actingAs($parent->user)
            ->delete(route('parent.children.show', $child))
            ->assertStatus(405); // Method Not Allowed
    }
}
