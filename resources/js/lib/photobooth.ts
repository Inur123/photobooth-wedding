import { eventDate, eventName, eventTheme } from '@/lib/eventTheme';
import type { GuestEvent } from '@/types/event';

export type PhotoSource = HTMLCanvasElement | ImageBitmap | HTMLVideoElement;
type Slot = { x: number; y: number; width: number; height: number };

export function photoboothLayout(count: number) {
    if (count === 1) {
        const margin = 64;
        const top = 160;
        const bottom = 260;
        return {
            width: 1080,
            height: 1440,
            slots: [
                {
                    x: margin,
                    y: top,
                    width: 1080 - margin * 2,
                    height: 1440 - top - bottom,
                },
            ],
        };
    }
    const width = count === 4 ? 1500 : 900;
    const height = count === 4 ? 1900 : count === 3 ? 2400 : 1800;
    const margin = count === 4 ? 90 : 64;
    const gap = count === 4 ? 40 : 32;
    const top = 160;
    const bottom = 260;
    const columns = count === 4 ? 2 : 1;
    const rows = Math.ceil(count / columns);
    const slotWidth = (width - margin * 2 - gap * (columns - 1)) / columns;
    const slotHeight = (height - top - bottom - gap * (rows - 1)) / rows;
    return {
        width,
        height,
        slots: Array.from({ length: count }, (_, index) => ({
            x: margin + (index % columns) * (slotWidth + gap),
            y: top + Math.floor(index / columns) * (slotHeight + gap),
            width: slotWidth,
            height: slotHeight,
        })),
    };
}

function drawPhoto(
    context: CanvasRenderingContext2D,
    source: PhotoSource,
    slot: Slot,
    mirror: boolean,
) {
    const sourceWidth =
        'videoWidth' in source ? source.videoWidth : source.width;
    const sourceHeight =
        'videoHeight' in source ? source.videoHeight : source.height;
    if (!sourceWidth || !sourceHeight) return;
    const scale = Math.max(
        slot.width / sourceWidth,
        slot.height / sourceHeight,
    );
    const width = slot.width / scale;
    const height = slot.height / scale;
    context.save();
    context.translate(slot.x + (mirror ? slot.width : 0), slot.y);
    if (mirror) context.scale(-1, 1);
    context.drawImage(
        source,
        (sourceWidth - width) / 2,
        (sourceHeight - height) / 2,
        width,
        height,
        0,
        0,
        slot.width,
        slot.height,
    );
    context.restore();
}

export function drawPhotobooth(
    canvas: HTMLCanvasElement,
    event: GuestEvent,
    count: number,
    shots: PhotoSource[],
    options: {
        live?: HTMLVideoElement | null;
        mirrorLive?: boolean;
        scale?: number;
    } = {},
) {
    const layout = photoboothLayout(count);
    const scale = options.scale ?? 1;
    const width = Math.round(layout.width * scale);
    const height = Math.round(layout.height * scale);
    if (canvas.width !== width || canvas.height !== height) {
        canvas.width = width;
        canvas.height = height;
    }
    const context = canvas.getContext('2d');
    if (!context) throw new Error('Foto belum bisa diproses. Coba kembali.');
    context.setTransform(scale, 0, 0, scale, 0, 0);
    const theme = eventTheme(event);
    const color = (name: string) =>
        String(theme[`--event-${name}` as keyof typeof theme]);
    context.fillStyle = color('surface');
    context.fillRect(0, 0, layout.width, layout.height);

    context.strokeStyle = color('action');
    context.lineWidth = 2;
    context.strokeRect(25, 25, layout.width - 50, layout.height - 50);
    context.fillStyle = color('action');
    context.font = '600 27px "Plus Jakarta Sans", sans-serif';
    context.textAlign = 'left';
    context.fillText('titip.moment', layout.slots[0].x, 93);
    context.font = '400 20px "Plus Jakarta Sans", sans-serif';
    context.textAlign = 'right';
    context.fillStyle = color('muted');
    context.fillText(`${count} kenangan`, layout.width - layout.slots[0].x, 93);

    layout.slots.forEach((slot, index) => {
        const shot = shots[index];
        const live = index === shots.length ? options.live : null;
        context.fillStyle = color('soft');
        context.fillRect(slot.x, slot.y, slot.width, slot.height);
        if (shot || live?.videoWidth) {
            drawPhoto(
                context,
                shot ?? live!,
                slot,
                !shot && (options.mirrorLive ?? true),
            );
        } else {
            context.fillStyle = color('muted');
            context.textAlign = 'center';
            context.font = '400 25px "Plus Jakarta Sans", sans-serif';
            context.fillText(
                `Pose ${index + 1}`,
                slot.x + slot.width / 2,
                slot.y + slot.height / 2,
            );
        }
        context.strokeStyle = color('border');
        context.lineWidth = 2;
        context.strokeRect(slot.x, slot.y, slot.width, slot.height);
    });

    const center = layout.width / 2;
    const footerTop = layout.height - 260;
    context.strokeStyle = color('action');
    context.lineWidth = 2;
    context.beginPath();
    context.moveTo(center - 55, footerTop + 48);
    context.lineTo(center + 55, footerTop + 48);
    context.stroke();
    context.fillStyle = color('ink');
    context.textAlign = 'center';
    let fontSize = count === 4 ? 76 : 60;
    do {
        context.font = `500 ${fontSize}px "Playfair Display", serif`;
        if (context.measureText(eventName(event)).width <= layout.width - 140)
            break;
        fontSize -= 2;
    } while (fontSize > 28);
    context.fillText(
        eventName(event),
        center,
        footerTop + 135,
        layout.width - 140,
    );
    context.fillStyle = color('muted');
    context.font = '400 23px "Plus Jakarta Sans", sans-serif';
    context.fillText(eventDate(event.event_date), center, footerTop + 190);
}
