import { describe, it, expect } from 'vitest';
import {
    applyThreshold,
    findLargestConnectedComponent,
    featherAlpha,
    calculateTransparencyRatio,
    compositeCutout
} from '../../resources/js/idcard-mask.js';

describe('ID Card Masking Utilities', () => {
    describe('applyThreshold', () => {
        it('thresholds confidence scores correctly with default 0.5 cutoff', () => {
            const input = new Float32Array([0.1, 0.49, 0.5, 0.8, 0.99]);
            const output = applyThreshold(input);
            expect(Array.from(output)).toEqual([0, 0, 1, 1, 1]);
        });

        it('supports custom threshold', () => {
            const input = new Float32Array([0.2, 0.6, 0.75, 0.9]);
            const output = applyThreshold(input, 0.7);
            expect(Array.from(output)).toEqual([0, 0, 1, 1]);
        });
    });

    describe('findLargestConnectedComponent', () => {
        it('keeps the largest foreground cluster and removes small isolated noise islands', () => {
            const w = 10;
            const h = 10;
            const mask = new Uint8Array(w * h);

            // Small 2x2 noise island in top-left (indices: (0,0), (0,1), (1,0), (1,1))
            mask[0 * w + 0] = 1;
            mask[0 * w + 1] = 1;
            mask[1 * w + 0] = 1;
            mask[1 * w + 1] = 1;

            // Main large subject in center (4x4 block)
            for (let y = 4; y <= 7; y++) {
                for (let x = 4; x <= 7; x++) {
                    mask[y * w + x] = 1;
                }
            }

            const cleaned = findLargestConnectedComponent(mask, w, h);

            // The 2x2 noise island should be removed (set to 0)
            expect(cleaned[0 * w + 0]).toBe(0);
            expect(cleaned[0 * w + 1]).toBe(0);
            expect(cleaned[1 * w + 0]).toBe(0);
            expect(cleaned[1 * w + 1]).toBe(0);

            // The main 4x4 cluster should be preserved (set to 1)
            for (let y = 4; y <= 7; y++) {
                for (let x = 4; x <= 7; x++) {
                    expect(cleaned[y * w + x]).toBe(1);
                }
            }
        });
    });

    describe('featherAlpha', () => {
        it('produces smooth soft transition along boundary while keeping core opaque', () => {
            const w = 20;
            const h = 20;
            const mask = new Uint8Array(w * h);

            // 10x10 square in center from (5,5) to (14,14)
            for (let y = 5; y <= 14; y++) {
                for (let x = 5; x <= 14; x++) {
                    mask[y * w + x] = 1;
                }
            }

            const alpha = featherAlpha(mask, w, h, 2);

            // Deep background corner (0,0) should be fully transparent (0)
            expect(alpha[0 * w + 0]).toBe(0);

            // Deep foreground center (10,10) should be fully opaque (255)
            expect(alpha[10 * w + 10]).toBe(255);

            // Boundary pixel (e.g. at x=5, y=5) should have intermediate alpha
            const edgeAlpha = alpha[5 * w + 5];
            expect(edgeAlpha).toBeGreaterThan(0);
            expect(edgeAlpha).toBeLessThan(255);
        });
    });

    describe('calculateTransparencyRatio', () => {
        it('calculates the proportion of transparent pixels accurately', () => {
            // 100 pixels: 60 transparent (alpha <= 15) and 40 opaque (alpha > 15)
            const alpha = new Uint8ClampedArray(100);
            for (let i = 0; i < 60; i++) {
                alpha[i] = 0; // transparent
            }
            for (let i = 60; i < 100; i++) {
                alpha[i] = 255; // opaque
            }

            const ratio = calculateTransparencyRatio(alpha, 15);
            expect(ratio).toBeCloseTo(0.60, 4);
        });
    });

    describe('compositeCutout', () => {
        it('applies alpha mask to RGBA buffer without altering RGB color values', () => {
            const w = 2;
            const h = 2;
            const buffer = new Uint8ClampedArray(w * h * 4);

            // Fill with solid red
            for (let i = 0; i < w * h; i++) {
                buffer[i * 4 + 0] = 255; // R
                buffer[i * 4 + 1] = 0;   // G
                buffer[i * 4 + 2] = 0;   // B
                buffer[i * 4 + 3] = 255; // A
            }

            const fakeImageData = {
                data: buffer,
                width: w,
                height: h
            };

            const alphaMask = new Uint8ClampedArray([0, 100, 200, 255]);
            const result = compositeCutout(fakeImageData, alphaMask);

            expect(result.data[0 * 4 + 3]).toBe(0);
            expect(result.data[1 * 4 + 3]).toBe(100);
            expect(result.data[2 * 4 + 3]).toBe(200);
            expect(result.data[3 * 4 + 3]).toBe(255);

            // Red channel still 255
            expect(result.data[0 * 4 + 0]).toBe(255);
            expect(result.data[1 * 4 + 0]).toBe(255);
        });
    });
});
