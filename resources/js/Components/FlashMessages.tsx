import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { PageProps } from '@/types';

export default function FlashMessages() {
    const { flash } = usePage<PageProps>().props;
    const [visible, setVisible] = useState(true);

    const message = flash.success ?? flash.error ?? flash.status ?? null;
    const isError = Boolean(flash.error);

    useEffect(() => {
        setVisible(true);

        if (!message) {
            return;
        }

        const timer = setTimeout(() => setVisible(false), 5000);

        return () => clearTimeout(timer);
    }, [message]);

    if (!message || !visible) {
        return null;
    }

    return (
        <div
            className={`mb-4 rounded-md border px-4 py-3 text-sm ${
                isError
                    ? 'border-red-200 bg-red-50 text-red-800'
                    : 'border-green-200 bg-green-50 text-green-800'
            }`}
            role="alert"
        >
            {message}
        </div>
    );
}
