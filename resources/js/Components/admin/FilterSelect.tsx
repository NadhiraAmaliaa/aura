import MaterialIcon from "@/Components/MaterialIcon";
import { useEffect, useRef, useState } from "react";

export interface FilterSelectOption {
    value: string;
    label: string;
    /** Optional CSS color string — renders a colored dot next to the label. */
    dot?: string;
}

interface FilterSelectProps {
    id?: string;
    value: string;
    onChange: (value: string) => void;
    options: FilterSelectOption[];
    /** Text shown when nothing is selected ("all" state). */
    placeholder: string;
}

export default function FilterSelect({
    id,
    value,
    onChange,
    options,
    placeholder,
}: FilterSelectProps) {
    const [open, setOpen] = useState(false);
    const containerRef = useRef<HTMLDivElement>(null);

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
            if (event.key === "Escape") {
                setOpen(false);
            }
        };

        document.addEventListener("mousedown", onMouseDown);
        document.addEventListener("keydown", onKeyDown);

        return () => {
            document.removeEventListener("mousedown", onMouseDown);
            document.removeEventListener("keydown", onKeyDown);
        };
    }, []);

    const selected = options.find((o) => o.value === value);

    const rows: (FilterSelectOption & { isAll?: boolean })[] = [
        { value: "", label: placeholder, isAll: true },
        ...options,
    ];

    const select = (next: string) => {
        onChange(next);
        setOpen(false);
    };

    return (
        <div ref={containerRef} className="relative">
            {/* Trigger */}
            <button
                id={id}
                type="button"
                onClick={() => setOpen((prev) => !prev)}
                aria-haspopup="listbox"
                aria-expanded={open}
                className={
                    "flex h-10 w-full items-center justify-between gap-2 rounded-lg bg-surface-container-low text-sm font-medium transition-colors " +
                    (open
                        ? "border-2 border-primary px-[13px]"
                        : "border border-outline-variant px-3.5 hover:border-primary/50")
                }
            >
                <span className="flex min-w-0 items-center gap-2.5">
                    {selected?.dot && (
                        <span
                            className="h-2.5 w-2.5 flex-shrink-0 rounded-full ring-2 ring-white"
                            style={{ backgroundColor: selected.dot }}
                        />
                    )}
                    <span
                        className={
                            "truncate " +
                            (selected
                                ? "text-on-surface"
                                : "text-on-surface-variant")
                        }
                    >
                        {selected?.label ?? placeholder}
                    </span>
                </span>
                <MaterialIcon
                    name="expand_more"
                    className={
                        "flex-shrink-0 text-on-surface-variant transition-transform duration-200 " +
                        (open ? "rotate-180" : "")
                    }
                    style={{ fontSize: 20 }}
                />
            </button>

            {/* Dropdown */}
            {open && (
                <div
                    role="listbox"
                    className="absolute left-0 top-full z-50 mt-2 min-w-full origin-top rounded-2xl border border-outline-variant bg-white p-1.5 shadow-xl"
                >
                    {rows.map((row) => {
                        const isSelected = value === row.value;

                        return (
                            <button
                                key={row.isAll ? "__all__" : row.value}
                                type="button"
                                role="option"
                                aria-selected={isSelected}
                                onClick={() => select(row.value)}
                                className={
                                    "flex w-full items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors " +
                                    (isSelected
                                        ? "bg-surface-container-high"
                                        : "hover:bg-surface-container-low")
                                }
                            >
                                <span className="flex min-w-0 items-center gap-3">
                                    {row.dot && (
                                        <span
                                            className="h-2.5 w-2.5 flex-shrink-0 rounded-full"
                                            style={{
                                                backgroundColor: row.dot,
                                            }}
                                        />
                                    )}
                                    <span
                                        className={
                                            "truncate " +
                                            (isSelected
                                                ? "font-semibold text-primary"
                                                : "font-medium text-on-surface")
                                        }
                                    >
                                        {row.label}
                                    </span>
                                </span>
                                {isSelected && (
                                    <MaterialIcon
                                        name="check"
                                        className="flex-shrink-0 text-primary"
                                        style={{ fontSize: 18 }}
                                    />
                                )}
                            </button>
                        );
                    })}
                </div>
            )}
        </div>
    );
}
