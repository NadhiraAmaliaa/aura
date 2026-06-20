import MaterialIcon from '@/Components/MaterialIcon';
import { PaginationLink } from '@/types';
import { Link } from '@inertiajs/react';

export default function Pagination({ links }: { links: PaginationLink[] }) {
    if (links.length <= 3) {
        return null;
    }

    const lastIndex = links.length - 1;

    const renderLabel = (link: PaginationLink, index: number) => {
        if (index === 0) {
            return <MaterialIcon name="chevron_left" />;
        }

        if (index === lastIndex) {
            return <MaterialIcon name="chevron_right" />;
        }

        return <span dangerouslySetInnerHTML={{ __html: link.label }} />;
    };

    return (
        <nav className="flex items-center gap-1">
            {links.map((link, index) => {
                const baseClass =
                    'flex h-10 w-10 items-center justify-center rounded text-sm';

                if (link.url === null) {
                    return (
                        <span
                            key={index}
                            className={`${baseClass} cursor-default text-on-surface-variant opacity-30`}
                        >
                            {renderLabel(link, index)}
                        </span>
                    );
                }

                return (
                    <Link
                        key={index}
                        href={link.url}
                        preserveScroll
                        className={`${baseClass} transition-colors ${
                            link.active
                                ? 'bg-primary font-bold text-white'
                                : 'font-medium text-on-surface-variant hover:bg-surface-container-low'
                        }`}
                    >
                        {renderLabel(link, index)}
                    </Link>
                );
            })}
        </nav>
    );
}
