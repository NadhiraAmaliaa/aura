import { ImgHTMLAttributes } from 'react';

export default function ApplicationLogo(props: ImgHTMLAttributes<HTMLImageElement>) {
    return (
        <div
            {...props}
            className={
                'flex h-9 w-9 items-center justify-center rounded-lg bg-green-700 font-bold text-white ' +
                (props.className ?? '')
            }
        >
            P
        </div>
    );
}
