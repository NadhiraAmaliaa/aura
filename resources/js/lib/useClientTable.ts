import { useMemo, useState } from "react";

/**
 * Client-side table state: global search across the full dataset first, then
 * pagination over the filtered result.
 *
 * The page receives every row from the server, so the search always spans the
 * complete dataset instead of just the currently visible page. This mirrors the
 * behaviour of the Reporting Absensi table.
 */
export function useClientTable<T>(
    rows: T[],
    searchableText: (row: T) => string,
    initialPerPage = 10,
) {
    const [search, setSearch] = useState("");
    const [perPage, setPerPage] = useState(initialPerPage);
    const [page, setPage] = useState(1);

    // 1. Search across all rows.
    const filtered = useMemo(() => {
        const term = search.trim().toLowerCase();

        if (!term) {
            return rows;
        }

        return rows.filter((row) =>
            searchableText(row).toLowerCase().includes(term),
        );
    }, [rows, search, searchableText]);

    // 2. Paginate the filtered rows.
    const total = filtered.length;
    const totalPages = Math.max(1, Math.ceil(total / perPage));
    const safePage = Math.min(page, totalPages);
    const from = total === 0 ? 0 : (safePage - 1) * perPage + 1;
    const to = Math.min(safePage * perPage, total);

    const pagedRows = useMemo(
        () => filtered.slice(from - 1, to),
        [filtered, from, to],
    );

    // Any change to search or page size resets back to the first page.
    const onSearchChange = (value: string) => {
        setSearch(value);
        setPage(1);
    };

    const onPerPageChange = (value: number) => {
        setPerPage(value);
        setPage(1);
    };

    return {
        search,
        onSearchChange,
        perPage,
        onPerPageChange,
        page: safePage,
        setPage,
        rows: pagedRows,
        from,
        to,
        total,
        totalPages,
    };
}
