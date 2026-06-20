import { ReactNode } from "react";

export default function TableCard({ children }: { children: ReactNode }) {
    return (
        <section className="overflow-hidden rounded-xl border border-outline-variant bg-white shadow-sm">
            {children}
        </section>
    );
}
