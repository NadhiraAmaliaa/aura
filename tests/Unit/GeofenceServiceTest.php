<?php

namespace Tests\Unit;

use App\Models\AttendanceLocation;
use App\Services\GeofenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeofenceServiceTest extends TestCase
{
    use RefreshDatabase;

    private const OFFICE_LAT = 3.5952000;

    private const OFFICE_LNG = 98.6722000;

    public function test_nearest_active_returns_null_when_no_active_location(): void
    {
        AttendanceLocation::create([
            'name' => 'Kantor Lama',
            'latitude' => self::OFFICE_LAT,
            'longitude' => self::OFFICE_LNG,
            'radius' => 200,
            'is_active' => false,
        ]);

        $result = app(GeofenceService::class)->nearestActive(self::OFFICE_LAT, self::OFFICE_LNG);

        $this->assertNull($result);
    }

    public function test_nearest_active_picks_the_closest_location(): void
    {
        $far = AttendanceLocation::create([
            'name' => 'Kantor Jauh',
            'latitude' => 3.7000000,
            'longitude' => 98.8000000,
            'radius' => 200,
            'is_active' => true,
        ]);

        $near = AttendanceLocation::create([
            'name' => 'Kantor Dekat',
            'latitude' => self::OFFICE_LAT,
            'longitude' => self::OFFICE_LNG,
            'radius' => 200,
            'is_active' => true,
        ]);

        $result = app(GeofenceService::class)->nearestActive(self::OFFICE_LAT, self::OFFICE_LNG);

        $this->assertNotNull($result);
        $this->assertTrue($near->is($result['location']));
        $this->assertLessThan(1.0, $result['distance']);
        $this->assertNotSame($far->id, $result['location']->id);
    }
}
