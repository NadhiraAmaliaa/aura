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
import { AttendanceLocation } from "@/types";
import { router } from "@inertiajs/react";
import { useState } from "react";

export default function DeleteLocationDialog({
    location,
    open,
    onOpenChange,
}: {
    location: AttendanceLocation | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const [processing, setProcessing] = useState(false);

    const confirmDelete = () => {
        if (!location) {
            return;
        }

        router.delete(
            route("admin.attendance-locations.destroy", location.id),
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
                    <AlertDialogTitle>Hapus lokasi ini?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Lokasi{" "}
                        <span className="font-semibold text-foreground">
                            {location?.name}
                        </span>{" "}
                        akan dihapus permanen dan tidak dapat dikembalikan.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel disabled={processing}>
                        Batal
                    </AlertDialogCancel>
                    <AlertDialogAction
                        className={cn(buttonVariants({ variant: "destructive" }))}
                        disabled={processing}
                        onClick={(e) => {
                            e.preventDefault();
                            confirmDelete();
                        }}
                    >
                        Hapus
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}
