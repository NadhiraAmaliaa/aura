import { SelectHTMLAttributes } from 'react';

export default function SelectInput({
    className = '',
    children,
    ...props
}: SelectHTMLAttributes<HTMLSelectElement>) {
    return (
        <select
            {...props}
            className={
                'rounded-md border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 ' +
                className
            }
        >
            {children}
        </select>
    );
}
