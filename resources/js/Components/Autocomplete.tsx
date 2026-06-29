import { useEffect, useMemo, useRef, useState } from "react";

export interface AutocompleteOption {
    id: number;
    name: string;
    level?: string | null;
}

interface AutocompleteProps {
    id?: string;
    /** The lookup endpoint URL (without query string). */
    url: string;
    /** Currently selected id, or empty/null when nothing is selected. */
    value: number | string | null;
    /** Text to display for the current selection (e.g. when editing). */
    displayValue?: string;
    /** Extra query parameters to send with every request. */
    params?: Record<string, string | number | null | undefined>;
    placeholder?: string;
    disabled?: boolean;
    className?: string;
    /** Called when the user picks an option, or clears the field (null). */
    onSelect: (option: AutocompleteOption | null) => void;
    /**
     * Optional quick-create handler. When provided, an "add" row is shown for
     * the typed text when it does not exactly match an existing option. It must
     * create the record and resolve with the new option (which is then
     * selected) or null on failure.
     */
    onCreate?: (name: string) => Promise<AutocompleteOption | null>;
    /** Label prefix for the quick-create row (e.g. "Tambah perguruan tinggi"). */
    createLabel?: string;
}

/**
 * A searchable select that loads its options from a server endpoint as the
 * user types. The endpoint must accept a `q` query parameter and return a JSON
 * array of `{ id, name, level? }` objects.
 */
export default function Autocomplete({
    id,
    url,
    value,
    displayValue = "",
    params = {},
    placeholder = "Ketik untuk mencari...",
    disabled = false,
    className = "",
    onSelect,
    onCreate,
    createLabel = "Tambah",
}: AutocompleteProps) {
    const [query, setQuery] = useState(displayValue);
    const [options, setOptions] = useState<AutocompleteOption[]>([]);
    const [open, setOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [creating, setCreating] = useState(false);
    const [highlight, setHighlight] = useState(0);

    const containerRef = useRef<HTMLDivElement>(null);
    const debounceRef = useRef<ReturnType<typeof setTimeout> | undefined>(undefined);

    // Keep the visible text in sync when the selection is set externally
    // (e.g. when an edit form loads, or the university filter resets it).
    useEffect(() => {
        setQuery(displayValue);
    }, [displayValue]);

    // Serialise extra params so the effect re-runs when they change.
    const paramsKey = useMemo(() => JSON.stringify(params), [params]);

    useEffect(() => {
        if (!open) {
            return;
        }

        if (debounceRef.current) {
            clearTimeout(debounceRef.current);
        }

        debounceRef.current = setTimeout(() => {
            const search = new URLSearchParams();
            search.set("q", query);

            Object.entries(params).forEach(([key, val]) => {
                if (val !== null && val !== undefined && val !== "") {
                    search.set(key, String(val));
                }
            });

            setLoading(true);

            fetch(`${url}?${search.toString()}`, {
                headers: { Accept: "application/json" },
                credentials: "same-origin",
            })
                .then((response) => (response.ok ? response.json() : []))
                .then((data: AutocompleteOption[]) => {
                    setOptions(Array.isArray(data) ? data : []);
                    setHighlight(0);
                })
                .catch(() => setOptions([]))
                .finally(() => setLoading(false));
        }, 250);

        return () => {
            if (debounceRef.current) {
                clearTimeout(debounceRef.current);
            }
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [query, open, url, paramsKey]);

    // Close the dropdown when clicking outside of it.
    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (
                containerRef.current &&
                !containerRef.current.contains(event.target as Node)
            ) {
                setOpen(false);
            }
        };

        document.addEventListener("mousedown", handleClickOutside);

        return () =>
            document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    const choose = (option: AutocompleteOption) => {
        setQuery(option.name);
        setOpen(false);
        onSelect(option);
    };

    const trimmedQuery = query.trim();
    const hasExactMatch = options.some(
        (option) => option.name.toLowerCase() === trimmedQuery.toLowerCase(),
    );
    const canCreate =
        Boolean(onCreate) && trimmedQuery !== "" && !hasExactMatch && !loading;

    const handleCreate = async () => {
        if (!onCreate || trimmedQuery === "" || creating) {
            return;
        }

        setCreating(true);

        try {
            const created = await onCreate(trimmedQuery);

            if (created) {
                choose(created);
            }
        } finally {
            setCreating(false);
        }
    };

    const handleChange = (text: string) => {
        setQuery(text);
        setOpen(true);

        // Typing invalidates a previous selection until a new one is chosen.
        if (value) {
            onSelect(null);
        }
    };

    const handleKeyDown = (event: React.KeyboardEvent<HTMLInputElement>) => {
        if (!open) {
            return;
        }

        if (event.key === "ArrowDown") {
            event.preventDefault();
            setHighlight((index) => Math.min(index + 1, options.length - 1));
        } else if (event.key === "ArrowUp") {
            event.preventDefault();
            setHighlight((index) => Math.max(index - 1, 0));
        } else if (event.key === "Enter") {
            if (options[highlight]) {
                event.preventDefault();
                choose(options[highlight]);
            }
        } else if (event.key === "Escape") {
            setOpen(false);
        }
    };

    return (
        <div ref={containerRef} className="relative">
            <input
                id={id}
                type="text"
                role="combobox"
                aria-expanded={open}
                aria-autocomplete="list"
                autoComplete="off"
                value={query}
                disabled={disabled}
                placeholder={placeholder}
                onChange={(e) => handleChange(e.target.value)}
                onFocus={() => setOpen(true)}
                onKeyDown={handleKeyDown}
                className={
                    "w-full rounded-md border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 disabled:cursor-not-allowed disabled:bg-gray-100 " +
                    className
                }
            />

            {open && (
                <ul className="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-md border border-gray-200 bg-white py-1 text-sm shadow-lg">
                    {loading ? (
                        <li className="px-3 py-2 text-gray-500">Memuat...</li>
                    ) : options.length === 0 ? (
                        <li className="px-3 py-2 text-gray-500">
                            {trimmedQuery === ""
                                ? "Ketik untuk mencari..."
                                : "Tidak ada hasil."}
                        </li>
                    ) : (
                        options.map((option, index) => (
                            <li key={option.id}>
                                <button
                                    type="button"
                                    onMouseDown={(e) => {
                                        // Prevent the input blur from closing
                                        // the list before the click registers.
                                        e.preventDefault();
                                        choose(option);
                                    }}
                                    onMouseEnter={() => setHighlight(index)}
                                    className={
                                        "flex w-full items-center justify-between px-3 py-2 text-left " +
                                        (index === highlight
                                            ? "bg-green-50 text-green-800"
                                            : "text-gray-700 hover:bg-gray-50")
                                    }
                                >
                                    <span>{option.name}</span>
                                    {option.level && (
                                        <span className="ml-2 shrink-0 text-xs text-gray-400">
                                            {option.level}
                                        </span>
                                    )}
                                </button>
                            </li>
                        ))
                    )}

                    {canCreate && (
                        <li className="border-t border-gray-100">
                            <button
                                type="button"
                                onMouseDown={(e) => {
                                    e.preventDefault();
                                    handleCreate();
                                }}
                                disabled={creating}
                                className="flex w-full items-center gap-2 px-3 py-2 text-left text-green-700 hover:bg-green-50 disabled:cursor-wait disabled:text-gray-400"
                            >
                                <span className="text-base leading-none">
                                    +
                                </span>
                                <span>
                                    {creating
                                        ? "Menyimpan..."
                                        : `${createLabel} "${trimmedQuery}"`}
                                </span>
                            </button>
                        </li>
                    )}
                </ul>
            )}
        </div>
    );
}
