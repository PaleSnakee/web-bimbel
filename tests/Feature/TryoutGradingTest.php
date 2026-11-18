<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tryout;
use App\Models\TryoutAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TryoutGradingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Carbon::setTestNow('2026-11-02 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function student(ClassRoom $classRoom): Student
    {
        $user = User::create([
            'name' => 'Siswa ' . uniqid(),
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

    private function fixture(): array
    {
        $classRoom = ClassRoom::create([
            'code' => 'KLS-' . uniqid(),
            'name' => 'Kelas Uji',
            'education_level' => 'SD',
            'grade_level' => '6',
            'capacity' => 30,
            'status' => 'active',
        ]);
        $subject = Subject::create([
            'code' => 'SUB-' . uniqid(),
            'name' => 'Matematika',
            'education_level' => 'SD',
            'status' => 'active',
        ]);
        $tryout = Tryout::create([
            'title' => 'Try Out Penilaian',
            'subject_id' => $subject->id,
            'start_at' => now()->subMinute(),
            'end_at' => now()->addHour(),
            'duration_minutes' => 30,
            'status' => 'published',
        ]);
        $tryout->classRooms()->attach($classRoom);

        $questions = collect([10, 20, 15])->map(function (int $points, int $index) use ($tryout) {
            $question = $tryout->questions()->create([
                'question_text' => 'Soal ' . ($index + 1),
                'points' => $points,
                'order_number' => $index + 1,
            ]);
            foreach (['A', 'B', 'C', 'D'] as $optionIndex => $label) {
                $question->options()->create([
                    'option_label' => $label,
                    'option_text' => 'Pilihan ' . $label,
                    'is_correct' => $label === 'B',
                    'order_number' => $optionIndex + 1,
                ]);
            }

            return $question->load('options');
        });

        return [$classRoom, $tryout, $questions];
    }

    public function test_submit_persists_weighted_grading_snapshot_for_correct_incorrect_and_unanswered_answers(): void
    {
        [$classRoom, $tryout, $questions] = $this->fixture();
        $student = $this->student($classRoom);

        $this->actingAs($student->user)->post(route('student.tryouts.start', $tryout));
        $attempt = TryoutAttempt::firstOrFail();

        $this->actingAs($student->user)->putJson(route('student.tryouts.attempts.answers.update', [$tryout, $attempt]), [
            'question_id' => $questions[0]->id,
            'option_id' => $questions[0]->options->firstWhere('is_correct', true)->id,
        ])->assertOk();
        $this->actingAs($student->user)->putJson(route('student.tryouts.attempts.answers.update', [$tryout, $attempt]), [
            'question_id' => $questions[1]->id,
            'option_id' => $questions[1]->options->firstWhere('is_correct', false)->id,
        ])->assertOk();

        $this->actingAs($student->user)
            ->post(route('student.tryouts.attempts.submit', [$tryout, $attempt]))
            ->assertRedirect(route('student.tryouts.attempts.result', [$tryout, $attempt]));

        $attempt->refresh();
        $this->assertSame('submitted', $attempt->status);
        $this->assertSame(3, $attempt->total_questions);
        $this->assertSame(2, $attempt->answered_questions);
        $this->assertSame(1, $attempt->correct_answers);
        $this->assertSame(1, $attempt->incorrect_answers);
        $this->assertSame(1, $attempt->unanswered_questions);
        $this->assertSame(45, $attempt->total_points);
        $this->assertSame(10, $attempt->earned_points);
        $this->assertSame('22.22', $attempt->final_score);
        $this->assertNotNull($attempt->graded_at);
    }

    public function test_grading_is_idempotent_and_result_review_are_only_available_to_attempt_owner(): void
    {
        [$classRoom, $tryout, $questions] = $this->fixture();
        $owner = $this->student($classRoom);
        $otherStudent = $this->student($classRoom);

        $this->actingAs($owner->user)->post(route('student.tryouts.start', $tryout));
        $attempt = TryoutAttempt::firstOrFail();
        $this->actingAs($owner->user)->post(route('student.tryouts.attempts.submit', [$tryout, $attempt]));
        $gradedAt = $attempt->fresh()->graded_at;

        $this->actingAs($owner->user)->post(route('student.tryouts.attempts.submit', [$tryout, $attempt]));
        $this->assertTrue($attempt->fresh()->graded_at->equalTo($gradedAt));

        $this->actingAs($owner->user)
            ->get(route('student.tryouts.attempts.result', [$tryout, $attempt]))
            ->assertOk()
            ->assertSee('Nilai Akhir');
        $this->actingAs($owner->user)
            ->get(route('student.tryouts.attempts.review', [$tryout, $attempt]))
            ->assertOk()
            ->assertSee('Jawaban benar');
        $this->actingAs($otherStudent->user)
            ->get(route('student.tryouts.attempts.result', [$tryout, $attempt]))
            ->assertNotFound();
        $this->actingAs($otherStudent->user)
            ->get(route('student.tryouts.attempts.review', [$tryout, $attempt]))
            ->assertNotFound();
    }

    public function test_zero_total_points_is_graded_safely_without_division_by_zero(): void
    {
        [$classRoom, $tryout, $questions] = $this->fixture();
        $questions->each(fn ($question) => $question->update(['points' => 0]));
        $student = $this->student($classRoom);

        $this->actingAs($student->user)->post(route('student.tryouts.start', $tryout));
        $attempt = TryoutAttempt::firstOrFail();
        $this->actingAs($student->user)->post(route('student.tryouts.attempts.submit', [$tryout, $attempt]));

        $attempt->refresh();
        $this->assertSame(0, $attempt->total_points);
        $this->assertSame(0, $attempt->earned_points);
        $this->assertSame('0.00', $attempt->final_score);
    }
}
