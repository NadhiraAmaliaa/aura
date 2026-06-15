import { PropsWithChildren } from 'react';

export default function Badge({
    className = '',
    children,
}: PropsWithChildren<{ className?: string }>) {
    return (
        <span
            className={
                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' +
                className
            }
        >
            {children}
        </span>
    );
}
