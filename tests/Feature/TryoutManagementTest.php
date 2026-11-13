<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\LearningSchedule;
use App\Models\Subject;
use App\Models\Tryout;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TryoutManagementTest extends TestCase
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

    private function classRoom(string $status = 'active'): ClassRoom
    {
        return ClassRoom::create([
            'code' => 'KLS-' . uniqid(), 'name' => 'Kelas ' . uniqid(),
            'education_level' => 'SD', 'grade_level' => '1', 'capacity' => 30, 'status' => $status,
        ]);
    }

    private function subject(string $status = 'active'): Subject
    {
        return Subject::create([
            'code' => 'SUB-' . uniqid(), 'name' => 'Mapel ' . uniqid(),
            'education_level' => 'SD', 'status' => $status,
        ]);
    }

    private function tutor(): Tutor
    {
        return $this->user('tutor')->tutor()->create();
    }

    private function assign(Tutor $tutor, Subject $subject, ClassRoom $classRoom): void
    {
        $tutor->subjects()->syncWithoutDetaching([$subject->id]);
        LearningSchedule::create([
            'class_room_id' => $classRoom->id, 'tutor_id' => $tutor->id, 'subject_id' => $subject->id,
            'day_of_week' => 'monday', 'start_time' => '09:00', 'end_time' => '10:00',
            'effective_from' => '2026-01-01', 'effective_until' => '2026-12-31', 'status' => 'active',
        ]);
    }

    private function payload(Subject $subject, array $classRooms, array $overrides = []): array
    {
        return array_merge([
            'title' => 'Try Out ' . uniqid(),
            'description' => 'Deskripsi Try Out',
            'subject_id' => $subject->id,
            'class_room_ids' => collect($classRooms)->pluck('id')->all(),
            'start_at' => '2026-11-02 09:00:00',
            'end_at' => '2026-11-02 11:00:00',
            'duration_minutes' => 90,
        ], $overrides);
    }

    private function tryout(Subject $subject, array $classRooms, ?Tutor $tutor = null, array $overrides = []): Tryout
    {
        $tryout = Tryout::create(array_merge([
            'title' => 'Try Out ' . uniqid(), 'subject_id' => $subject->id, 'tutor_id' => $tutor?->id,
            'start_at' => '2026-11-02 09:00:00', 'end_at' => '2026-11-02 11:00:00',
            'duration_minutes' => 90, 'status' => 'draft',
        ], $overrides));
        $tryout->classRooms()->sync(collect($classRooms)->pluck('id')->all());

        return $tryout;
    }

    public function test_admin_can_view_create_show_edit_and_filter_tryouts(): void
    {
        $admin = $this->user('admin');
        $subject = $this->subject();
        $classRoom = $this->classRoom();

        $this->actingAs($admin)->get(route('admin.tryouts.index'))->assertOk()->assertSee('Try Out Online');
        $this->actingAs($admin)
            ->post(route('admin.tryouts.store'), $this->payload($subject, [$classRoom], ['title' => 'TO Matematika']))
            ->assertRedirect();

        $tryout = Tryout::firstOrFail();
        $this->assertSame('draft', $tryout->status);
        $this->assertTrue($tryout->classRooms()->whereKey($classRoom->id)->exists());
        $this->actingAs($admin)->get(route('admin.tryouts.show', $tryout))->assertOk()->assertSee('TO Matematika');
        $this->actingAs($admin)
            ->put(route('admin.tryouts.update', $tryout), $this->payload($subject, [$classRoom], ['title' => 'TO Diperbarui']))
            ->assertRedirect(route('admin.tryouts.show', $tryout));
        $this->assertDatabaseHas('tryouts', ['id' => $tryout->id, 'title' => 'TO Diperbarui']);
        $this->actingAs($admin)->get(route('admin.tryouts.index', ['search' => 'Diperbarui', 'status' => 'draft']))
            ->assertOk()->assertSee('TO Diperbarui');
    }

    public function test_admin_can_change_valid_status_but_invalid_transition_is_rejected(): void
    {
        $admin = $this->user('admin');
        $tryout = $this->tryout($this->subject(), [$this->classRoom()]);

        $this->actingAs($admin)
            ->patch(route('admin.tryouts.update-status', $tryout), ['status' => 'published'])
            ->assertRedirect(route('admin.tryouts.show', $tryout));
        $this->assertDatabaseHas('tryouts', ['id' => $tryout->id, 'status' => 'published']);
        $this->actingAs($admin)
            ->patch(route('admin.tryouts.update-status', $tryout), ['status' => 'draft'])
            ->assertSessionHasErrors('status');
    }

    public function test_tutor_can_manage_only_owned_tryout_for_assigned_subject_and_class(): void
    {
        $tutor = $this->tutor();
        $subject = $this->subject();
        $classRoom = $this->classRoom();
        $this->assign($tutor, $subject, $classRoom);

        $this->actingAs($tutor->user)
            ->post(route('tutor.tryouts.store'), $this->payload($subject, [$classRoom]))
            ->assertRedirect();
        $tryout = Tryout::firstOrFail();
        $this->assertSame($tutor->id, $tryout->tutor_id);
        $this->actingAs($tutor->user)->get(route('tutor.tryouts.index'))->assertOk()->assertSee($tryout->title);

        $otherTutor = $this->tutor();
        $this->actingAs($otherTutor->user)->get(route('tutor.tryouts.show', $tryout))->assertNotFound();
        $this->actingAs($otherTutor->user)->put(route('tutor.tryouts.update', $tryout), $this->payload($subject, [$classRoom]))->assertNotFound();
    }

    public function test_tutor_cannot_use_unassigned_subject_or_class(): void
    {
        $tutor = $this->tutor();
        $subject = $this->subject();
        $allowedClass = $this->classRoom();
        $this->assign($tutor, $subject, $allowedClass);
        $otherSubject = $this->subject();
        $otherClass = $this->classRoom();

        $this->actingAs($tutor->user)
            ->post(route('tutor.tryouts.store'), $this->payload($otherSubject, [$allowedClass]))
            ->assertSessionHasErrors('subject_id');
        $this->actingAs($tutor->user)
            ->post(route('tutor.tryouts.store'), $this->payload($subject, [$otherClass]))
            ->assertSessionHasErrors('class_room_ids');
    }

    public function test_inactive_data_empty_classes_invalid_time_and_duration_are_rejected(): void
    {
        $admin = $this->user('admin');
        $subject = $this->subject();
        $classRoom = $this->classRoom();

        $this->actingAs($admin)->post(route('admin.tryouts.store'), $this->payload($subject, []))->assertSessionHasErrors('class_room_ids');
        $this->actingAs($admin)->post(route('admin.tryouts.store'), $this->payload($this->subject('inactive'), [$classRoom]))->assertSessionHasErrors('subject_id');
        $this->actingAs($admin)->post(route('admin.tryouts.store'), $this->payload($subject, [$this->classRoom('inactive')]))->assertSessionHasErrors('class_room_ids');
        $this->actingAs($admin)->post(route('admin.tryouts.store'), $this->payload($subject, [$classRoom], ['start_at' => '2026-11-02 12:00:00', 'end_at' => '2026-11-02 11:00:00']))->assertSessionHasErrors('end_at');
        $this->actingAs($admin)->post(route('admin.tryouts.store'), $this->payload($subject, [$classRoom], ['duration_minutes' => 121]))->assertSessionHasErrors('duration_minutes');
    }

    public function test_draft_can_be_deleted_and_published_tryout_cannot_be_deleted(): void
    {
        $admin = $this->user('admin');
        $draft = $this->tryout($this->subject(), [$this->classRoom()]);
        $this->actingAs($admin)->delete(route('admin.tryouts.destroy', $draft))->assertRedirect(route('admin.tryouts.index'));
        $this->assertDatabaseMissing('tryouts', ['id' => $draft->id]);

        $published = $this->tryout($this->subject(), [$this->classRoom()], null, ['status' => 'published']);
        $this->actingAs($admin)->delete(route('admin.tryouts.destroy', $published))->assertSessionHas('error');
        $this->assertDatabaseHas('tryouts', ['id' => $published->id]);
    }

    public function test_non_admin_and_non_tutor_cannot_access_tryout_routes(): void
    {
        $this->actingAs($this->user('student'))->get(route('admin.tryouts.index'))->assertRedirect('/login');
        $this->actingAs($this->user('admin'))->get(route('tutor.tryouts.index'))->assertRedirect('/login');
    }
}
