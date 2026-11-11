<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MasterDataDeletionTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // Helpers
    // =========================================================================

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'admin-test@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    private function createClassRoom(array $overrides = []): ClassRoom
    {
        return ClassRoom::create(array_merge([
            'code' => 'KLS-' . uniqid(),
            'name' => 'Kelas Test',
            'education_level' => 'SD',
            'grade_level' => '1',
            'capacity' => 30,
            'status' => 'active',
        ], $overrides));
    }

    private function createStudentInClass(ClassRoom $classRoom): Student
    {
        $user = User::create([
            'name' => 'Murid Test',
            'email' => 'student-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'status' => 'active',
        ]);

        return $user->student()->create([
            'nis' => 'NIS-' . uniqid(),
            'education_level' => $classRoom->education_level,
            'class_room_id' => $classRoom->id,
        ]);
    }

    private function createSubject(array $overrides = []): Subject
    {
        return Subject::create(array_merge([
            'code' => 'MPL-' . uniqid(),
            'name' => 'Mapel Test',
            'education_level' => 'SD',
            'status' => 'active',
        ], $overrides));
    }

    private function createTutorWithSubjects(array $subjectIds): Tutor
    {
        $user = User::create([
            'name' => 'Tutor Test',
            'email' => 'tutor-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $tutor = $user->tutor()->create();
        $tutor->subjects()->sync($subjectIds);

        return $tutor;
    }

    // =========================================================================
    // Kelas — Kebijakan Penghapusan
    // =========================================================================

    public function test_classroom_without_students_can_be_deleted(): void
    {
        $admin = $this->createAdmin();
        $classRoom = $this->createClassRoom();

        $response = $this->actingAs($admin)->delete(route('admin.classes.destroy', $classRoom));

        $response->assertRedirect(route('admin.classes.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('class_rooms', ['id' => $classRoom->id]);
    }

    public function test_classroom_with_students_cannot_be_deleted(): void
    {
        $admin = $this->createAdmin();
        $classRoom = $this->createClassRoom();
        $student = $this->createStudentInClass($classRoom);

        $response = $this->actingAs($admin)->delete(route('admin.classes.destroy', $classRoom));

        $response->assertRedirect(route('admin.classes.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('class_rooms', ['id' => $classRoom->id]);
        $this->assertSame($classRoom->id, $student->fresh()->class_room_id);
    }

    public function test_student_does_not_lose_class_reference_when_deletion_is_blocked(): void
    {
        $admin = $this->createAdmin();
        $classRoom = $this->createClassRoom();
        $student = $this->createStudentInClass($classRoom);

        $this->actingAs($admin)->delete(route('admin.classes.destroy', $classRoom));

        $student->refresh();
        $this->assertNotNull($student->class_room_id);
        $this->assertSame($classRoom->id, $student->class_room_id);
    }

    public function test_inactive_classroom_is_not_available_for_new_student_assignment(): void
    {
        $admin = $this->createAdmin();
        $classRoom = $this->createClassRoom(['status' => 'inactive']);

        $studentUser = User::create([
            'name' => 'Murid Baru',
            'email' => 'murid-baru@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.students.store'), [
            'nis' => 'NIS-NEW-001',
            'name' => 'Murid Baru',
            'email' => 'murid-new-assign@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'status' => 'active',
            'education_level' => $classRoom->education_level,
            'class_room_id' => $classRoom->id,
        ]);

        $response->assertSessionHasErrors('class_room_id');
    }

    // =========================================================================
    // Mata Pelajaran — Kebijakan Penghapusan
    // =========================================================================

    public function test_subject_without_tutors_can_be_deleted(): void
    {
        $admin = $this->createAdmin();
        $subject = $this->createSubject();

        $response = $this->actingAs($admin)->delete(route('admin.subjects.destroy', $subject));

        $response->assertRedirect(route('admin.subjects.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('subjects', ['id' => $subject->id]);
    }

    public function test_subject_with_tutors_cannot_be_deleted(): void
    {
        $admin = $this->createAdmin();
        $subject = $this->createSubject();
        $tutor = $this->createTutorWithSubjects([$subject->id]);

        $response = $this->actingAs($admin)->delete(route('admin.subjects.destroy', $subject));

        $response->assertRedirect(route('admin.subjects.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('subjects', ['id' => $subject->id]);
        $this->assertDatabaseHas('subject_tutor', [
            'subject_id' => $subject->id,
            'tutor_id' => $tutor->id,
        ]);
    }

    public function test_tutor_subject_relationship_is_preserved_when_deletion_is_blocked(): void
    {
        $admin = $this->createAdmin();
        $subject = $this->createSubject();
        $tutor = $this->createTutorWithSubjects([$subject->id]);

        $this->actingAs($admin)->delete(route('admin.subjects.destroy', $subject));

        $this->assertTrue($tutor->fresh()->subjects->contains($subject->id));
    }

    // =========================================================================
    // Mata Pelajaran Nonaktif — Penugasan Tutor
    // =========================================================================

    public function test_inactive_subject_cannot_be_newly_assigned_to_tutor(): void
    {
        $admin = $this->createAdmin();
        $inactiveSubject = $this->createSubject(['status' => 'inactive']);

        $response = $this->actingAs($admin)->post(route('admin.tutors.store'), [
            'name' => 'Tutor Baru',
            'email' => 'tutor-baru@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'status' => 'active',
            'subject_ids' => [$inactiveSubject->id],
        ]);

        $response->assertSessionHasErrors('subject_ids');
    }

    public function test_existing_inactive_subject_can_be_retained_on_tutor_update(): void
    {
        $admin = $this->createAdmin();
        $subject = $this->createSubject(['status' => 'active']);
        $tutor = $this->createTutorWithSubjects([$subject->id]);

        $subject->update(['status' => 'inactive']);

        $response = $this->actingAs($admin)->put(route('admin.tutors.update', $tutor), [
            'name' => $tutor->user->name,
            'email' => $tutor->user->email,
            'status' => 'active',
            'subject_ids' => [$subject->id],
        ]);

        $response->assertSessionDoesntHaveErrors('subject_ids');
        $this->assertTrue($tutor->fresh()->subjects->contains($subject->id));
    }

    public function test_new_inactive_subject_cannot_be_added_on_tutor_update(): void
    {
        $admin = $this->createAdmin();
        $activeSubject = $this->createSubject(['status' => 'active']);
        $inactiveSubject = $this->createSubject(['status' => 'inactive']);
        $tutor = $this->createTutorWithSubjects([$activeSubject->id]);

        $response = $this->actingAs($admin)->put(route('admin.tutors.update', $tutor), [
            'name' => $tutor->user->name,
            'email' => $tutor->user->email,
            'status' => 'active',
            'subject_ids' => [$activeSubject->id, $inactiveSubject->id],
        ]);

        $response->assertSessionHasErrors('subject_ids');
    }
}