import { useCallback, useState } from 'react';

interface Coordinates {
    latitude: number | null;
    longitude: number | null;
}

/**
 * Capture the browser's current geolocation. Resolves with null coordinates
 * when permission is denied or the API is unavailable, so check-in/check-out
 * can still proceed (location is optional, geofencing is not yet enforced).
 */
export function useGeolocation() {
    const [locating, setLocating] = useState(false);

    const capture = useCallback((): Promise<Coordinates> => {
        return new Promise((resolve) => {
            if (!('geolocation' in navigator)) {
                resolve({ latitude: null, longitude: null });
                return;
            }

            setLocating(true);

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    setLocating(false);
                    resolve({
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                    });
                },
                () => {
                    setLocating(false);
                    resolve({ latitude: null, longitude: null });
                },
                { enableHighAccuracy: true, timeout: 10000 },
            );
        });
    }, []);

    return { capture, locating };
}
