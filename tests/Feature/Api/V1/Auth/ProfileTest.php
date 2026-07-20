<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\Division;
use App\Models\Intern;
use App\Models\InternProgram;
use App\Models\StudyProgram;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create an active intern user wired to named master-data records and
     * return both the user and its intern profile.
     *
     * @return array{0: User, 1: Intern}
     */
    private function activeInternUser(array $userAttributes = []): array
    {
        $university = University::factory()->create(['name' => 'Universitas Negeri Yogyakarta']);
        $studyProgram = StudyProgram::factory()->create([
            'university_id' => $university->id,
            'name' => 'Pendidikan Teknik Informatika',
        ]);
        $division = Division::factory()->create(['name' => 'Pengembangan Aplikasi']);
        $program = InternProgram::factory()->create(['name' => 'Magang Merdeka Berbasis Kampus']);

        $user = User::factory()->create(array_merge([
            'role' => 'intern',
            'nik' => null,
            'name' => 'Nadhira Amalia',
            'email' => 'nadhira.amalia@student.uny.ac.id',
        ], $userAttributes));

        $intern = Intern::factory()->create([
            'user_id' => $user->id,
            'university_id' => $university->id,
            'study_program_id' => $studyProgram->id,
            'division_id' => $division->id,
            'intern_program_id' => $program->id,
            'nim' => '203040044',
            'phone' => '0812-3456-7890',
            'start_date' => '2025-02-12',
            'end_date' => '2025-08-15',
            'status' => Intern::STATUS_ACTIVE,
        ]);

        return [$user->fresh(), $intern];
    }

    public function test_me_returns_expanded_intern_profile(): void
    {
        [$user] = $this->activeInternUser();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.name', 'Nadhira Amalia')
            ->assertJsonPath('data.email', 'nadhira.amalia@student.uny.ac.id')
            ->assertJsonPath('data.intern.nim', '203040044')
            ->assertJsonPath('data.intern.phone', '0812-3456-7890')
            ->assertJsonPath('data.intern.university', 'Universitas Negeri Yogyakarta')
            ->assertJsonPath('data.intern.major', 'Pendidikan Teknik Informatika')
            ->assertJsonPath('data.intern.program', 'Magang Merdeka Berbasis Kampus')
            ->assertJsonPath('data.intern.division', 'Pengembangan Aplikasi')
            ->assertJsonPath('data.intern.start_date', '2025-02-12')
            ->assertJsonPath('data.intern.end_date', '2025-08-15');
    }

    public function test_me_falls_back_to_legacy_free_text_columns(): void
    {
        $user = User::factory()->create(['role' => 'intern', 'nik' => null]);
        Intern::factory()->create([
            'user_id' => $user->id,
            'university_id' => null,
            'study_program_id' => null,
            'division_id' => null,
            'university' => 'Kampus Lama',
            'major' => 'Teknik Lama',
            'division' => 'Divisi Lama',
        ]);
        Sanctum::actingAs($user->fresh());

        $this->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.intern.university', 'Kampus Lama')
            ->assertJsonPath('data.intern.major', 'Teknik Lama')
            ->assertJsonPath('data.intern.division', 'Divisi Lama');
    }

    public function test_update_contact_requires_authentication(): void
    {
        $this->patchJson('/api/v1/auth/profile/contact', [
            'email' => 'x@example.com',
        ])->assertUnauthorized();
    }

    public function test_update_contact_updates_email_and_phone(): void
    {
        [$user, $intern] = $this->activeInternUser();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/auth/profile/contact', [
            'email' => 'baru@student.uny.ac.id',
            'phone' => '0899-0000-1111',
        ])
            ->assertOk()
            ->assertJsonPath('data.email', 'baru@student.uny.ac.id')
            ->assertJsonPath('data.intern.phone', '0899-0000-1111');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'baru@student.uny.ac.id',
        ]);
        $this->assertDatabaseHas('interns', [
            'id' => $intern->id,
            'phone' => '0899-0000-1111',
        ]);
    }

    public function test_update_contact_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        [$user] = $this->activeInternUser();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/auth/profile/contact', [
            'email' => 'taken@example.com',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_update_contact_allows_keeping_own_email(): void
    {
        [$user] = $this->activeInternUser();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/auth/profile/contact', [
            'email' => 'nadhira.amalia@student.uny.ac.id',
            'phone' => '0812-0000-0000',
        ])->assertOk();
    }

    public function test_update_password_requires_authentication(): void
    {
        $this->putJson('/api/v1/auth/password', [
            'current_password' => 'secret',
            'password' => 'newsecret',
            'password_confirmation' => 'newsecret',
        ])->assertUnauthorized();
    }

    public function test_update_password_changes_the_password(): void
    {
        [$user] = $this->activeInternUser(['password' => Hash::make('old-password')]);
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/auth/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertOk();

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_update_password_rejects_wrong_current_password(): void
    {
        [$user] = $this->activeInternUser(['password' => Hash::make('old-password')]);
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/auth/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('current_password');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_update_password_requires_confirmation(): void
    {
        [$user] = $this->activeInternUser(['password' => Hash::make('old-password')]);
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/auth/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'mismatch',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }
}
