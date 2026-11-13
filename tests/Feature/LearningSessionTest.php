<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\LearningSchedule;
use App\Models\LearningSession;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LearningSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function createUser(string $role, string $status = 'active'): User
    {
        return User::create([
            'name' => ucfirst($role) . ' Test',
            'email' => $role . '-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => $role,
            'status' => $status,
        ]);
    }

    private function createClassRoom(string $status = 'active'): ClassRoom
    {
        return ClassRoom::create([
            'code' => 'KLS-' . uniqid(),
            'name' => 'Kelas Test',
            'education_level' => 'SD',
            'grade_level' => '1',
            'capacity' => 30,
            'status' => $status,
        ]);
    }

    private function createSubject(string $status = 'active'): Subject
    {
        return Subject::create([
            'code' => 'MPL-' . uniqid(),
            'name' => 'Mata Pelajaran Test',
            'education_level' => 'SD',
            'status' => $status,
        ]);
    }

    private function createTutor(string $status = 'active', array $subjectIds = []): Tutor
    {
        $user = $this->createUser('tutor', $status);
        $tutor = $user->tutor()->create();
        $tutor->subjects()->sync($subjectIds);

        return $tutor;
    }

    private function createSchedule(ClassRoom $classRoom, Tutor $tutor, Subject $subject, array $overrides = []): LearningSchedule
    {
        return LearningSchedule::create(array_merge([
            'class_room_id' => $classRoom->id,
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'day_of_week' => 'monday',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'effective_from' => '2026-01-01',
            'effective_until' => '2026-12-31',
            'status' => 'active',
        ], $overrides));
    }

    private function sessionData(LearningSchedule $schedule, array $overrides = []): array
    {
        return array_merge([
            'learning_schedule_id' => $schedule->id,
            'session_date' => '2026-10-05', // Monday
            'start_time' => '09:00',
            'end_time' => '11:00',
            'status' => 'scheduled',
            'topic' => 'Materi test',
            'notes' => 'Catatan test',
        ], $overrides);
    }

    private function setupActiveSchedule(): array
    {
        $classRoom = $this->createClassRoom();
        $subject = $this->createSubject();
        $tutor = $this->createTutor('active', [$subject->id]);
        $schedule = $this->createSchedule($classRoom, $tutor, $subject);

        return [$classRoom, $subject, $tutor, $schedule];
    }

    // =========================================================================
    // CRUD
    // =========================================================================

    public function test_admin_can_view_session_index(): void
    {
        $this->actingAs($this->createUser('admin'))
            ->get(route('admin.learning-sessions.index'))
            ->assertOk()
            ->assertSee('Sesi Pembelajaran');
    }

    public function test_admin_can_create_a_valid_session(): void
    {
        $admin = $this->createUser('admin');
        [, , , $schedule] = $this->setupActiveSchedule();

        $this->actingAs($admin)
            ->post(route('admin.learning-sessions.store'), $this->sessionData($schedule))
            ->assertRedirect(route('admin.learning-sessions.index'));

        $this->assertDatabaseHas('learning_sessions', [
            'learning_schedule_id' => $schedule->id,
            'status' => 'scheduled',
        ]);
    }

    public function test_admin_can_view_session_detail(): void
    {
        $admin = $this->createUser('admin');
        [, , , $schedule] = $this->setupActiveSchedule();
        $session = LearningSession::create($this->sessionData($schedule));

        $this->actingAs($admin)
            ->get(route('admin.learning-sessions.show', $session))
            ->assertOk()
            ->assertSee('Detail Sesi Pembelajaran');
    }

    public function test_admin_can_edit_session(): void
    {
        $admin = $this->createUser('admin');
        [, , , $schedule] = $this->setupActiveSchedule();
        $session = LearningSession::create($this->sessionData($schedule));

        $this->actingAs($admin)
            ->put(route('admin.learning-sessions.update', $session), $this->sessionData($schedule, [
                'topic' => 'Materi diperbarui',
                'notes' => 'Catatan diperbarui',
            ]))
            ->assertRedirect(route('admin.learning-sessions.show', $session));

        $this->assertDatabaseHas('learning_sessions', [
            'id' => $session->id,
            'topic' => 'Materi diperbarui',
            'notes' => 'Catatan diperbarui',
        ]);
    }

    // =========================================================================
    // Status
    // =========================================================================

    public function test_admin_can_mark_session_as_completed(): void
    {
        $admin = $this->createUser('admin');
        [, , , $schedule] = $this->setupActiveSchedule();
        $session = LearningSession::create($this->sessionData($schedule));

        $this->actingAs($admin)
            ->patch(route('admin.learning-sessions.update-status', $session), ['status' => 'completed'])
            ->assertRedirect(route('admin.learning-sessions.show', $session));

        $this->assertDatabaseHas('learning_sessions', [
            'id' => $session->id,
            'status' => 'completed',
        ]);
    }

    public function test_admin_can_cancel_session(): void
    {
        $admin = $this->createUser('admin');
        [, , , $schedule] = $this->setupActiveSchedule();
        $session = LearningSession::create($this->sessionData($schedule));

        $this->actingAs($admin)
            ->patch(route('admin.learning-sessions.update-status', $session), ['status' => 'cancelled'])
            ->assertRedirect(route('admin.learning-sessions.show', $session));

        $this->assertDatabaseHas('learning_sessions', [
            'id' => $session->id,
            'status' => 'cancelled',
        ]);
    }

    // =========================================================================
    // Validasi jadwal
    // =========================================================================

    public function test_inactive_schedule_is_rejected(): void
    {
        $admin = $this->createUser('admin');
        [, , , $schedule] = $this->setupActiveSchedule();
        $schedule->update(['status' => 'inactive']);

        $this->actingAs($admin)
            ->post(route('admin.learning-sessions.store'), $this->sessionData($schedule))
            ->assertSessionHasErrors('learning_schedule_id');
    }

    // =========================================================================
    // Validasi tanggal
    // =========================================================================

    public function test_wrong_day_of_week_is_rejected(): void
    {
        $admin = $this->createUser('admin');
        [, , , $schedule] = $this->setupActiveSchedule();

        // 2026-10-06 is Tuesday, schedule is Monday
        $this->actingAs($admin)
            ->post(route('admin.learning-sessions.store'), $this->sessionData($schedule, [
                'session_date' => '2026-10-06',
            ]))
            ->assertSessionHasErrors('session_date');
    }

    public function test_date_outside_effective_period_is_rejected(): void
    {
        $admin = $this->createUser('admin');
        [, , , $schedule] = $this->setupActiveSchedule();

        // 2027-10-04 is Monday but outside effective_until 2026-12-31
        $this->actingAs($admin)
            ->post(route('admin.learning-sessions.store'), $this->sessionData($schedule, [
                'session_date' => '2027-10-04',
            ]))
            ->assertSessionHasErrors('session_date');
    }

    // =========================================================================
    // Validasi waktu
    // =========================================================================

    public function test_time_outside_schedule_range_is_rejected(): void
    {
        $admin = $this->createUser('admin');
        [, , , $schedule] = $this->setupActiveSchedule();

        $this->actingAs($admin)
            ->post(route('admin.learning-sessions.store'), $this->sessionData($schedule, [
                'start_time' => '08:00',
                'end_time' => '10:00',
            ]))
            ->assertSessionHasErrors('start_time');
    }

    public function test_end_time_before_start_time_is_rejected(): void
    {
        $admin = $this->createUser('admin');
        [, , , $schedule] = $this->setupActiveSchedule();

        $this->actingAs($admin)
            ->post(route('admin.learning-sessions.store'), $this->sessionData($schedule, [
                'start_time' => '10:30',
                'end_time' => '09:30',
            ]))
            ->assertSessionHasErrors('end_time');
    }

    // =========================================================================
    // Duplikasi dan bentrok
    // =========================================================================

    public function test_duplicate_session_for_same_schedule_and_date_is_rejected(): void
    {
        $admin = $this->createUser('admin');
        [, , , $schedule] = $this->setupActiveSchedule();
        LearningSession::create($this->sessionData($schedule));

        $this->actingAs($admin)
            ->post(route('admin.learning-sessions.store'), $this->sessionData($schedule))
            ->assertSessionHasErrors('session_date');
    }

    public function test_overlapping_tutor_session_is_rejected(): void
    {
        $admin = $this->createUser('admin');
        $classRoom1 = $this->createClassRoom();
        $classRoom2 = $this->createClassRoom();
        $subject = $this->createSubject();
        $tutor = $this->createTutor('active', [$subject->id]);

        $schedule1 = $this->createSchedule($classRoom1, $tutor, $subject);
        $schedule2 = $this->createSchedule($classRoom2, $tutor, $subject);

        LearningSession::create($this->sessionData($schedule1));

        $this->actingAs($admin)
            ->post(route('admin.learning-sessions.store'), $this->sessionData($schedule2, [
                'start_time' => '10:00',
                'end_time' => '11:00',
            ]))
            ->assertSessionHasErrors('start_time');
    }

    public function test_overlapping_classroom_session_is_rejected(): void
    {
        $admin = $this->createUser('admin');
        $classRoom = $this->createClassRoom();
        $subject1 = $this->createSubject();
        $subject2 = $this->createSubject();
        $tutor1 = $this->createTutor('active', [$subject1->id]);
        $tutor2 = $this->createTutor('active', [$subject2->id]);

        $schedule1 = $this->createSchedule($classRoom, $tutor1, $subject1);
        $schedule2 = $this->createSchedule($classRoom, $tutor2, $subject2);

        LearningSession::create($this->sessionData($schedule1));

        $this->actingAs($admin)
            ->post(route('admin.learning-sessions.store'), $this->sessionData($schedule2, [
                'start_time' => '10:00',
                'end_time' => '11:00',
            ]))
            ->assertSessionHasErrors('start_time');
    }

    public function test_non_overlapping_session_can_be_saved(): void
    {
        $admin = $this->createUser('admin');
        [, , , $schedule] = $this->setupActiveSchedule();
        LearningSession::create($this->sessionData($schedule));

        // Next Monday
        $this->actingAs($admin)
            ->post(route('admin.learning-sessions.store'), $this->sessionData($schedule, [
                'session_date' => '2026-10-12',
            ]))
            ->assertRedirect(route('admin.learning-sessions.index'));

        $this->assertSame(2, LearningSession::count());
    }

    // =========================================================================
    // Perlindungan penghapusan jadwal
    // =========================================================================

    public function test_schedule_with_sessions_cannot_be_deleted(): void
    {
        $admin = $this->createUser('admin');
        [, , , $schedule] = $this->setupActiveSchedule();
        LearningSession::create($this->sessionData($schedule));

        $this->actingAs($admin)
            ->delete(route('admin.learning-schedules.destroy', $schedule))
            ->assertRedirect(route('admin.learning-schedules.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('learning_schedules', ['id' => $schedule->id]);
    }

    public function test_schedule_without_sessions_can_still_be_deleted(): void
    {
        $admin = $this->createUser('admin');
        [, , , $schedule] = $this->setupActiveSchedule();

        $this->actingAs($admin)
            ->delete(route('admin.learning-schedules.destroy', $schedule))
            ->assertRedirect(route('admin.learning-schedules.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('learning_schedules', ['id' => $schedule->id]);
    }

    // =========================================================================
    // Akses non-admin
    // =========================================================================

    public function test_non_admin_cannot_access_session_crud(): void
    {
        $this->actingAs($this->createUser('student'))
            ->get(route('admin.learning-sessions.index'))
            ->assertRedirect('/login')
            ->assertSessionHas('error', 'Anda tidak memiliki akses ke halaman ini.');
    }
}