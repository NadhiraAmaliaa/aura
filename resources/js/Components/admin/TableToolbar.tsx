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
    perPage?: number | null;
    onPerPageChange?: (value: number | null) => void;
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
                        value={perPage ?? ""}
                        onChange={(event: ChangeEvent<HTMLSelectElement>) =>
                            onPerPageChange(
                                event.target.value === ""
                                    ? null
                                    : Number(event.target.value),
                            )
                        }
                        className="appearance-none h-9 rounded-lg border border-outline-variant bg-surface-container-low pl-3 pr-7 text-sm font-medium focus:border-primary focus:ring-primary bg-[url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%229ca3af%22 stroke-width=%222%22%3e%3cpolyline points=%226 9 12 15 18 9%22%3e%3c/polyline%3e%3c/svg%3e')] bg-no-repeat bg-right bg-[length:18px]"
                    >
                        <option value=""></option>
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
