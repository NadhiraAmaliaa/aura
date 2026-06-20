import MaterialIcon from "@/Components/MaterialIcon";
import { ReactNode } from "react";

export interface Column<T> {
    header: ReactNode;
    cell: (row: T, index: number) => ReactNode;
    align?: "left" | "center" | "right";
    className?: string;
    headClassName?: string;
}

function alignClass(align?: "left" | "center" | "right"): string {
    if (align === "center") return "text-center";
    if (align === "right") return "text-right";
    return "text-left";
}

export default function DataTable<T>({
    columns,
    rows,
    getRowKey,
    emptyText = "Belum ada data.",
    emptyIcon = "inbox",
}: {
    columns: Column<T>[];
    rows: T[];
    getRowKey: (row: T) => string | number;
    emptyText?: string;
    emptyIcon?: string;
}) {
    return (
        <div className="overflow-x-auto">
            <table className="w-full border-collapse">
                <thead>
                    <tr className="bg-[#eab308] text-xs font-semibold uppercase tracking-wider text-white">
                        {columns.map((col, index) => (
                            <th
                                key={index}
                                className={
                                    "whitespace-nowrap px-6 py-2.5 " +
                                    alignClass(col.align) +
                                    (index < columns.length - 1
                                        ? " border-r border-white/20"
                                        : "") +
                                    (col.headClassName
                                        ? ` ${col.headClassName}`
                                        : "")
                                }
                            >
                                {col.header}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody className="divide-y divide-outline-variant">
                    {rows.length === 0 ? (
                        <tr>
                            <td colSpan={columns.length} className="px-6 py-16">
                                <div className="flex flex-col items-center justify-center gap-2 text-center">
                                    <MaterialIcon
                                        name={emptyIcon}
                                        className="text-on-surface-variant/40"
                                        style={{ fontSize: "32px" }}
                                    />
                                    <p className="text-sm font-medium text-on-surface-variant">
                                        {emptyText}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    ) : (
                        rows.map((row, rowIndex) => (
                            <tr
                                key={getRowKey(row)}
                                className={
                                    "group transition-colors hover:bg-surface-container-lowest " +
                                    (rowIndex % 2 === 1
                                        ? "bg-surface-container-low/30"
                                        : "")
                                }
                            >
                                {columns.map((col, colIndex) => (
                                    <td
                                        key={colIndex}
                                        className={
                                            "px-6 py-3 text-sm text-on-surface " +
                                            alignClass(col.align) +
                                            (colIndex < columns.length - 1
                                                ? " border-r border-outline-variant"
                                                : "") +
                                            (col.className
                                                ? ` ${col.className}`
                                                : "")
                                        }
                                    >
                                        {col.cell(row, rowIndex)}
                                    </td>
                                ))}
                            </tr>
                        ))
                    )}
                </tbody>
            </table>
        </div>
    );
}
