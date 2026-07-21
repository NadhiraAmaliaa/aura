<?php

namespace Tests\Feature\Api\V1\Attendance;

use App\Models\AttendanceLocation;
use App\Models\Intern;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttendanceLocationsTest extends TestCase
{
    use RefreshDatabase;

    private function internUser(): User
    {
        $user = User::factory()->create(['role' => 'intern']);

        Intern::factory()->create([
            'user_id' => $user->id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => Intern::STATUS_ACTIVE,
        ]);

        return $user->fresh();
    }

    public function test_locations_requires_authentication(): void
    {
        $this->getJson('/api/v1/attendance/locations')->assertUnauthorized();
    }

    public function test_locations_returns_only_active_locations_ordered_by_name(): void
    {
        AttendanceLocation::create([
            'name' => 'Kantor Cabang',
            'latitude' => 3.6000000,
            'longitude' => 98.6800000,
            'radius' => 150,
            'is_active' => true,
        ]);

        AttendanceLocation::create([
            'name' => 'Kantor Pusat',
            'latitude' => 3.5952000,
            'longitude' => 98.6722000,
            'radius' => 200,
            'is_active' => true,
        ]);

        AttendanceLocation::create([
            'name' => 'Kantor Lama',
            'latitude' => 3.5000000,
            'longitude' => 98.5000000,
            'radius' => 100,
            'is_active' => false,
        ]);

        Sanctum::actingAs($this->internUser());

        $this->getJson('/api/v1/attendance/locations')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Kantor Cabang')
            ->assertJsonPath('data.1.name', 'Kantor Pusat')
            // Coordinates are numeric for map compatibility.
            ->assertJsonPath('data.1.latitude', 3.5952)
            ->assertJsonPath('data.1.longitude', 98.6722)
            ->assertJsonPath('data.1.radius', 200);
    }

    public function test_locations_returns_empty_when_none_active(): void
    {
        AttendanceLocation::create([
            'name' => 'Kantor Lama',
            'latitude' => 3.5000000,
            'longitude' => 98.5000000,
            'radius' => 100,
            'is_active' => false,
        ]);

        Sanctum::actingAs($this->internUser());

        $this->getJson('/api/v1/attendance/locations')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
