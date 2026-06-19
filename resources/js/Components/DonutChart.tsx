import { useMemo } from "react";

export interface DonutSlice {
    label: string;
    value: number;
    color: string;
}

interface DonutChartProps {
    data: DonutSlice[];
    /** Message shown when there is no data to display. */
    emptyMessage?: string;
    size?: number;
    thickness?: number;
}

/**
 * A dependency-free SVG donut chart with an inline legend.
 *
 * Slices with a zero value are ignored. When every slice is zero an empty
 * state is rendered instead of the ring.
 */
export default function DonutChart({
    data,
    emptyMessage = "Tidak ada data untuk ditampilkan.",
    size = 200,
    thickness = 28,
}: DonutChartProps) {
    const slices = useMemo(
        () => data.filter((slice) => slice.value > 0),
        [data],
    );

    const total = useMemo(
        () => slices.reduce((sum, slice) => sum + slice.value, 0),
        [slices],
    );

    if (total === 0) {
        return (
            <div className="flex h-full min-h-[200px] flex-col items-center justify-center text-center">
                <div className="flex h-32 w-32 items-center justify-center rounded-full border-8 border-dashed border-gray-200 text-gray-300">
                    <svg
                        className="h-10 w-10"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        strokeWidth={1.5}
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"
                        />
                    </svg>
                </div>
                <p className="mt-4 text-sm text-gray-500">{emptyMessage}</p>
            </div>
        );
    }

    const radius = (size - thickness) / 2;
    const circumference = 2 * Math.PI * radius;
    let offset = 0;

    return (
        <div className="flex flex-col items-center gap-6 sm:flex-row sm:items-center sm:justify-center">
            <div className="relative" style={{ width: size, height: size }}>
                <svg
                    width={size}
                    height={size}
                    viewBox={`0 0 ${size} ${size}`}
                    className="-rotate-90"
                >
                    <circle
                        cx={size / 2}
                        cy={size / 2}
                        r={radius}
                        fill="none"
                        stroke="#f1f5f9"
                        strokeWidth={thickness}
                    />
                    {slices.map((slice) => {
                        const fraction = slice.value / total;
                        const dash = fraction * circumference;
                        const segment = (
                            <circle
                                key={slice.label}
                                cx={size / 2}
                                cy={size / 2}
                                r={radius}
                                fill="none"
                                stroke={slice.color}
                                strokeWidth={thickness}
                                strokeDasharray={`${dash} ${circumference - dash}`}
                                strokeDashoffset={-offset}
                            />
                        );
                        offset += dash;
                        return segment;
                    })}
                </svg>
                <div className="absolute inset-0 flex flex-col items-center justify-center">
                    <span className="text-3xl font-bold text-gray-900">
                        {total}
                    </span>
                    <span className="text-xs uppercase tracking-wide text-gray-400">
                        Total
                    </span>
                </div>
            </div>

            <ul className="w-full space-y-2 sm:w-56">
                {slices.map((slice) => {
                    const percent = ((slice.value / total) * 100).toFixed(1);
                    return (
                        <li
                            key={slice.label}
                            className="flex items-center justify-between text-sm"
                        >
                            <span className="flex items-center gap-2 text-gray-600">
                                <span
                                    className="inline-block h-3 w-3 rounded-full"
                                    style={{ backgroundColor: slice.color }}
                                />
                                {slice.label}
                            </span>
                            <span className="font-medium text-gray-900">
                                {slice.value}{" "}
                                <span className="text-gray-400">
                                    ({percent}%)
                                </span>
                            </span>
                        </li>
                    );
                })}
            </ul>
        </div>
    );
}
