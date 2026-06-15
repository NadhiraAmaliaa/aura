import Badge from '@/Components/Badge';
import { useGeolocation } from '@/lib/useGeolocation';
import {
    attendanceStatusBadge,
    attendanceStatusLabels,
    formatTime,
    workModeBadge,
    workModeLabels,
} from '@/lib/labels';
import { Attendance, LeaveRequest, WorkMode } from '@/types';
import { router } from '@inertiajs/react';
import { useState } from 'react';

const workModeOptions: { value: WorkMode; title: string; description: string }[] =
    [
        {
            value: 'wfo',
            title: 'WFO (Bekerja dari Kantor)',
            description:
                'Berlaku batas waktu & status terlambat. Validasi lokasi kantor menyusul.',
        },
        {
            value: 'wfh',
            title: 'WFH (Bekerja dari Rumah)',
            description:
                'Berlaku batas waktu & status terlambat. Tanpa validasi lokasi.',
        },
        {
            value: 'dinas',
            title: 'Dinas (Tugas Luar)',
            description:
                'Dapat Check In kapan saja & di mana saja. Tanpa status terlambat.',
        },
    ];

export default function TodayAttendanceCard({
    todayAttendance,
    todayLeave,
    expectedCheckOut,
}: {
    todayAttendance: Attendance | null;
    todayLeave: LeaveRequest | null;
    expectedCheckOut: string | null;
}) {
    const { capture, locating } = useGeolocation();
    const [workMode, setWorkMode] = useState<WorkMode>('wfo');
    const [processing, setProcessing] = useState(false);

    const today = new Date().toLocaleDateString('id-ID', {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });

    const checkIn = async () => {
        setProcessing(true);
        const coords = await capture();
        router.post(
            route('intern.attendance.check-in'),
            { work_mode: workMode, ...coords },
            { onFinish: () => setProcessing(false), preserveScroll: true },
        );
    };

    const checkOut = async () => {
        setProcessing(true);
        const coords = await capture();
        router.post(
            route('intern.attendance.check-out'),
            { ...coords },
            { onFinish: () => setProcessing(false), preserveScroll: true },
        );
    };

    const buttonLabel = (action: string) =>
        locating
            ? 'Mengambil lokasi...'
            : processing
              ? 'Memproses...'
              : action;

    return (
        <div className="rounded-lg bg-white p-6 shadow">
            <div className="mb-4">
                <h3 className="text-lg font-semibold text-gray-900">
                    Absensi Hari Ini
                </h3>
                <p className="text-sm text-gray-500">{today}</p>
                <p className="mt-1 text-sm text-gray-500">
                    {expectedCheckOut
                        ? `Jam pulang yang diharapkan hari ini: ${expectedCheckOut} (informasi).`
                        : 'Akhir pekan — absensi lembur atau kegiatan khusus diperbolehkan.'}
                </p>
            </div>

            {todayLeave ? (
                <div className="rounded-md bg-blue-50 px-4 py-3 text-sm text-blue-800">
                    Hari ini Anda tercatat{' '}
                    {todayLeave.type === 'sakit' ? 'Sakit' : 'Izin'} berdasarkan
                    pengajuan yang telah disetujui. Anda tidak dapat melakukan
                    Check In.
                </div>
            ) : !todayAttendance ? (
                <div>
                    <p className="mb-4 text-gray-600">
                        Anda belum Check In hari ini. Pilih mode kehadiran:
                    </p>

                    <div className="max-w-md space-y-3">
                        {workModeOptions.map((option) => (
                            <label
                                key={option.value}
                                className={`block cursor-pointer rounded-lg border p-4 transition ${
                                    workMode === option.value
                                        ? 'border-green-600 bg-green-50'
                                        : 'border-gray-200 hover:bg-gray-50'
                                }`}
                            >
                                <span className="flex items-center gap-2 font-medium text-gray-900">
                                    <input
                                        type="radio"
                                        name="work_mode"
                                        value={option.value}
                                        checked={workMode === option.value}
                                        onChange={() =>
                                            setWorkMode(option.value)
                                        }
                                        className="text-green-700 focus:ring-green-600"
                                    />
                                    {option.title}
                                </span>
                                <p className="ml-6 mt-2 text-xs text-gray-500">
                                    {option.description}
                                </p>
                            </label>
                        ))}
                    </div>

                    <button
                        onClick={checkIn}
                        disabled={processing || locating}
                        className="mt-4 rounded-md bg-green-700 px-5 py-2 text-sm font-semibold text-white transition hover:bg-green-800 disabled:opacity-50"
                    >
                        {buttonLabel('Check In')}
                    </button>
                </div>
            ) : !todayAttendance.check_out_time ? (
                <div>
                    <p className="mb-3 text-gray-600">
                        Check In pada pukul{' '}
                        <span className="font-medium">
                            {formatTime(todayAttendance.check_in_time)}
                        </span>
                        <Badge
                            className={`ml-2 ${attendanceStatusBadge[todayAttendance.status]}`}
                        >
                            {attendanceStatusLabels[todayAttendance.status]}
                        </Badge>
                        {todayAttendance.work_mode && (
                            <Badge
                                className={`ml-1 ${workModeBadge[todayAttendance.work_mode]}`}
                            >
                                {workModeLabels[todayAttendance.work_mode]}
                            </Badge>
                        )}
                    </p>
                    <button
                        onClick={checkOut}
                        disabled={processing || locating}
                        className="rounded-md bg-green-700 px-5 py-2 text-sm font-semibold text-white transition hover:bg-green-800 disabled:opacity-50"
                    >
                        {buttonLabel('Check Out')}
                    </button>
                </div>
            ) : (
                <div className="rounded-md bg-blue-50 px-4 py-3 text-sm text-blue-800">
                    Absensi hari ini telah selesai. Masuk:{' '}
                    {formatTime(todayAttendance.check_in_time)} &middot; Keluar:{' '}
                    {formatTime(todayAttendance.check_out_time)}
                </div>
            )}
        </div>
    );
}
