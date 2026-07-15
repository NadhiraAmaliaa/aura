<?php

namespace Tests\Feature\Api\V1\Leave;

use App\Models\Intern;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LeaveRequestStoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // A weekday so "tomorrow" is a working day (total_days = 1).
        Carbon::setTestNow(Carbon::parse('2026-06-09 09:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function activeInternUser(): User
    {
        $user = User::factory()->create(['role' => 'intern']);

        Intern::factory()->create([
            'user_id' => $user->id,
            'start_date' => Carbon::today()->subMonths(2)->toDateString(),
            'end_date' => Carbon::today()->addMonths(2)->toDateString(),
            'status' => Intern::STATUS_ACTIVE,
        ]);

        return $user->fresh();
    }

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/v1/leave-requests', [])->assertUnauthorized();
    }

    public function test_intern_can_submit_izin_for_tomorrow(): void
    {
        $user = $this->activeInternUser();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/leave-requests', [
            'type' => 'izin',
            'reason' => 'Keperluan keluarga.',
            'start_date' => Carbon::tomorrow()->toDateString(),
            'end_date' => Carbon::tomorrow()->toDateString(),
        ])
            ->assertCreated()
            ->assertJsonPath('data.type', 'izin')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total_days', 1);

        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $user->id,
            'type' => 'izin',
            'status' => 'pending',
        ]);
    }

    public function test_izin_for_today_is_rejected(): void
    {
        $user = $this->activeInternUser();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/leave-requests', [
            'type' => 'izin',
            'reason' => 'Keperluan keluarga.',
            'start_date' => Carbon::today()->toDateString(),
            'end_date' => Carbon::today()->toDateString(),
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('start_date');

        $this->assertDatabaseCount('leave_requests', 0);
    }

    public function test_sakit_requires_evidence(): void
    {
        $user = $this->activeInternUser();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/leave-requests', [
            'type' => 'sakit',
            'reason' => 'Demam.',
            'start_date' => Carbon::today()->toDateString(),
            'end_date' => Carbon::today()->toDateString(),
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('evidence');

        $this->assertDatabaseCount('leave_requests', 0);
    }

    public function test_sakit_with_evidence_is_stored(): void
    {
        Storage::fake('public');
        $user = $this->activeInternUser();
        Sanctum::actingAs($user);

        $this->post('/api/v1/leave-requests', [
            'type' => 'sakit',
            'reason' => 'Demam.',
            'start_date' => Carbon::today()->toDateString(),
            'end_date' => Carbon::today()->toDateString(),
            'evidence' => UploadedFile::fake()->create('surat-sakit.pdf', 200, 'application/pdf'),
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.type', 'sakit');

        $leaveRequest = LeaveRequest::firstOrFail();
        $this->assertNotNull($leaveRequest->evidence_path);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $disk->assertExists($leaveRequest->evidence_path);
    }

    public function test_evidence_rejects_disallowed_file_type(): void
    {
        Storage::fake('public');
        $user = $this->activeInternUser();
        Sanctum::actingAs($user);

        $this->post('/api/v1/leave-requests', [
            'type' => 'sakit',
            'reason' => 'Demam.',
            'start_date' => Carbon::today()->toDateString(),
            'end_date' => Carbon::today()->toDateString(),
            'evidence' => UploadedFile::fake()->create('virus.exe', 100, 'application/octet-stream'),
        ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('evidence');

        $this->assertDatabaseCount('leave_requests', 0);
    }

    public function test_total_days_is_calculated_across_range(): void
    {
        $user = $this->activeInternUser();
        Sanctum::actingAs($user);

        // Wed 2026-06-10 .. Fri 2026-06-12 → 3 working days.
        $this->postJson('/api/v1/leave-requests', [
            'type' => 'izin',
            'reason' => 'Acara keluarga.',
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-12',
        ])
            ->assertCreated()
            ->assertJsonPath('data.total_days', 3);
    }
}
