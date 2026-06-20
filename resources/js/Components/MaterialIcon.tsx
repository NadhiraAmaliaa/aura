import { CSSProperties } from "react";

export default function MaterialIcon({
    name,
    className,
    filled = false,
    style,
}: {
    name: string;
    className?: string;
    filled?: boolean;
    style?: CSSProperties;
}) {
    return (
        <span
            aria-hidden="true"
            className={"material-symbols-outlined" + (className ? ` ${className}` : "")}
            style={{
                fontVariationSettings: `'FILL' ${filled ? 1 : 0}`,
                ...style,
            }}
        >
            {name}
        </span>
    );
}
