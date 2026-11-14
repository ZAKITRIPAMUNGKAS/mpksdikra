import { describe, expect, it } from 'vitest';
import { createPresentationEvents, normalizeRagResponse } from './adapter.js';

describe('normalizeRagResponse', () => {
    it('keeps only valid citations and marks a response without sources as uncertain', () => {
        const message = normalizeRagResponse({
            message: 'Informasi kegiatan tersedia [1].',
            sources: [],
        }, { eventId: 9 });

        expect(message.content).toContain('Informasi kegiatan tersedia');
        expect(message.sources).toEqual([]);
        expect(message.confidence).toBe('low');
        expect(message.notice).toContain('Saya kurang yakin');
    });

    it('does not mark a conversational greeting without sources as uncertain', () => {
        const message = normalizeRagResponse({
            intent: 'chat',
            message: 'Hai! Selamat siang.',
            sources: [],
        }, { eventId: 9 });

        expect(message.confidence).toBe('grounded');
        expect(message.notice).toBeNull();
    });

    it('maps an existing source into the shared citation contract', () => {
        const message = normalizeRagResponse({
            message: 'Kuota masih tersedia [1].',
            sources: [{
                id: 1,
                title: 'Kapasitas kegiatan',
                badge: 'Database: events',
                snippet: 'Kuota 20, terdaftar 4.',
                score: 0.91,
            }],
        }, { eventId: 9 });

        expect(message.confidence).toBe('grounded');
        expect(message.sources[0]).toMatchObject({
            id: '1',
            title: 'Kapasitas kegiatan',
            page: null,
            score: 0.91,
        });
    });
});

describe('createPresentationEvents', () => {
    it('announces retrieval status in the agreed order before completion', () => {
        expect(createPresentationEvents({ content: 'Jawaban ringkas.' }, { reducedMotion: true })
            .map((event) => event.type === 'status' ? event.payload : event.type))
            .toEqual([
                'Mencari data kader…',
                'Membaca dokumen…',
                'Menyusun jawaban…',
                'token',
                'done',
            ]);
    });
});
