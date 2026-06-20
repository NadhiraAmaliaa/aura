import MaterialIcon from "@/Components/MaterialIcon";
import { Link } from "@inertiajs/react";
import { ReactNode } from "react";

type Tone = "edit" | "delete" | "view";

const TONE_CLASS: Record<Tone, string> = {
    edit: "bg-tertiary/10 text-tertiary hover:bg-tertiary hover:text-white",
    delete: "bg-error/10 text-error hover:bg-error hover:text-white",
    view: "bg-primary/10 text-primary hover:bg-primary hover:text-white",
};

export function IconAction({
    icon,
    label,
    tone = "edit",
    href,
    onClick,
    disabled = false,
}: {
    icon: string;
    label: string;
    tone?: Tone;
    href?: string;
    onClick?: () => void;
    disabled?: boolean;
}) {
    const className =
        "rounded-lg p-1.5 transition-all disabled:cursor-not-allowed disabled:bg-surface-container disabled:text-on-surface-variant/40 disabled:hover:bg-surface-container disabled:hover:text-on-surface-variant/40 " +
        TONE_CLASS[tone];

    const content = <MaterialIcon name={icon} style={{ fontSize: "18px" }} />;

    if (href && !disabled) {
        return (
            <Link href={href} aria-label={label} className={className}>
                {content}
            </Link>
        );
    }

    return (
        <button
            type="button"
            aria-label={label}
            onClick={onClick}
            disabled={disabled}
            className={className}
        >
            {content}
        </button>
    );
}

export default function RowActions({ children }: { children: ReactNode }) {
    return (
        <div className="flex justify-center gap-2 opacity-60 transition-opacity group-hover:opacity-100">
            {children}
        </div>
    );
}
