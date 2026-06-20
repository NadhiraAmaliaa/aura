import { usePage } from "@inertiajs/react";
import { useEffect, useRef } from "react";
import { toast } from "sonner";

import { PageProps } from "@/types";

/**
 * Headless component that surfaces Laravel flash messages as Sonner toasts.
 * Renders nothing; mount it once inside the authenticated layout.
 */
export default function FlashToaster() {
    const { flash } = usePage<PageProps>().props;
    const lastShown = useRef<string | null>(null);

    useEffect(() => {
        const success = flash.success ?? flash.status ?? null;
        const error = flash.error ?? null;
        const message = error ?? success;

        if (!message || message === lastShown.current) {
            return;
        }

        lastShown.current = message;

        if (error) {
            toast.error(error);
        } else {
            toast.success(success as string);
        }
    }, [flash]);

    return null;
}
