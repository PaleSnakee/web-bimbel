<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\LearningSchedule;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LearningScheduleTest extends TestCase
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

    private function scheduleData(ClassRoom $classRoom, Tutor $tutor, Subject $subject, array $overrides = []): array
    {
        return array_merge([
            'class_room_id' => $classRoom->id,
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'day_of_week' => 'monday',
            'start_time' => '09:00',
            'end_time' => '10:30',
            'effective_from' => '2026-10-01',
            'effective_until' => null,
            'status' => 'active',
            'notes' => 'Jadwal pengujian',
        ], $overrides);
    }

    public function test_admin_can_view_schedule_index(): void
    {
        $this->actingAs($this->createUser('admin'))
            ->get(route('admin.learning-schedules.index'))
            ->assertOk()
            ->assertSee('Jadwal Pembelajaran');
    }

    public function test_admin_can_create_and_update_a_valid_schedule(): void
    {
        $admin = $this->createUser('admin');
        $classRoom = $this->createClassRoom();
        $subject = $this->createSubject();
        $tutor = $this->createTutor('active', [$subject->id]);

        $this->actingAs($admin)
            ->post(route('admin.learning-schedules.store'), $this->scheduleData($classRoom, $tutor, $subject))
            ->assertRedirect(route('admin.learning-schedules.index'));

        $schedule = LearningSchedule::firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.learning-schedules.update', $schedule), $this->scheduleData($classRoom, $tutor, $subject, [
                'start_time' => '11:00',
                'end_time' => '12:30',
                'notes' => 'Jadwal diperbarui',
            ]))
            ->assertRedirect(route('admin.learning-schedules.show', $schedule));

        $this->assertDatabaseHas('learning_schedules', [
            'id' => $schedule->id,
            'start_time' => '11:00',
            'end_time' => '12:30',
            'notes' => 'Jadwal diperbarui',
        ]);
    }

    public function test_admin_can_deactivate_schedule(): void
    {
        $admin = $this->createUser('admin');
        $classRoom = $this->createClassRoom();
        $subject = $this->createSubject();
        $tutor = $this->createTutor('active', [$subject->id]);
        $schedule = LearningSchedule::create($this->scheduleData($classRoom, $tutor, $subject));

        $this->actingAs($admin)
            ->post(route('admin.learning-schedules.deactivate', $schedule))
            ->assertRedirect(route('admin.learning-schedules.index'));

        $this->assertDatabaseHas('learning_schedules', [
            'id' => $schedule->id,
            'status' => 'inactive',
        ]);
    }

    public function test_inactive_master_data_and_unassigned_subject_are_rejected(): void
    {
        $admin = $this->createUser('admin');
        $activeClassRoom = $this->createClassRoom();
        $activeSubject = $this->createSubject();
        $activeTutor = $this->createTutor('active', [$activeSubject->id]);

        $this->actingAs($admin)
            ->post(route('admin.learning-schedules.store'), $this->scheduleData(
                $this->createClassRoom('inactive'),
                $activeTutor,
                $activeSubject,
            ))
            ->assertSessionHasErrors('class_room_id');

        $this->actingAs($admin)
            ->post(route('admin.learning-schedules.store'), $this->scheduleData(
                $activeClassRoom,
                $this->createTutor('inactive', [$activeSubject->id]),
                $activeSubject,
            ))
            ->assertSessionHasErrors('tutor_id');

        $inactiveSubject = $this->createSubject('inactive');
        $this->actingAs($admin)
            ->post(route('admin.learning-schedules.store'), $this->scheduleData(
                $activeClassRoom,
                $this->createTutor('active', [$inactiveSubject->id]),
                $inactiveSubject,
            ))
            ->assertSessionHasErrors('subject_id');

        $this->actingAs($admin)
            ->post(route('admin.learning-schedules.store'), $this->scheduleData(
                $activeClassRoom,
                $this->createTutor(),
                $activeSubject,
            ))
            ->assertSessionHasErrors('subject_id');
    }

    public function test_invalid_time_range_is_rejected(): void
    {
        $admin = $this->createUser('admin');
        $classRoom = $this->createClassRoom();
        $subject = $this->createSubject();
        $tutor = $this->createTutor('active', [$subject->id]);

        $this->actingAs($admin)
            ->post(route('admin.learning-schedules.store'), $this->scheduleData($classRoom, $tutor, $subject, [
                'start_time' => '10:30',
                'end_time' => '09:00',
            ]))
            ->assertSessionHasErrors('end_time');
    }

    public function test_overlapping_tutor_and_classroom_schedules_are_rejected(): void
    {
        $admin = $this->createUser('admin');
        $firstClassRoom = $this->createClassRoom();
        $secondClassRoom = $this->createClassRoom();
        $firstSubject = $this->createSubject();
        $secondSubject = $this->createSubject();
        $firstTutor = $this->createTutor('active', [$firstSubject->id]);
        $secondTutor = $this->createTutor('active', [$secondSubject->id]);

        LearningSchedule::create($this->scheduleData($firstClassRoom, $firstTutor, $firstSubject));

        $this->actingAs($admin)
            ->post(route('admin.learning-schedules.store'), $this->scheduleData($secondClassRoom, $firstTutor, $firstSubject, [
                'start_time' => '10:00',
                'end_time' => '11:00',
            ]))
            ->assertSessionHasErrors('tutor_id');

        $this->actingAs($admin)
            ->post(route('admin.learning-schedules.store'), $this->scheduleData($firstClassRoom, $secondTutor, $secondSubject, [
                'start_time' => '10:00',
                'end_time' => '11:00',
            ]))
            ->assertSessionHasErrors('class_room_id');
    }

    public function test_non_overlapping_schedule_can_be_saved(): void
    {
        $admin = $this->createUser('admin');
        $classRoom = $this->createClassRoom();
        $subject = $this->createSubject();
        $tutor = $this->createTutor('active', [$subject->id]);

        LearningSchedule::create($this->scheduleData($classRoom, $tutor, $subject));

        $this->actingAs($admin)
            ->post(route('admin.learning-schedules.store'), $this->scheduleData($classRoom, $tutor, $subject, [
                'start_time' => '10:30',
                'end_time' => '11:30',
            ]))
            ->assertRedirect(route('admin.learning-schedules.index'));

        $this->assertSame(2, LearningSchedule::count());
    }

    public function test_non_admin_cannot_access_schedule_crud(): void
    {
        $this->actingAs($this->createUser('student'))
            ->get(route('admin.learning-schedules.index'))
            ->assertRedirect('/login')
            ->assertSessionHas('error', 'Anda tidak memiliki akses ke halaman ini.');
    }
}