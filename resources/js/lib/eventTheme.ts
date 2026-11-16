import type { CSSProperties } from 'vue';
import type { GuestEvent } from '@/types/event';

function rgb(hex: string): number[] {
    return [1, 3, 5].map((start) => parseInt(hex.slice(start, start + 2), 16));
}

function luminance(hex: string): number {
    return rgb(hex).reduce((sum, channel, index) => {
        const value = channel / 255;
        const linear =
            value <= 0.04045 ? value / 12.92 : ((value + 0.055) / 1.055) ** 2.4;
        return sum + linear * [0.2126, 0.7152, 0.0722][index];
    }, 0);
}

function contrast(a: string, b: string): number {
    const values = [luminance(a), luminance(b)].sort((x, y) => y - x);
    return (values[0] + 0.05) / (values[1] + 0.05);
}

function mix(a: string, b: string, weight: number): string {
    const target = rgb(b);
    return `#${rgb(a)
        .map((channel, index) =>
            Math.round(channel * (1 - weight) + target[index] * weight)
                .toString(16)
                .padStart(2, '0'),
        )
        .join('')}`;
}

export function eventTheme(event: GuestEvent): CSSProperties {
    const valid = (value: string, fallback: string) =>
        /^#[\da-f]{6}$/i.test(value) ? value : fallback;
    const background = valid(event.colors.neutral, '#f8f6f2');
    const isDeep = luminance(background) < 0.3;
    const fallbackInk = isDeep ? '#f8f6f2' : '#1d1c29';
    const primary = valid(event.colors.primary, fallbackInk);
    const ink = contrast(primary, background) >= 4.5 ? primary : fallbackInk;
    const accent = valid(event.colors.accent, '#ff5d6c');
    const surface = mix(background, '#ffffff', isDeep ? 0.045 : 0.68);
    const soft = mix(background, accent, 0.12);
    const surfaces = [background, surface, soft];
    let action = accent;
    for (
        let step = 0;
        step < 20 && surfaces.some((color) => contrast(action, color) < 4.5);
        step++
    ) {
        action = mix(action, isDeep ? '#ffffff' : '#000000', 0.08);
    }
    const onAction = contrast('#ffffff', action) >= 4.5 ? '#ffffff' : '#14131f';
    let muted = mix(ink, background, 0.24);
    for (
        let step = 0;
        step < 20 && surfaces.some((color) => contrast(muted, color) < 4.5);
        step++
    ) {
        muted = mix(muted, ink, 0.1);
    }
    const border = isDeep ? '#494650' : '#ded6cc';
    return {
        '--event-bg': background,
        '--event-ink': ink,
        '--event-accent': accent,
        '--event-action': action,
        '--event-on-action': onAction,
        '--event-surface': surface,
        '--event-soft': soft,
        '--event-muted': muted,
        '--event-border': border,
        '--background': background,
        '--foreground': ink,
        '--card': surface,
        '--card-foreground': ink,
        '--popover': surface,
        '--popover-foreground': ink,
        '--primary': action,
        '--primary-foreground': onAction,
        '--accent': soft,
        '--accent-foreground': ink,
        '--muted': mix(background, accent, 0.08),
        '--muted-foreground': muted,
        '--border': border,
        '--input': border,
        '--ring': action,
        colorScheme: 'light',
    };
}

export function eventName(event: GuestEvent): string {
    return [event.party_one_name, event.party_two_name]
        .filter(Boolean)
        .join(' & ');
}

export function eventDate(value: string | null): string {
    if (!value) return 'Hari istimewa kita';
    const date = /^\d{4}-\d{2}-\d{2}$/.test(value)
        ? new Date(`${value}T12:00:00+07:00`)
        : new Date(value);
    if (Number.isNaN(date.getTime())) return 'Hari istimewa kita';

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        timeZone: 'Asia/Jakarta',
    }).format(date);
}

export function initials(name: string): string {
    return (
        name
            .trim()
            .split(/\s+/)
            .slice(0, 2)
            .map((word) => word[0])
            .join('')
            .toUpperCase() || 'T'
    );
}
