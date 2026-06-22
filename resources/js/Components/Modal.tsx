import { PropsWithChildren, useEffect } from "react";

export default function Modal({
    children,
    show = false,
    maxWidth = "2xl",
    closeable = true,
    onClose = () => {},
}: PropsWithChildren<{
    show: boolean;
    maxWidth?: "sm" | "md" | "lg" | "xl" | "2xl";
    closeable?: boolean;
    onClose: () => void;
}>) {
    const close = () => {
        if (closeable) {
            onClose();
        }
    };

    useEffect(() => {
        const handleEscape = (e: KeyboardEvent) => {
            if (e.key === "Escape") {
                close();
            }
        };

        document.body.style.overflow = show ? "hidden" : "";
        document.addEventListener("keydown", handleEscape);

        return () => {
            document.removeEventListener("keydown", handleEscape);
            document.body.style.overflow = "";
        };
    });

    const maxWidthClass = {
        sm: "sm:max-w-sm",
        md: "sm:max-w-md",
        lg: "sm:max-w-lg",
        xl: "sm:max-w-xl",
        "2xl": "sm:max-w-2xl",
    }[maxWidth];

    if (!show) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-0">
            <div
                className="absolute inset-0 bg-gray-500/75"
                onClick={close}
            ></div>

            <div
                className={`relative z-10 w-full transform overflow-hidden rounded-lg bg-white shadow-xl transition-all sm:mx-auto ${maxWidthClass}`}
            >
                {children}
            </div>
        </div>
    );
}
