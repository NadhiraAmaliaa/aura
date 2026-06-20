import { TextareaHTMLAttributes } from 'react';

export default function TextareaInput({
    className = '',
    ...props
}: TextareaHTMLAttributes<HTMLTextAreaElement>) {
    return (
        <textarea
            {...props}
            className={
                'rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary ' +
                className
            }
        />
    );
}
