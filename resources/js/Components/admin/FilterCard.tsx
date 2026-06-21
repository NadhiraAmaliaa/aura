import { FormEventHandler, ReactNode } from "react";

export const filterControlClass =
    "h-10 w-full rounded-lg border-outline-variant bg-surface-container-low px-3 text-sm focus:border-primary focus:ring-primary";

export default function FilterCard({
    children,
    actions,
    onSubmit,
}: {
    children: ReactNode;
    actions?: ReactNode;
    onSubmit?: FormEventHandler;
}) {
    return (
        <form
            onSubmit={onSubmit}
            className="mb-6 rounded-xl border border-outline-variant bg-white p-4 shadow-sm"
        >
            <div className="flex flex-col gap-4 lg:flex-row lg:items-end">
                <div className="grid flex-1 grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    {children}
                </div>
                {actions && (
                    <div className="flex items-center justify-end gap-3">
                        {actions}
                    </div>
                )}
            </div>
        </form>
    );
}

export function FilterField({
    label,
    htmlFor,
    children,
    className,
}: {
    label: string;
    htmlFor?: string;
    children: ReactNode;
    className?: string;
}) {
    return (
        <div className={className}>
            <label
                htmlFor={htmlFor}
                className="mb-1 block text-xs font-medium text-on-surface-variant"
            >
                {label}
            </label>
            {children}
        </div>
    );
}
