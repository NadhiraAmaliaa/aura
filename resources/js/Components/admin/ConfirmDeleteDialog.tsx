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

export default function ConfirmDeleteDialog({
    open,
    onOpenChange,
    title = "Hapus data ini?",
    description,
    deleteUrl,
    confirmLabel = "Hapus",
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    title?: string;
    description: ReactNode;
    deleteUrl: string | null;
    confirmLabel?: string;
}) {
    const [processing, setProcessing] = useState(false);

    const confirmDelete = () => {
        if (!deleteUrl) {
            return;
        }

        router.delete(deleteUrl, {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                onOpenChange(false);
            },
        });
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
                        className={cn(buttonVariants({ variant: "destructive" }))}
                        disabled={processing}
                        onClick={(event) => {
                            event.preventDefault();
                            confirmDelete();
                        }}
                    >
                        {confirmLabel}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}
