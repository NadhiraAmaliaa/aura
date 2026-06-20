import Pagination from "@/Components/Pagination";
import { PaginationLink } from "@/types";

export default function TableFooter({
    from,
    to,
    total,
    links,
}: {
    from: number | null;
    to: number | null;
    total: number;
    links: PaginationLink[];
}) {
    return (
        <div className="flex flex-col items-center justify-between gap-3 border-t border-outline-variant bg-surface-container-lowest px-6 py-4 sm:flex-row">
            <p className="text-sm text-on-surface-variant">
                Menampilkan{" "}
                <span className="font-bold text-on-surface">
                    {from ?? 0} - {to ?? 0}
                </span>{" "}
                dari <span className="font-bold text-on-surface">{total}</span>{" "}
                entitas
            </p>
            <Pagination links={links} />
        </div>
    );
}
