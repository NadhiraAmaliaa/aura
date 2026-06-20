import MaterialIcon from "@/Components/MaterialIcon";
import { Link } from "@inertiajs/react";

const TONE_CLASS = {
    green: "bg-[#28a745] hover:brightness-95",
    blue: "bg-primary hover:brightness-110",
};

interface BaseProps {
    label: string;
    icon?: string;
    tone?: keyof typeof TONE_CLASS;
    className?: string;
}

export default function ActionButton({
    label,
    icon = "add_circle",
    tone = "green",
    href,
    onClick,
    type = "button",
    disabled = false,
    className = "",
}: BaseProps & {
    href?: string;
    onClick?: () => void;
    type?: "button" | "submit";
    disabled?: boolean;
}) {
    const classes =
        "flex h-11 items-center justify-center gap-2 rounded-lg px-6 font-bold text-white shadow-sm transition-all active:scale-95 disabled:opacity-60 " +
        TONE_CLASS[tone] +
        (className ? ` ${className}` : "");

    const content = (
        <>
            <MaterialIcon name={icon} style={{ fontSize: "18px" }} />
            {label}
        </>
    );

    if (href) {
        return (
            <Link href={href} className={classes}>
                {content}
            </Link>
        );
    }

    return (
        <button
            type={type}
            onClick={onClick}
            disabled={disabled}
            className={classes}
        >
            {content}
        </button>
    );
}
