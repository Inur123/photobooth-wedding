/**
 * Polyfills for older browsers and mobile WebViews (iOS 15 / Safari 15, older Androids)
 * Ensures standard APIs (Object.hasOwn, Array.at, String.at, crypto.randomUUID, requestIdleCallback)
 * are always available before application mount.
 */

// 1. Object.hasOwn (Safari < 15.4)
if (typeof Object.hasOwn !== 'function') {
    Object.hasOwn = function (obj: object, prop: PropertyKey): boolean {
        return Object.prototype.hasOwnProperty.call(obj, prop);
    };
}

// 2. Array.prototype.at (Safari < 15.4)
if (!Array.prototype.at) {
    Array.prototype.at = function (n: number) {
        let index = Math.trunc(n) || 0;
        if (index < 0) {
            index += this.length;
        }
        if (index < 0 || index >= this.length) {
            return undefined;
        }
        return this[index];
    };
}

// 3. String.prototype.at (Safari < 15.4)
if (!String.prototype.at) {
    String.prototype.at = function (n: number) {
        let index = Math.trunc(n) || 0;
        if (index < 0) {
            index += this.length;
        }
        if (index < 0 || index >= this.length) {
            return undefined;
        }
        return this.charAt(index);
    };
}

// 4. crypto.randomUUID (Safari < 15.4)
if (typeof crypto !== 'undefined' && typeof crypto.randomUUID !== 'function') {
    crypto.randomUUID =
        function (): `${string}-${string}-${string}-${string}-${string}` {
            return '10000000-1000-4000-8000-100000000000'.replace(
                /[018]/g,
                (c: string) => {
                    const n = Number(c);
                    return (
                        n ^
                        (crypto.getRandomValues(new Uint8Array(1))[0] &
                            (15 >> (n / 4)))
                    ).toString(16);
                },
            ) as `${string}-${string}-${string}-${string}-${string}`;
        };
}

// 5. window.requestIdleCallback & cancelIdleCallback (Safari < 16.4)
if (typeof window !== 'undefined') {
    if (!window.requestIdleCallback) {
        window.requestIdleCallback = function (
            cb: IdleRequestCallback,
        ): number {
            const start = Date.now();
            return window.setTimeout(() => {
                cb({
                    didTimeout: false,
                    timeRemaining: () => Math.max(0, 50 - (Date.now() - start)),
                });
            }, 1);
        };
    }

    if (!window.cancelIdleCallback) {
        window.cancelIdleCallback = function (id: number): void {
            clearTimeout(id);
        };
    }
}
