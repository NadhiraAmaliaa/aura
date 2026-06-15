import { Link } from '@inertiajs/react';
import { PaginationLink } from '@/types';

export default function Pagination({ links }: { links: PaginationLink[] }) {
    if (links.length <= 3) {
        return null;
    }

    return (
        <nav className="flex flex-wrap items-center gap-1">
            {links.map((link, index) =>
                link.url === null ? (
                    <span
                        key={index}
                        className="cursor-default rounded-md px-3 py-2 text-sm text-gray-400"
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ) : (
                    <Link
                        key={index}
                        href={link.url}
                        className={`rounded-md px-3 py-2 text-sm transition ${
                            link.active
                                ? 'bg-green-700 text-white'
                                : 'text-gray-600 hover:bg-gray-100'
                        }`}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                        preserveScroll
                    />
                ),
            )}
        </nav>
    );
}
