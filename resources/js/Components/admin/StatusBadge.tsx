import { ReactNode } from "react";

type StatusTone = "success" | "neutral" | "info" | "warning" | "danger";

const TONE_CLASS: Record<StatusTone, string> = {
    success: "bg-green-100 text-green-800",
    neutral: "bg-surface-container text-on-surface-variant",
    info: "bg-surface-container text-tertiary",
    warning: "bg-amber-100 text-amber-800",
    danger: "bg-red-100 text-red-700",
};

export default function StatusBadge({
    tone = "neutral",
    children,
}: {
    tone?: StatusTone;
    children: ReactNode;
}) {
    return (
        <span
            className={
                "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium " +
                TONE_CLASS[tone]
            }
        >
            {children}
        </span>
    );
}
