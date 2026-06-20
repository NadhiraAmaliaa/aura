import MaterialIcon from "@/Components/MaterialIcon";
import { ChangeEvent, ReactNode } from "react";

const PER_PAGE_OPTIONS = [10, 25, 50, 100];

export default function TableToolbar({
    search,
    onSearchChange,
    searchPlaceholder = "Pencarian:",
    perPage,
    onPerPageChange,
    children,
}: {
    search?: string;
    onSearchChange?: (value: string) => void;
    searchPlaceholder?: string;
    perPage?: number;
    onPerPageChange?: (value: number) => void;
    children?: ReactNode;
}) {
    const hasPerPage = perPage !== undefined && onPerPageChange !== undefined;
    const hasSearch = onSearchChange !== undefined;

    return (
        <div className="flex flex-col items-center justify-between gap-4 border-b border-outline-variant bg-surface-container-lowest px-6 py-4 md:flex-row">
            {hasPerPage ? (
                <div className="flex items-center gap-2 text-sm text-on-surface-variant">
                    <span>Tampilkan</span>
                    <select
                        value={perPage}
                        onChange={(event: ChangeEvent<HTMLSelectElement>) =>
                            onPerPageChange(Number(event.target.value))
                        }
                        className="h-9 rounded-lg border-outline-variant bg-surface-container-low px-2 text-sm font-medium focus:border-primary focus:ring-primary"
                    >
                        {PER_PAGE_OPTIONS.map((option) => (
                            <option key={option} value={option}>
                                {option}
                            </option>
                        ))}
                    </select>
                    <span>data</span>
                </div>
            ) : (
                <div />
            )}

            <div className="flex w-full items-center gap-3 md:w-auto">
                {children}
                {hasSearch && (
                    <div className="relative w-full md:w-64">
                        <MaterialIcon
                            name="search"
                            className="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant"
                            style={{ fontSize: "20px" }}
                        />
                        <input
                            type="text"
                            value={search}
                            onChange={(event) =>
                                onSearchChange(event.target.value)
                            }
                            placeholder={searchPlaceholder}
                            className="h-9 w-full rounded-lg border-outline-variant bg-surface-container-low pl-10 pr-4 text-sm focus:border-primary focus:ring-primary"
                        />
                    </div>
                )}
            </div>
        </div>
    );
}
