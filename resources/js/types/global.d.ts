import { route as routeFn } from 'ziggy-js';

declare global {
    // The Ziggy `route()` helper is made available globally via the @routes Blade directive.
    const route: typeof routeFn;
}

export {};
