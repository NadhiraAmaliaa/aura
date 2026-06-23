<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Intern;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class InternArchiveTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /**
     * Create an intern user with the given internship attributes.
     */
    private function makeIntern(array $attributes = []): User
    {
        $user = User::factory()->create(['role' => 'intern']);

        Intern::factory()->for($user)->create(array_merge([
            'status' => Intern::STATUS_ACTIVE,
            'start_date' => Carbon::today()->subDays(10),
            'end_date' => Carbon::today()->addDays(10),
        ], $attributes));

        return $user->refresh();
    }

    public function test_archiving_soft_deletes_the_intern_but_keeps_the_user_and_history(): void
    {
        $user = $this->makeIntern();
        $intern = $user->intern;

        Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::today(),
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.interns.destroy', $intern->id))
            ->assertRedirect();

        // Intern is soft-deleted, never destroyed.
        $this->assertSoftDeleted('interns', ['id' => $intern->id]);
        // The user account and attendance history are preserved.
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseHas('attendances', ['user_id' => $user->id]);
    }

    public function test_data_tab_excludes_archived_and_archive_tab_shows_only_archived(): void
    {
        $admin = $this->admin();

        $active = $this->makeIntern();
        $archived = $this->makeIntern();
        $archived->intern->delete();

        // Default (data) tab: only the active intern.
        $this->actingAs($admin)
            ->get(route('admin.interns.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('tab', 'data')
                ->where('tabCounts.data', 1)
                ->where('tabCounts.arsip', 1)
                ->where('interns.data', fn ($rows) => collect($rows)->pluck('user_id')->all() === [$active->id])
            );

        // Archive tab: only the archived intern.
        $this->actingAs($admin)
            ->get(route('admin.interns.index', ['tab' => 'arsip']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('tab', 'arsip')
                ->where('interns.data', fn ($rows) => collect($rows)->pluck('user_id')->all() === [$archived->id])
            );
    }

    public function test_restore_returns_the_intern_to_the_active_list(): void
    {
        $user = $this->makeIntern();
        $intern = $user->intern;
        $intern->delete();

        $this->actingAs($this->admin())
            ->patch(route('admin.interns.restore', $intern->id))
            ->assertRedirect();

        $this->assertDatabaseHas('interns', [
            'id' => $intern->id,
            'deleted_at' => null,
        ]);
    }

    public function test_archived_intern_is_still_included_in_attendance_reports(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-23 09:00'));

        $user = $this->makeIntern([
            'start_date' => Carbon::today()->subDays(5),
            'end_date' => Carbon::today()->addDays(5),
        ]);
        $name = $user->name;

        $user->intern->delete();

        $this->actingAs($this->admin())
            ->get(route('admin.attendances.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('report.rows', fn ($rows) => collect($rows)->pluck('nama')->contains($name))
            );
    }
}
