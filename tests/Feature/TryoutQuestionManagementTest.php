<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\Tryout;
use App\Models\TryoutQuestion;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TryoutQuestionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => ucfirst($role) . ' ' . uniqid(),
            'email' => $role . '-' . uniqid() . '@example.test',
            'password' => Hash::make('password'),
            'role' => $role,
            'status' => 'active',
        ]);
    }

    private function subject(): Subject
    {
        return Subject::create([
            'code' => 'SUB-' . uniqid(),
            'name' => 'Mata Pelajaran ' . uniqid(),
            'education_level' => 'SD',
            'status' => 'active',
        ]);
    }

    private function classRoom(): ClassRoom
    {
        return ClassRoom::create([
            'code' => 'KLS-' . uniqid(),
            'name' => 'Kelas ' . uniqid(),
            'education_level' => 'SD',
            'grade_level' => '1',
            'capacity' => 30,
            'status' => 'active',
        ]);
    }

    private function tutor(): Tutor
    {
        return $this->user('tutor')->tutor()->create();
    }

    private function tryout(?Tutor $tutor = null, string $status = 'draft'): Tryout
    {
        $tryout = Tryout::create([
            'title' => 'Try Out ' . uniqid(),
            'subject_id' => $this->subject()->id,
            'tutor_id' => $tutor?->id,
            'start_at' => '2026-11-02 09:00:00',
            'end_at' => '2026-11-02 11:00:00',
            'duration_minutes' => 90,
            'status' => $status,
        ]);
        $tryout->classRooms()->attach($this->classRoom());

        return $tryout;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'question_text' => 'Hasil dari 2 + 2 adalah?',
            'points' => 2,
            'order_number' => 1,
            'correct_option' => 'B',
            'options' => [
                ['option_label' => 'A', 'option_text' => '3', 'is_correct' => '0', 'order_number' => 1],
                ['option_label' => 'B', 'option_text' => '4', 'is_correct' => '1', 'order_number' => 2],
                ['option_label' => 'C', 'option_text' => '5', 'is_correct' => '0', 'order_number' => 3],
                ['option_label' => 'D', 'option_text' => '6', 'is_correct' => '0', 'order_number' => 4],
            ],
        ], $overrides);
    }

    public function test_admin_can_view_create_update_and_delete_questions(): void
    {
        $admin = $this->user('admin');
        $tryout = $this->tryout();

        $this->actingAs($admin)
            ->get(route('admin.tryouts.questions.index', $tryout))
            ->assertOk()
            ->assertSee('Bank Soal');

        $this->actingAs($admin)
            ->post(route('admin.tryouts.questions.store', $tryout), $this->payload())
            ->assertRedirect(route('admin.tryouts.questions.index', $tryout));

        $question = TryoutQuestion::firstOrFail();
        $this->assertDatabaseHas('tryout_questions', [
            'id' => $question->id,
            'tryout_id' => $tryout->id,
            'points' => 2,
            'order_number' => 1,
        ]);
        $this->assertSame(4, $question->options()->count());
        $this->assertSame(1, $question->options()->where('is_correct', true)->count());

        $this->actingAs($admin)
            ->put(route('admin.tryouts.questions.update', [$tryout, $question]), $this->payload([
                'question_text' => 'Pertanyaan diperbarui',
                'points' => 3,
                'order_number' => 2,
                'correct_option' => 'C',
                'options' => [
                    ['option_label' => 'A', 'option_text' => 'A', 'is_correct' => '0', 'order_number' => 1],
                    ['option_label' => 'B', 'option_text' => 'B', 'is_correct' => '0', 'order_number' => 2],
                    ['option_label' => 'C', 'option_text' => 'C', 'is_correct' => '1', 'order_number' => 3],
                    ['option_label' => 'D', 'option_text' => 'D', 'is_correct' => '0', 'order_number' => 4],
                ],
            ]))
            ->assertRedirect(route('admin.tryouts.questions.index', $tryout));

        $this->assertDatabaseHas('tryout_questions', ['id' => $question->id, 'points' => 3, 'order_number' => 2]);
        $this->assertDatabaseHas('tryout_question_options', ['tryout_question_id' => $question->id, 'option_label' => 'C', 'is_correct' => 1]);

        $this->actingAs($admin)
            ->delete(route('admin.tryouts.questions.destroy', [$tryout, $question]))
            ->assertRedirect(route('admin.tryouts.questions.index', $tryout));

        $this->assertDatabaseMissing('tryout_questions', ['id' => $question->id]);
    }

    public function test_admin_question_validation_rejects_invalid_mcq_data(): void
    {
        $admin = $this->user('admin');
        $tryout = $this->tryout();

        $this->actingAs($admin)
            ->post(route('admin.tryouts.questions.store', $tryout), $this->payload(['options' => []]))
            ->assertSessionHasErrors('options');

        $withoutCorrect = $this->payload();
        foreach ($withoutCorrect['options'] as &$option) {
            $option['is_correct'] = '0';
        }
        unset($option);
        $this->actingAs($admin)
            ->post(route('admin.tryouts.questions.store', $tryout), $withoutCorrect)
            ->assertSessionHasErrors('options');

        $multipleCorrect = $this->payload();
        $multipleCorrect['options'][0]['is_correct'] = '1';
        $this->actingAs($admin)
            ->post(route('admin.tryouts.questions.store', $tryout), $multipleCorrect)
            ->assertSessionHasErrors('options');

        $this->actingAs($admin)
            ->post(route('admin.tryouts.questions.store', $tryout), $this->payload(['points' => 0]))
            ->assertSessionHasErrors('points');
    }

    public function test_question_order_must_be_unique_within_tryout(): void
    {
        $admin = $this->user('admin');
        $tryout = $this->tryout();

        $this->actingAs($admin)->post(route('admin.tryouts.questions.store', $tryout), $this->payload());
        $this->actingAs($admin)
            ->post(route('admin.tryouts.questions.store', $tryout), $this->payload(['question_text' => 'Soal kedua']))
            ->assertSessionHasErrors('order_number');

        $this->actingAs($admin)
            ->post(route('admin.tryouts.questions.store', $tryout), $this->payload(['question_text' => 'Soal kedua', 'order_number' => 2]))
            ->assertRedirect(route('admin.tryouts.questions.index', $tryout));

        $this->assertSame(2, $tryout->questions()->count());
    }

    public function test_tutor_can_manage_own_questions_but_cannot_access_another_tutors_tryout(): void
    {
        $owner = $this->tutor();
        $tryout = $this->tryout($owner);

        $this->actingAs($owner->user)
            ->post(route('tutor.tryouts.questions.store', $tryout), $this->payload())
            ->assertRedirect(route('tutor.tryouts.questions.index', $tryout));

        $question = TryoutQuestion::firstOrFail();
        $this->actingAs($owner->user)
            ->get(route('tutor.tryouts.questions.edit', [$tryout, $question]))
            ->assertOk();

        $otherTutor = $this->tutor();
        $this->actingAs($otherTutor->user)
            ->get(route('tutor.tryouts.questions.index', $tryout))
            ->assertNotFound();
        $this->actingAs($otherTutor->user)
            ->put(route('tutor.tryouts.questions.update', [$tryout, $question]), $this->payload())
            ->assertNotFound();
    }

    public function test_questions_cannot_be_managed_when_tryout_is_not_draft(): void
    {
        $admin = $this->user('admin');
        $tryout = $this->tryout(null, 'published');

        $this->actingAs($admin)
            ->post(route('admin.tryouts.questions.store', $tryout), $this->payload())
            ->assertSessionHasErrors('tryout');
    }

    public function test_users_without_required_role_cannot_access_question_routes(): void
    {
        $tryout = $this->tryout();

        $this->actingAs($this->user('student'))
            ->get(route('admin.tryouts.questions.index', $tryout))
            ->assertRedirect('/login');
        $this->actingAs($this->user('admin'))
            ->get(route('tutor.tryouts.questions.index', $tryout))
            ->assertRedirect('/login');
    }
}
