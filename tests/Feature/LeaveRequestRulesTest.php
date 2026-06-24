<?php

namespace Tests\Feature;

use App\Models\Intern;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LeaveRequestRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function makeIntern(): User
    {
        $user = User::factory()->create(['role' => 'intern']);

        Intern::factory()->for($user)->create([
            'status' => Intern::STATUS_ACTIVE,
            'start_date' => Carbon::today()->subDays(10),
            'end_date' => Carbon::today()->addDays(30),
        ]);

        return $user->refresh();
    }

    public function test_izin_for_today_is_rejected(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-09 09:00'));
        $user = $this->makeIntern();

        $this->actingAs($user)
            ->post(route('intern.leave-requests.store'), [
                'type' => 'izin',
                'reason' => 'Keperluan keluarga.',
                'start_date' => Carbon::today()->toDateString(),
                'end_date' => Carbon::today()->toDateString(),
            ])
            ->assertSessionHasErrors('start_date');

        $this->assertDatabaseCount('leave_requests', 0);
    }

    public function test_izin_for_tomorrow_is_accepted(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-09 09:00'));
        $user = $this->makeIntern();

        $this->actingAs($user)
            ->post(route('intern.leave-requests.store'), [
                'type' => 'izin',
                'reason' => 'Keperluan keluarga.',
                'start_date' => Carbon::today()->addDay()->toDateString(),
                'end_date' => Carbon::today()->addDay()->toDateString(),
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $user->id,
            'type' => 'izin',
        ]);
    }

    public function test_sakit_for_today_requires_evidence(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-09 09:00'));
        $user = $this->makeIntern();

        $this->actingAs($user)
            ->post(route('intern.leave-requests.store'), [
                'type' => 'sakit',
                'reason' => 'Demam.',
                'start_date' => Carbon::today()->toDateString(),
                'end_date' => Carbon::today()->toDateString(),
            ])
            ->assertSessionHasErrors('evidence');

        $this->assertDatabaseCount('leave_requests', 0);
    }

    public function test_sakit_with_evidence_is_accepted_and_stored(): void
    {
        Storage::fake('public');
        Carbon::setTestNow(Carbon::parse('2026-06-09 09:00'));
        $user = $this->makeIntern();

        $this->actingAs($user)
            ->post(route('intern.leave-requests.store'), [
                'type' => 'sakit',
                'reason' => 'Demam.',
                'start_date' => Carbon::today()->toDateString(),
                'end_date' => Carbon::today()->toDateString(),
                'evidence' => UploadedFile::fake()->create('surat-sakit.pdf', 200, 'application/pdf'),
            ])
            ->assertSessionHasNoErrors();

        $leaveRequest = \App\Models\LeaveRequest::firstOrFail();

        $this->assertNotNull($leaveRequest->evidence_path);
        /** @var \Illuminate\Filesystem\FilesystemAdapter $fakeDisk */
        $fakeDisk = Storage::disk('public');
        $fakeDisk->assertExists($leaveRequest->evidence_path);
    }

    public function test_evidence_rejects_disallowed_file_type(): void
    {
        Storage::fake('public');
        Carbon::setTestNow(Carbon::parse('2026-06-09 09:00'));
        $user = $this->makeIntern();

        $this->actingAs($user)
            ->post(route('intern.leave-requests.store'), [
                'type' => 'sakit',
                'reason' => 'Demam.',
                'start_date' => Carbon::today()->toDateString(),
                'end_date' => Carbon::today()->toDateString(),
                'evidence' => UploadedFile::fake()->create('virus.exe', 100, 'application/octet-stream'),
            ])
            ->assertSessionHasErrors('evidence');

        $this->assertDatabaseCount('leave_requests', 0);
    }

    public function test_izin_evidence_is_optional(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-09 09:00'));
        $user = $this->makeIntern();

        $this->actingAs($user)
            ->post(route('intern.leave-requests.store'), [
                'type' => 'izin',
                'reason' => 'Keperluan keluarga.',
                'start_date' => Carbon::today()->addDay()->toDateString(),
                'end_date' => Carbon::today()->addDay()->toDateString(),
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $user->id,
            'type' => 'izin',
            'evidence_path' => null,
        ]);
    }
}
