import { ReactNode } from "react";

export default function PageHeader({
    title,
    children,
}: {
    title: ReactNode;
    children?: ReactNode;
}) {
    return (
        <>
            <h1 className="text-[28px] font-extrabold tracking-tight text-on-surface">
                {title}
            </h1>
            {children && (
                <div className="flex flex-wrap items-center gap-3">
                    {children}
                </div>
            )}
        </>
    );
}
