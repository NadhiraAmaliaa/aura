import { AutocompleteOption } from "@/Components/Autocomplete";

/**
 * Read a cookie value by name, or null when it is not present.
 */
function getCookie(name: string): string | null {
    const match = document.cookie.match(
        new RegExp("(?:^|; )" + name.replace(/([.$?*|{}()[\]\\/+^])/g, "\\$1") + "=([^;]*)"),
    );

    return match ? decodeURIComponent(match[1]) : null;
}

/**
 * POST a JSON payload to a Laravel endpoint and return the parsed response.
 *
 * Sends the XSRF token from the cookie so the request passes CSRF protection.
 * On a validation error (422) the first message is surfaced via alert and null
 * is returned; null is also returned for any other failure.
 */
export async function postJson(
    url: string,
    body: Record<string, unknown>,
): Promise<AutocompleteOption | null> {
    try {
        const response = await fetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-XSRF-TOKEN": getCookie("XSRF-TOKEN") ?? "",
            },
            credentials: "same-origin",
            body: JSON.stringify(body),
        });

        if (response.ok) {
            return (await response.json()) as AutocompleteOption;
        }

        if (response.status === 422) {
            const data = await response.json();
            const errors = data?.errors as
                | Record<string, string[]>
                | undefined;
            const first = errors
                ? Object.values(errors)[0]?.[0]
                : data?.message;

            if (first) {
                window.alert(first);
            }
        }

        return null;
    } catch {
        return null;
    }
}
