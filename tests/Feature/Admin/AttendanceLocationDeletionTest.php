<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the admin office-location deletion policy: an office that has already
 * been used for attendance is preserved (deactivate instead), while an unused
 * office can still be hard-deleted. This protects the frozen geofence snapshot
 * that historical attendance records reference.
 */
class AttendanceLocationDeletionTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_referenced_office_cannot_be_hard_deleted(): void
    {
        $office = AttendanceLocation::create([
            'name' => 'Kantor Pusat',
            'latitude' => 3.5952000,
            'longitude' => 98.6722000,
            'radius' => 200,
            'is_active' => true,
        ]);

        Attendance::factory()->create([
            'check_in_office_id' => $office->id,
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.attendance-locations.destroy', $office))
            ->assertRedirect(route('admin.attendance-locations.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('attendance_locations', ['id' => $office->id]);
    }

    public function test_unreferenced_office_can_be_deleted(): void
    {
        $office = AttendanceLocation::create([
            'name' => 'Kantor Cabang',
            'latitude' => 3.7000000,
            'longitude' => 98.6722000,
            'radius' => 200,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.attendance-locations.destroy', $office))
            ->assertRedirect(route('admin.attendance-locations.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('attendance_locations', ['id' => $office->id]);
    }
}
