<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tryout;
use App\Models\TryoutAttempt;
use App\Models\TryoutQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentDashboardTest extends TestCase
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

    private function createStudent(?ClassRoom $classRoom = null, string $status = 'active', string $name = 'Budi Santoso'): Student
    {
        $user = User::create([
            'name' => $name,
            'email' => 'student-' . uniqid() . '@example.test',
            'password' => Hash::make('password'),
            'role' => 'student',
            'status' => $status,
        ]);

        return $user->student()->create([
            'class_room_id' => $classRoom?->id,
            'nis' => 'NIS-' . uniqid(),
            'education_level' => 'SD',
        ]);
    }

    private function createTryout(ClassRoom $classRoom, array $overrides = []): Tryout
    {
        $subject = Subject::create([
            'code' => 'SUB-' . uniqid(),
            'name' => 'Matematika Dasar',
            'education_level' => 'SD',
            'status' => 'active',
        ]);

        $tryout = Tryout::create(array_merge([
            'title' => 'Try Out ' . uniqid(),
            'subject_id' => $subject->id,
            'start_at' => now()->subHours(1),
            'end_at' => now()->addHours(3),
            'duration_minutes' => 60,
            'status' => 'published',
        ], $overrides));

        $tryout->classRooms()->attach($classRoom);

        return $tryout;
    }

    private function createQuestionWithOptions(Tryout $tryout, int $points = 10): TryoutQuestion
    {
        $question = $tryout->questions()->create([
            'question_text' => 'Berapakah 2 + 2?',
            'points' => $points,
            'order_number' => 1,
        ]);

        $question->options()->createMany([
            ['option_label' => 'A', 'option_text' => '3', 'is_correct' => false, 'order_number' => 1],
            ['option_label' => 'B', 'option_text' => '4', 'is_correct' => true, 'order_number' => 2],
            ['option_label' => 'C', 'option_text' => '5', 'is_correct' => false, 'order_number' => 3],
        ]);

        return $question;
    }

    public function test_guest_cannot_access_student_dashboard(): void
    {
        $this->get(route('student.dashboard'))->assertRedirect(route('login'));
    }

    public function test_non_student_roles_cannot_access_student_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $tutor = User::create([
            'name' => 'Tutor User',
            'email' => 'tutor@example.test',
            'password' => Hash::make('password'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $this->actingAs($admin)->get(route('student.dashboard'))->assertRedirect('/login');
        $this->actingAs($tutor)->get(route('student.dashboard'))->assertRedirect('/login');
    }

    public function test_inactive_student_cannot_access_student_dashboard(): void
    {
        $classRoom = $this->createClassRoom();
        $student = $this->createStudent($classRoom, 'inactive');

        $this->actingAs($student->user)->get(route('student.dashboard'))->assertRedirect('/login');
    }

    public function test_student_cannot_access_admin_or_tutor_dashboard(): void
    {
        $classRoom = $this->createClassRoom();
        $student = $this->createStudent($classRoom);

        $this->actingAs($student->user)->get(route('admin.dashboard'))->assertRedirect('/login');
        $this->actingAs($student->user)->get(route('tutor.dashboard'))->assertRedirect('/login');
    }

    public function test_student_can_view_dashboard_with_layout_and_profile_info(): void
    {
        $classRoom = $this->createClassRoom('Kelas 6 Bintang');
        $student = $this->createStudent($classRoom, 'active', 'Ahmad Dahlan');

        $response = $this->actingAs($student->user)->get(route('student.dashboard'));

        $response->assertOk()
            ->assertSee('El Renaissance')
            ->assertSee('Ahmad Dahlan')
            ->assertSee('Kelas 6 Bintang')
            ->assertSee('DASHBOARD SISWA')
            ->assertSee('Try Out Tersedia')
            ->assertSee('Sudah Dikerjakan')
            ->assertSee('Belum Dikerjakan')
            ->assertSee('Rata-rata Nilai')
            ->assertSee('Daftar Try Out')
            ->assertSee('Riwayat Pengerjaan')
            ->assertSee('Informasi Hasil Try Out');
    }

    public function test_dashboard_statistics_are_scoped_to_student_class_and_database(): void
    {
        $classA = $this->createClassRoom('Kelas A');
        $classB = $this->createClassRoom('Kelas B');

        $studentA = $this->createStudent($classA, 'active', 'Siswa Kelas A');
        $studentB = $this->createStudent($classB, 'active', 'Siswa Kelas B');

        // 3 Tryouts for Class A
        $tryoutA1 = $this->createTryout($classA, ['title' => 'TO Matematika A1']);
        $tryoutA2 = $this->createTryout($classA, ['title' => 'TO IPA A2']);
        $tryoutA3 = $this->createTryout($classA, ['title' => 'TO Bahasa A3']);

        // 1 Tryout for Class B only
        $this->createTryout($classB, ['title' => 'TO Khusus B']);

        // Student A completed tryout A1 with score 80.00
        TryoutAttempt::create([
            'tryout_id' => $tryoutA1->id,
            'student_id' => $studentA->id,
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

        // Student A completed tryout A2 with score 90.00
        TryoutAttempt::create([
            'tryout_id' => $tryoutA2->id,
            'student_id' => $studentA->id,
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

        // Student B completed something else with 100
        TryoutAttempt::create([
            'tryout_id' => $tryoutA1->id,
            'student_id' => $studentB->id,
            'started_at' => now()->subHours(1),
            'expires_at' => now()->subMinutes(20),
            'submitted_at' => now()->subMinutes(20),
            'status' => 'submitted',
            'total_questions' => 10,
            'answered_questions' => 10,
            'correct_answers' => 10,
            'incorrect_answers' => 0,
            'unanswered_questions' => 0,
            'total_points' => 100,
            'earned_points' => 100,
            'final_score' => 100.00,
            'graded_at' => now()->subMinutes(20),
        ]);

        $response = $this->actingAs($studentA->user)->get(route('student.dashboard'));

        $response->assertOk();

        // Total available for class A: 3
        // Completed by student A: 2
        // Pending: 1
        // Average score for student A: (80 + 90) / 2 = 85.0
        $response->assertViewHas('statistics', function ($stats) {
            return $stats['available_tryouts'] === 3
                && $stats['completed_tryouts'] === 2
                && $stats['pending_tryouts'] === 1
                && $stats['average_score'] == 85.0
                && $stats['graded_attempts_count'] === 2;
        });

        // Visible tryouts should include A1, A2, A3, but NOT TO Khusus B
        $response->assertSee('TO Matematika A1')
            ->assertSee('TO IPA A2')
            ->assertSee('TO Bahasa A3')
            ->assertDontSee('TO Khusus B');
    }

    public function test_dashboard_displays_graded_attempt_snapshot_and_history(): void
    {
        $classRoom = $this->createClassRoom('Kelas 6 Reguler');
        $student = $this->createStudent($classRoom);
        $tryout = $this->createTryout($classRoom, ['title' => 'TO Sains Nasional']);

        $attempt = TryoutAttempt::create([
            'tryout_id' => $tryout->id,
            'student_id' => $student->id,
            'started_at' => now()->subHours(2),
            'expires_at' => now()->subHour(),
            'submitted_at' => now()->subHour(),
            'status' => 'submitted',
            'total_questions' => 20,
            'answered_questions' => 18,
            'correct_answers' => 15,
            'incorrect_answers' => 3,
            'unanswered_questions' => 2,
            'total_points' => 100,
            'earned_points' => 75,
            'final_score' => 75.00,
            'graded_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($student->user)->get(route('student.dashboard'));

        $response->assertOk()
            ->assertSee('TO Sains Nasional')
            ->assertSee('75')
            ->assertSee('15 soal') // Jawaban Benar
            ->assertSee('3 soal')  // Jawaban Salah
            ->assertSee('2 soal')  // Jawaban Kosong
            ->assertSee('75 / 100') // Poin diperoleh
            ->assertSee(route('student.tryouts.attempts.result', [$tryout, $attempt]))
            ->assertSee(route('student.tryouts.attempts.review', [$tryout, $attempt]));
    }

    public function test_expired_in_progress_attempt_is_finalized_on_dashboard_visit(): void
    {
        $classRoom = $this->createClassRoom();
        $student = $this->createStudent($classRoom);
        $tryout = $this->createTryout($classRoom, ['title' => 'TO Timer Test']);
        $question = $this->createQuestionWithOptions($tryout, 10);

        // Create an attempt whose expires_at is in the past
        $attempt = TryoutAttempt::create([
            'tryout_id' => $tryout->id,
            'student_id' => $student->id,
            'started_at' => now()->subHours(2),
            'expires_at' => now()->subMinute(),
            'status' => 'in_progress',
        ]);

        $attempt->answers()->create([
            'tryout_question_id' => $question->id,
            'tryout_question_option_id' => $question->options->firstWhere('is_correct', true)->id,
        ]);

        $this->actingAs($student->user)->get(route('student.dashboard'))->assertOk();

        $attempt->refresh();
        $this->assertSame('expired', $attempt->status);
        $this->assertNotNull($attempt->graded_at);
        $this->assertEquals(100.0, (float) $attempt->final_score);
    }
}
