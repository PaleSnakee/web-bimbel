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

class StudentTryoutAttemptTest extends TestCase
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

    private function classRoom(): ClassRoom
    {
        return ClassRoom::create([
            'code' => 'KLS-' . uniqid(),
            'name' => 'Kelas ' . uniqid(),
            'education_level' => 'SD',
            'grade_level' => '6',
            'capacity' => 30,
            'status' => 'active',
        ]);
    }

    private function student(?ClassRoom $classRoom = null, string $status = 'active'): Student
    {
        $user = User::create([
            'name' => 'Siswa ' . uniqid(),
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

    private function tryout(ClassRoom $classRoom, array $overrides = []): Tryout
    {
        $subject = Subject::create([
            'code' => 'SUB-' . uniqid(),
            'name' => 'Matematika',
            'education_level' => 'SD',
            'status' => 'active',
        ]);

        $tryout = Tryout::create(array_merge([
            'title' => 'Try Out ' . uniqid(),
            'subject_id' => $subject->id,
            'start_at' => now()->subMinutes(15),
            'end_at' => now()->addHours(2),
            'duration_minutes' => 60,
            'status' => 'published',
        ], $overrides));

        $tryout->classRooms()->attach($classRoom);

        return $tryout;
    }

    private function question(Tryout $tryout, int $order = 1): TryoutQuestion
    {
        $question = $tryout->questions()->create([
            'question_text' => 'Pertanyaan ' . $order,
            'points' => 1,
            'order_number' => $order,
        ]);

        foreach (['A', 'B', 'C', 'D'] as $index => $label) {
            $question->options()->create([
                'option_label' => $label,
                'option_text' => 'Pilihan ' . $label,
                'is_correct' => $label === 'B',
                'order_number' => $index + 1,
            ]);
        }

        return $question;
    }

    public function test_student_only_sees_tryouts_for_own_class(): void
    {
        $classRoom = $this->classRoom();
        $student = $this->student($classRoom);
        $visible = $this->tryout($classRoom, ['title' => 'Untuk Kelas Saya']);
        $hidden = $this->tryout($this->classRoom(), ['title' => 'Untuk Kelas Lain']);

        $this->actingAs($student->user)
            ->get(route('student.tryouts.index'))
            ->assertOk()
            ->assertSee($visible->title)
            ->assertDontSee($hidden->title);

        $this->actingAs($student->user)
            ->get(route('student.tryouts.show', $hidden))
            ->assertNotFound();
    }

    public function test_student_cannot_start_unavailable_tryout_or_with_inactive_account(): void
    {
        $classRoom = $this->classRoom();
        $student = $this->student($classRoom);
        $draft = $this->tryout($classRoom, ['status' => 'draft']);
        $future = $this->tryout($classRoom, ['start_at' => now()->addMinute(), 'end_at' => now()->addHours(2)]);
        $ended = $this->tryout($classRoom, ['start_at' => now()->subHours(2), 'end_at' => now()->subMinute()]);

        $this->actingAs($student->user)->post(route('student.tryouts.start', $draft))->assertSessionHasErrors('tryout');
        $this->actingAs($student->user)->post(route('student.tryouts.start', $future))->assertSessionHasErrors('tryout');
        $this->actingAs($student->user)->post(route('student.tryouts.start', $ended))->assertSessionHasErrors('tryout');

        $inactive = $this->student($classRoom, 'inactive');
        $this->actingAs($inactive->user)->get(route('student.tryouts.index'))->assertRedirect('/login');
    }

    public function test_start_creates_one_attempt_and_initial_answers_then_refresh_continues_it(): void
    {
        $student = $this->student($classRoom = $this->classRoom());
        $tryout = $this->tryout($classRoom);
        $this->question($tryout, 1);
        $this->question($tryout, 2);

        $this->actingAs($student->user)
            ->post(route('student.tryouts.start', $tryout))
            ->assertRedirect();

        $attempt = TryoutAttempt::firstOrFail();
        $this->assertSame($student->id, $attempt->student_id);
        $this->assertSame('in_progress', $attempt->status);
        $this->assertSame(2, $attempt->answers()->count());
        $this->assertTrue($attempt->expires_at->equalTo(now()->addMinutes(60)));

        $this->actingAs($student->user)
            ->post(route('student.tryouts.start', $tryout))
            ->assertRedirect(route('student.tryouts.attempts.show', [$tryout, $attempt]));

        $this->assertSame(1, TryoutAttempt::count());
    }

    public function test_student_can_save_and_replace_own_valid_answer_without_receiving_answer_key(): void
    {
        $student = $this->student($classRoom = $this->classRoom());
        $tryout = $this->tryout($classRoom);
        $question = $this->question($tryout);
        $this->actingAs($student->user)->post(route('student.tryouts.start', $tryout));

        $attempt = TryoutAttempt::firstOrFail();
        $firstOption = $question->options()->orderBy('id')->firstOrFail();
        $secondOption = $question->options()->orderByDesc('id')->firstOrFail();

        $this->actingAs($student->user)
            ->putJson(route('student.tryouts.attempts.answers.update', [$tryout, $attempt]), [
                'question_id' => $question->id,
                'option_id' => $firstOption->id,
            ])
            ->assertOk()
            ->assertJsonPath('option_id', $firstOption->id);

        $this->actingAs($student->user)
            ->putJson(route('student.tryouts.attempts.answers.update', [$tryout, $attempt]), [
                'question_id' => $question->id,
                'option_id' => $secondOption->id,
            ])
            ->assertOk()
            ->assertJsonPath('option_id', $secondOption->id);

        $this->assertDatabaseHas('tryout_attempt_answers', [
            'tryout_attempt_id' => $attempt->id,
            'tryout_question_id' => $question->id,
            'tryout_question_option_id' => $secondOption->id,
        ]);

        $this->actingAs($student->user)
            ->get(route('student.tryouts.attempts.show', [$tryout, $attempt]))
            ->assertOk()
            ->assertDontSee('is_correct');
    }

    public function test_cross_attempt_question_and_option_are_rejected(): void
    {
        $student = $this->student($classRoom = $this->classRoom());
        $tryout = $this->tryout($classRoom);
        $question = $this->question($tryout);
        $otherTryout = $this->tryout($classRoom);
        $otherQuestion = $this->question($otherTryout);

        $this->actingAs($student->user)->post(route('student.tryouts.start', $tryout));
        $attempt = TryoutAttempt::firstOrFail();

        $this->actingAs($student->user)
            ->putJson(route('student.tryouts.attempts.answers.update', [$tryout, $attempt]), [
                'question_id' => $question->id,
                'option_id' => $otherQuestion->options()->firstOrFail()->id,
            ])
            ->assertNotFound();

        $this->actingAs($student->user)
            ->putJson(route('student.tryouts.attempts.answers.update', [$tryout, $attempt]), [
                'question_id' => $otherQuestion->id,
                'option_id' => $otherQuestion->options()->firstOrFail()->id,
            ])
            ->assertNotFound();
    }

    public function test_student_cannot_access_another_students_attempt(): void
    {
        $classRoom = $this->classRoom();
        $owner = $this->student($classRoom);
        $otherStudent = $this->student($classRoom);
        $tryout = $this->tryout($classRoom);
        $question = $this->question($tryout);

        $this->actingAs($owner->user)->post(route('student.tryouts.start', $tryout));
        $attempt = TryoutAttempt::firstOrFail();

        $this->actingAs($otherStudent->user)
            ->get(route('student.tryouts.attempts.show', [$tryout, $attempt]))
            ->assertNotFound();

        $this->actingAs($otherStudent->user)
            ->putJson(route('student.tryouts.attempts.answers.update', [$tryout, $attempt]), [
                'question_id' => $question->id,
                'option_id' => $question->options()->firstOrFail()->id,
            ])
            ->assertNotFound();
    }

    public function test_expired_attempt_rejects_answers_and_submit(): void
    {
        $student = $this->student($classRoom = $this->classRoom());
        $tryout = $this->tryout($classRoom, ['duration_minutes' => 5]);
        $question = $this->question($tryout);
        $this->actingAs($student->user)->post(route('student.tryouts.start', $tryout));
        $attempt = TryoutAttempt::firstOrFail();

        Carbon::setTestNow(now()->addMinutes(5));

        $this->actingAs($student->user)
            ->putJson(route('student.tryouts.attempts.answers.update', [$tryout, $attempt]), [
                'question_id' => $question->id,
                'option_id' => $question->options()->firstOrFail()->id,
            ])
            ->assertStatus(422);

        $this->assertDatabaseHas('tryout_attempts', ['id' => $attempt->id, 'status' => 'expired']);

        $this->actingAs($student->user)
            ->post(route('student.tryouts.attempts.submit', [$tryout, $attempt]))
            ->assertRedirect(route('student.tryouts.attempts.result', [$tryout, $attempt]));

        $this->assertDatabaseHas('tryout_attempts', [
            'id' => $attempt->id,
            'status' => 'expired',
        ]);
        $this->assertNotNull($attempt->fresh()->graded_at);
    }

    public function test_submit_is_idempotent_and_locks_answers(): void
    {
        $student = $this->student($classRoom = $this->classRoom());
        $tryout = $this->tryout($classRoom);
        $question = $this->question($tryout);
        $this->actingAs($student->user)->post(route('student.tryouts.start', $tryout));
        $attempt = TryoutAttempt::firstOrFail();

        $this->actingAs($student->user)
            ->post(route('student.tryouts.attempts.submit', [$tryout, $attempt]))
            ->assertRedirect(route('student.tryouts.attempts.result', [$tryout, $attempt]));

        $attempt->refresh();
        $submittedAt = $attempt->submitted_at;
        $this->assertSame('submitted', $attempt->status);
        $this->assertNotNull($submittedAt);

        $this->actingAs($student->user)
            ->post(route('student.tryouts.attempts.submit', [$tryout, $attempt]))
            ->assertRedirect(route('student.tryouts.attempts.result', [$tryout, $attempt]));

        $attempt->refresh();
        $this->assertTrue($attempt->submitted_at->equalTo($submittedAt));

        $this->actingAs($student->user)
            ->putJson(route('student.tryouts.attempts.answers.update', [$tryout, $attempt]), [
                'question_id' => $question->id,
                'option_id' => $question->options()->firstOrFail()->id,
            ])
            ->assertStatus(422);
    }

    public function test_non_students_and_guests_cannot_use_student_attempt_routes(): void
    {
        $classRoom = $this->classRoom();
        $tryout = $this->tryout($classRoom);
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-' . uniqid() . '@example.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->get(route('student.tryouts.index'))->assertRedirect(route('login'));
        $this->actingAs($admin)->get(route('student.tryouts.index'))->assertRedirect('/login');
    }
}