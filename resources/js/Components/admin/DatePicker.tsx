import MaterialIcon from "@/Components/MaterialIcon";
import { useEffect, useMemo, useRef, useState } from "react";

interface DatePickerProps {
    id?: string;
    /** Selected date as an ISO "YYYY-MM-DD" string, or "" when empty. */
    value: string;
    onChange: (value: string) => void;
    /** Minimum selectable date as "YYYY-MM-DD". Earlier dates are disabled. */
    min?: string;
    placeholder?: string;
}

const MONTHS = [
    "Januari",
    "Februari",
    "Maret",
    "April",
    "Mei",
    "Juni",
    "Juli",
    "Agustus",
    "September",
    "Oktober",
    "November",
    "Desember",
];

const WEEKDAYS = ["Sn", "Sl", "Rb", "Km", "Jm", "Sb", "Mg"];

/** Parse "YYYY-MM-DD" into a local Date (avoids timezone shifting). */
function parseISO(value: string): Date | null {
    if (!value) return null;
    const [y, m, d] = value.split("-").map(Number);
    if (!y || !m || !d) return null;
    return new Date(y, m - 1, d);
}

function toISO(date: Date): string {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, "0");
    const d = String(date.getDate()).padStart(2, "0");
    return `${y}-${m}-${d}`;
}

function formatDisplay(date: Date): string {
    return `${date.getDate()} ${MONTHS[date.getMonth()]} ${date.getFullYear()}`;
}

function startOfDay(date: Date): Date {
    return new Date(date.getFullYear(), date.getMonth(), date.getDate());
}

export default function DatePicker({
    id,
    value,
    onChange,
    min,
    placeholder = "Pilih tanggal",
}: DatePickerProps) {
    const [open, setOpen] = useState(false);
    const containerRef = useRef<HTMLDivElement>(null);

    const selectedDate = useMemo(() => parseISO(value), [value]);
    const minDate = useMemo(() => {
        const parsed = parseISO(min ?? "");
        return parsed ? startOfDay(parsed) : null;
    }, [min]);

    // The month currently shown in the calendar grid.
    const [viewDate, setViewDate] = useState<Date>(
        () => selectedDate ?? minDate ?? new Date(),
    );

    useEffect(() => {
        if (open) {
            setViewDate(selectedDate ?? minDate ?? new Date());
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open]);

    useEffect(() => {
        const onMouseDown = (event: MouseEvent) => {
            if (
                containerRef.current &&
                !containerRef.current.contains(event.target as Node)
            ) {
                setOpen(false);
            }
        };

        const onKeyDown = (event: KeyboardEvent) => {
            if (event.key === "Escape") setOpen(false);
        };

        document.addEventListener("mousedown", onMouseDown);
        document.addEventListener("keydown", onKeyDown);

        return () => {
            document.removeEventListener("mousedown", onMouseDown);
            document.removeEventListener("keydown", onKeyDown);
        };
    }, []);

    const year = viewDate.getFullYear();
    const month = viewDate.getMonth();

    // Build the grid: leading blanks (Mon-first) + days of month.
    const cells = useMemo(() => {
        const firstOfMonth = new Date(year, month, 1);
        // getDay(): 0=Sun..6=Sat -> convert to Mon-first index 0=Mon..6=Sun
        const lead = (firstOfMonth.getDay() + 6) % 7;
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        const list: (Date | null)[] = [];
        for (let i = 0; i < lead; i += 1) list.push(null);
        for (let d = 1; d <= daysInMonth; d += 1) {
            list.push(new Date(year, month, d));
        }
        return list;
    }, [year, month]);

    // Year range for the dropdown (10 years back to 5 forward, or from min).
    const years = useMemo(() => {
        const current = new Date().getFullYear();
        const start = minDate
            ? Math.min(minDate.getFullYear(), current - 5)
            : current - 10;
        const end = current + 5;
        const list: number[] = [];
        for (let y = start; y <= end; y += 1) list.push(y);
        return list;
    }, [minDate]);

    const today = startOfDay(new Date());

    const isDisabled = (date: Date) =>
        minDate ? startOfDay(date) < minDate : false;

    const isSameDay = (a: Date, b: Date | null) =>
        b !== null &&
        a.getFullYear() === b.getFullYear() &&
        a.getMonth() === b.getMonth() &&
        a.getDate() === b.getDate();

    const pick = (date: Date) => {
        if (isDisabled(date)) return;
        onChange(toISO(date));
        setOpen(false);
    };

    const changeMonth = (delta: number) => {
        setViewDate(new Date(year, month + delta, 1));
    };

    return (
        <div ref={containerRef} className="relative">
            {/* Trigger */}
            <button
                id={id}
                type="button"
                onClick={() => setOpen((prev) => !prev)}
                className={
                    "flex h-10 w-full items-center justify-between gap-2 rounded-lg bg-surface-container-low text-sm font-medium transition-colors " +
                    (open
                        ? "border-2 border-primary px-[11px]"
                        : "border border-outline-variant px-3 hover:border-primary/50")
                }
            >
                <span className="flex min-w-0 items-center gap-2">
                    <MaterialIcon
                        name="calendar_month"
                        className="flex-shrink-0 text-on-surface-variant"
                        style={{ fontSize: 18 }}
                    />
                    <span
                        className={
                            "truncate " +
                            (selectedDate
                                ? "text-on-surface"
                                : "text-on-surface-variant")
                        }
                    >
                        {selectedDate ? formatDisplay(selectedDate) : placeholder}
                    </span>
                </span>
                {selectedDate && (
                    <span
                        role="button"
                        tabIndex={-1}
                        aria-label="Hapus tanggal"
                        onClick={(event) => {
                            event.stopPropagation();
                            onChange("");
                        }}
                        className="flex flex-shrink-0 items-center text-on-surface-variant hover:text-error"
                    >
                        <MaterialIcon name="close" style={{ fontSize: 16 }} />
                    </span>
                )}
            </button>

            {/* Calendar popover */}
            {open && (
                <div className="absolute left-0 top-full z-50 mt-2 w-[17rem] rounded-2xl border border-outline-variant bg-white p-3 shadow-xl">
                    {/* Header: month/year selectors + nav */}
                    <div className="mb-2 flex items-center gap-1.5">
                        <button
                            type="button"
                            onClick={() => changeMonth(-1)}
                            className="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg text-on-surface-variant transition-colors hover:bg-surface-container-low"
                            aria-label="Bulan sebelumnya"
                        >
                            <MaterialIcon
                                name="chevron_left"
                                style={{ fontSize: 20 }}
                            />
                        </button>

                        <select
                            value={month}
                            onChange={(event) =>
                                setViewDate(
                                    new Date(year, Number(event.target.value), 1),
                                )
                            }
                            className="h-8 flex-1 rounded-lg border-outline-variant bg-surface-container-low px-2 text-xs font-semibold text-on-surface focus:border-primary focus:ring-primary"
                        >
                            {MONTHS.map((name, index) => (
                                <option key={name} value={index}>
                                    {name}
                                </option>
                            ))}
                        </select>

                        <select
                            value={year}
                            onChange={(event) =>
                                setViewDate(
                                    new Date(Number(event.target.value), month, 1),
                                )
                            }
                            className="h-8 w-[4.5rem] flex-shrink-0 rounded-lg border-outline-variant bg-surface-container-low px-2 text-xs font-semibold text-on-surface focus:border-primary focus:ring-primary"
                        >
                            {years.map((y) => (
                                <option key={y} value={y}>
                                    {y}
                                </option>
                            ))}
                        </select>

                        <button
                            type="button"
                            onClick={() => changeMonth(1)}
                            className="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg text-on-surface-variant transition-colors hover:bg-surface-container-low"
                            aria-label="Bulan berikutnya"
                        >
                            <MaterialIcon
                                name="chevron_right"
                                style={{ fontSize: 20 }}
                            />
                        </button>
                    </div>

                    {/* Weekday labels */}
                    <div className="grid grid-cols-7 gap-0.5">
                        {WEEKDAYS.map((day) => (
                            <div
                                key={day}
                                className="flex h-7 items-center justify-center text-[11px] font-semibold text-on-surface-variant"
                            >
                                {day}
                            </div>
                        ))}
                    </div>

                    {/* Day grid */}
                    <div className="grid grid-cols-7 gap-0.5">
                        {cells.map((date, index) => {
                            if (!date) {
                                return <div key={`blank-${index}`} />;
                            }

                            const disabled = isDisabled(date);
                            const selected = isSameDay(date, selectedDate);
                            const isToday = isSameDay(date, today);

                            return (
                                <button
                                    key={toISO(date)}
                                    type="button"
                                    disabled={disabled}
                                    onClick={() => pick(date)}
                                    className={
                                        "flex h-8 w-8 items-center justify-center rounded-lg text-xs font-medium transition-colors " +
                                        (selected
                                            ? "bg-primary text-white"
                                            : disabled
                                              ? "cursor-not-allowed text-outline-variant"
                                              : isToday
                                                ? "text-primary ring-1 ring-inset ring-primary hover:bg-surface-container-low"
                                                : "text-on-surface hover:bg-surface-container-low")
                                    }
                                >
                                    {date.getDate()}
                                </button>
                            );
                        })}
                    </div>
                </div>
            )}
        </div>
    );
}
