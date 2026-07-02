import MaterialIcon from "@/Components/MaterialIcon";

export default function SummaryCard({
    label,
    value,
    total = 0,
    icon,
    bgGradient,
}: {
    label: string;
    value: number;
    total?: number;
    icon: string;
    bgGradient: string;
}) {
    const pct = total > 0 ? Math.round((value / total) * 100) : 0;
    const showBar = total > 0;
    return (
        <div
            className={`relative overflow-hidden rounded-2xl px-5 py-4 text-white shadow-lg transition-all duration-300 hover:shadow-2xl hover:scale-105 ${
                bgGradient
            } before:absolute before:right-0 before:top-1/2 before:-translate-y-1/2 before:text-white before:opacity-20 before:-mr-4`}
        >
            {/* Background Icon (large, semi-transparent, animated float) */}
            <div className="absolute right-0 top-4 text-white opacity-20 icon-float">
                <MaterialIcon name={icon} filled style={{ fontSize: 120 }} />
            </div>

            {/* Content */}
            <div className="relative z-10">
                <p className="truncate text-xs font-semibold uppercase tracking-wider text-white/80">
                    {label}
                </p>
                <p className="mt-2 text-4xl font-extrabold tabular-nums text-white">
                    {value}
                </p>
                <div className="mt-4 space-y-1.5 h-10">
                    {showBar ? (
                        <>
                            <div className="h-1.5 w-full overflow-hidden rounded-full bg-white/30">
                                <div
                                    className="h-full rounded-full bg-white/70 progress-bar-fill transition-all duration-1000"
                                    style={{ width: `${Math.min(pct, 100)}%` }}
                                />
                            </div>
                            <p className="text-xs text-white/70">
                                {pct}% dari total
                            </p>
                        </>
                    ) : null}
                </div>
            </div>
        </div>
    );
}
