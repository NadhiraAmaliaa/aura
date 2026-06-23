import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from "@/Components/ui/alert-dialog";
import { buttonVariants } from "@/Components/ui/button";
import { cn } from "@/lib/utils";
import { router } from "@inertiajs/react";
import { ReactNode, useState } from "react";

type Method = "delete" | "patch" | "put" | "post";

/**
 * A generic confirmation dialog that submits a single request to the given
 * URL. Used for non-destructive state changes such as archiving and restoring,
 * where a hard "delete" dialog would be semantically wrong.
 */
export default function ConfirmActionDialog({
    open,
    onOpenChange,
    title,
    description,
    url,
    method = "patch",
    confirmLabel = "Lanjutkan",
    variant = "default",
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    title: string;
    description: ReactNode;
    url: string | null;
    method?: Method;
    confirmLabel?: string;
    variant?: "default" | "destructive";
}) {
    const [processing, setProcessing] = useState(false);

    const submit = () => {
        if (!url) {
            return;
        }

        router[method](
            url,
            {},
            {
                preserveScroll: true,
                onStart: () => setProcessing(true),
                onFinish: () => {
                    setProcessing(false);
                    onOpenChange(false);
                },
            },
        );
    };

    return (
        <AlertDialog open={open} onOpenChange={onOpenChange}>
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{title}</AlertDialogTitle>
                    <AlertDialogDescription>
                        {description}
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel disabled={processing}>
                        Batal
                    </AlertDialogCancel>
                    <AlertDialogAction
                        className={cn(buttonVariants({ variant }))}
                        disabled={processing}
                        onClick={(event) => {
                            event.preventDefault();
                            submit();
                        }}
                    >
                        {confirmLabel}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}
