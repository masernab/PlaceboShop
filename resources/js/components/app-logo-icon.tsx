import type { SVGAttributes } from 'react';

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg {...props} viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M10 4 Q 11.4 12.1 19.5 13.5 Q 11.4 14.9 10 23 Q 8.6 14.9 0.5 13.5 Q 8.6 12.1 10 4 Z" />
            <path d="M19 0.5 Q 19.6 3.9 23 4.5 Q 19.6 5.1 19 8.5 Q 18.4 5.1 15 4.5 Q 18.4 3.9 19 0.5 Z" />
        </svg>
    );
}
