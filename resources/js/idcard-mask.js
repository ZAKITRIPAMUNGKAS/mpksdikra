/**
 * ID Card Masking Utilities (Pure functions for client-side cutout processing)
 */

/**
 * Apply threshold to confidence mask or probability array.
 * Converts to binary mask: 1 = foreground, 0 = background.
 *
 * @param {Float32Array|Array<number>} maskData - Probability values (0.0 to 1.0)
 * @param {number} threshold - Cutoff probability (default: 0.5)
 * @returns {Uint8Array} Binary mask (1 for foreground, 0 for background)
 */
export function applyThreshold(maskData, threshold = 0.5) {
    const len = maskData.length;
    const binary = new Uint8Array(len);
    for (let i = 0; i < len; i++) {
        binary[i] = maskData[i] >= threshold ? 1 : 0;
    }
    return binary;
}

/**
 * Clean up isolated noise islands by keeping only the largest connected component of foreground.
 *
 * @param {Uint8Array} binaryMask - Binary mask of size width * height (1 = fg, 0 = bg)
 * @param {number} width - Mask width
 * @param {number} height - Mask height
 * @returns {Uint8Array} Cleaned binary mask
 */
export function findLargestConnectedComponent(binaryMask, width, height) {
    const total = width * height;
    const visited = new Uint8Array(total);
    const cleaned = new Uint8Array(total);

    let maxComponent = [];
    const queue = new Int32Array(total);

    for (let y = 0; y < height; y++) {
        const rowOffset = y * width;
        for (let x = 0; x < width; x++) {
            const idx = rowOffset + x;
            if (binaryMask[idx] === 1 && visited[idx] === 0) {
                // Start BFS for component
                let head = 0;
                let tail = 0;
                queue[tail++] = idx;
                visited[idx] = 1;

                const componentPixels = [];

                while (head < tail) {
                    const curr = queue[head++];
                    componentPixels.push(curr);

                    const cx = curr % width;
                    const cy = Math.floor(curr / width);

                    // 4-way neighbors
                    if (cx > 0) {
                        const n = curr - 1;
                        if (binaryMask[n] === 1 && visited[n] === 0) {
                            visited[n] = 1;
                            queue[tail++] = n;
                        }
                    }
                    if (cx < width - 1) {
                        const n = curr + 1;
                        if (binaryMask[n] === 1 && visited[n] === 0) {
                            visited[n] = 1;
                            queue[tail++] = n;
                        }
                    }
                    if (cy > 0) {
                        const n = curr - width;
                        if (binaryMask[n] === 1 && visited[n] === 0) {
                            visited[n] = 1;
                            queue[tail++] = n;
                        }
                    }
                    if (cy < height - 1) {
                        const n = curr + width;
                        if (binaryMask[n] === 1 && visited[n] === 0) {
                            visited[n] = 1;
                            queue[tail++] = n;
                        }
                    }
                }

                if (componentPixels.length > maxComponent.length) {
                    maxComponent = componentPixels;
                }
            }
        }
    }

    // Only mark the largest component in the output
    for (let i = 0; i < maxComponent.length; i++) {
        cleaned[maxComponent[i]] = 1;
    }

    return cleaned;
}

/**
 * Feather edges with separable box blur of given radius.
 * Produces smooth 0..255 alpha values along perimeter.
 *
 * @param {Uint8Array} binaryMask - Binary mask (1 = fg, 0 = bg)
 * @param {number} width - Mask width
 * @param {number} height - Mask height
 * @param {number} radius - Blur radius in pixels (default: 2)
 * @returns {Uint8ClampedArray} Alpha channel values (0 to 255)
 */
export function featherAlpha(binaryMask, width, height, radius = 2) {
    const total = width * height;
    const rawAlpha = new Float32Array(total);
    for (let i = 0; i < total; i++) {
        rawAlpha[i] = binaryMask[i] === 1 ? 255.0 : 0.0;
    }

    if (radius <= 0) {
        const out = new Uint8ClampedArray(total);
        for (let i = 0; i < total; i++) out[i] = Math.round(rawAlpha[i]);
        return out;
    }

    // Horizontal pass
    const hBlurred = new Float32Array(total);
    for (let y = 0; y < height; y++) {
        const row = y * width;
        for (let x = 0; x < width; x++) {
            let sum = 0;
            let count = 0;
            for (let k = -radius; k <= radius; k++) {
                const kx = x + k;
                if (kx >= 0 && kx < width) {
                    sum += rawAlpha[row + kx];
                    count++;
                }
            }
            hBlurred[row + x] = sum / count;
        }
    }

    // Vertical pass
    const finalAlpha = new Uint8ClampedArray(total);
    for (let x = 0; x < width; x++) {
        for (let y = 0; y < height; y++) {
            let sum = 0;
            let count = 0;
            for (let k = -radius; k <= radius; k++) {
                const ky = y + k;
                if (ky >= 0 && ky < height) {
                    sum += hBlurred[ky * width + x];
                    count++;
                }
            }
            finalAlpha[y * width + x] = Math.round(sum / count);
        }
    }

    return finalAlpha;
}

/**
 * Calculate proportion of transparent pixels.
 * In 8-bit alpha (0..255), transparent is alpha <= threshold (e.g. 15, corresponding to GD alpha >= 120).
 *
 * @param {Uint8ClampedArray|Array<number>} alphaMask - Alpha values (0..255)
 * @param {number} transparentThreshold - Threshold below which pixel is considered transparent (default: 15)
 * @returns {number} Transparency ratio (0.0 to 1.0)
 */
export function calculateTransparencyRatio(alphaMask, transparentThreshold = 15) {
    if (!alphaMask || alphaMask.length === 0) return 0;
    let transparentCount = 0;
    const total = alphaMask.length;
    for (let i = 0; i < total; i++) {
        if (alphaMask[i] <= transparentThreshold) {
            transparentCount++;
        }
    }
    return transparentCount / total;
}

/**
 * Composite alpha mask onto an RGBA ImageData buffer.
 *
 * @param {ImageData} imageData - 4-channel RGBA ImageData
 * @param {Uint8ClampedArray} alphaMask - 1-channel alpha mask of matching dimensions
 * @returns {ImageData}
 */
export function compositeCutout(imageData, alphaMask) {
    const data = imageData.data;
    const total = alphaMask.length;
    for (let i = 0; i < total; i++) {
        data[i * 4 + 3] = alphaMask[i];
    }
    return imageData;
}
