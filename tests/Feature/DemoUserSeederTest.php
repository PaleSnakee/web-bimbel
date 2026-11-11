<?php

namespace Tests\Feature;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\User;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoUserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_demo_accounts_with_their_required_profiles(): void
    {
        $this->seed(DemoUserSeeder::class);

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $tutor = User::query()->where('email', 'tutor@example.com')->firstOrFail();
        $student = User::query()->where('email', 'student@example.com')->firstOrFail();
        $parent = User::query()->where('email', 'parent@example.com')->firstOrFail();

        $this->assertSame('admin', $admin->role);
        $this->assertSame('tutor', $tutor->role);
        $this->assertSame('student', $student->role);
        $this->assertSame('parent', $parent->role);
        $this->assertSame('active', $admin->status);
        $this->assertSame('active', $tutor->status);
        $this->assertSame('active', $student->status);
        $this->assertSame('active', $parent->status);

        $this->assertNull($admin->tutor);
        $this->assertNull($admin->student);
        $this->assertNull($admin->guardian);

        $this->assertNotNull($tutor->tutor);
        $this->assertTrue($tutor->tutor->user->is($tutor));

        $this->assertNotNull($student->student);
        $this->assertSame('DEMO-STUDENT-001', $student->student->nis);
        $this->assertSame('SD', $student->student->education_level);
        $this->assertTrue($student->student->user->is($student));

        $this->assertNotNull($parent->guardian);
        $this->assertTrue($parent->guardian->user->is($parent));

        $this->assertTrue(Hash::check('password', $admin->password));
        $this->assertNotSame('password', $admin->password);
    }

    public function test_it_is_idempotent_and_profiles_match_their_users_roles(): void
    {
        $this->seed(DemoUserSeeder::class);
        $this->seed(DemoUserSeeder::class);

        $demoEmails = [
            'admin@example.com',
            'tutor@example.com',
            'student@example.com',
            'parent@example.com',
        ];

        $this->assertSame(4, User::query()->whereIn('email', $demoEmails)->count());
        $this->assertSame(1, Tutor::query()->count());
        $this->assertSame(1, Student::query()->count());
        $this->assertSame(1, Guardian::query()->count());

        $this->assertSame(
            0,
            Tutor::query()->whereHas('user', fn ($query) => $query->where('role', '!=', 'tutor'))->count(),
        );
        $this->assertSame(
            0,
            Student::query()->whereHas('user', fn ($query) => $query->where('role', '!=', 'student'))->count(),
        );
        $this->assertSame(
            0,
            Guardian::query()->whereHas('user', fn ($query) => $query->where('role', '!=', 'parent'))->count(),
        );
    }
}
