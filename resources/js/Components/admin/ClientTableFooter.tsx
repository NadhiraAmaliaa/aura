import MaterialIcon from "@/Components/MaterialIcon";

/**
 * Pagination footer for client-side paginated tables (the full dataset lives in
 * the browser). Matches the look of the server-side TableFooter and the
 * Reporting Absensi table controls.
 */
export default function ClientTableFooter({
    from,
    to,
    total,
    page,
    totalPages,
    onPageChange,
}: {
    from: number;
    to: number;
    total: number;
    page: number;
    totalPages: number;
    onPageChange: (page: number) => void;
}) {
    const pages = Array.from({ length: totalPages }, (_, i) => i + 1)
        .filter((p) => p === 1 || p === totalPages || Math.abs(p - page) <= 1)
        .reduce<(number | "...")[]>((acc, p, idx, arr) => {
            if (idx > 0 && (p as number) - (arr[idx - 1] as number) > 1) {
                acc.push("...");
            }
            acc.push(p);
            return acc;
        }, []);

    return (
        <div className="flex flex-col items-center justify-between gap-3 border-t border-outline-variant bg-surface-container-lowest px-6 py-4 sm:flex-row">
            <p className="text-sm text-on-surface-variant">
                Menampilkan{" "}
                <span className="font-bold text-on-surface">
                    {from} - {to}
                </span>{" "}
                dari <span className="font-bold text-on-surface">{total}</span>{" "}
                entitas
            </p>

            <div className="flex items-center gap-1">
                <button
                    type="button"
                    onClick={() => onPageChange(Math.max(1, page - 1))}
                    disabled={page <= 1}
                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant transition hover:bg-surface-container-low disabled:opacity-40"
                >
                    <MaterialIcon
                        name="chevron_left"
                        style={{ fontSize: 18 }}
                    />
                </button>

                {pages.map((p, idx) =>
                    p === "..." ? (
                        <span
                            key={`ellipsis-${idx}`}
                            className="px-1 text-sm text-on-surface-variant"
                        >
                            …
                        </span>
                    ) : (
                        <button
                            key={p}
                            type="button"
                            onClick={() => onPageChange(p as number)}
                            className={`inline-flex h-8 w-8 items-center justify-center rounded-lg border text-sm transition ${
                                p === page
                                    ? "border-primary bg-primary font-bold text-white"
                                    : "border-outline-variant text-on-surface-variant hover:bg-surface-container-low"
                            }`}
                        >
                            {p}
                        </button>
                    ),
                )}

                <button
                    type="button"
                    onClick={() => onPageChange(Math.min(totalPages, page + 1))}
                    disabled={page >= totalPages}
                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant transition hover:bg-surface-container-low disabled:opacity-40"
                >
                    <MaterialIcon
                        name="chevron_right"
                        style={{ fontSize: 18 }}
                    />
                </button>
            </div>
        </div>
    );
}
