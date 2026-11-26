import jsQR from 'jsqr';

let detectorPromise = null;

async function getBarcodeDetector() {
    if (!('BarcodeDetector' in window)) {
        return null;
    }

    if (!detectorPromise) {
        detectorPromise = (async () => {
            try {
                if (typeof window.BarcodeDetector.getSupportedFormats === 'function') {
                    const formats = await window.BarcodeDetector.getSupportedFormats();
                    if (!formats.includes('qr_code')) {
                        return null;
                    }
                }

                return new window.BarcodeDetector({ formats: ['qr_code'] });
            } catch {
                return null;
            }
        })();
    }

    return detectorPromise;
}

export async function decodeQrFromVideo(video, canvas) {
    if (!video || video.readyState < 2 || !video.videoWidth || !video.videoHeight) {
        return null;
    }

    const detector = await getBarcodeDetector();
    if (detector) {
        try {
            const codes = await detector.detect(video);
            const match = codes.find((item) => item && item.rawValue);
            if (match) {
                return match.rawValue;
            }
        } catch {
            // Fall back to jsQR when native BarcodeDetector fails on a frame.
        }
    }

    const maxWidth = 960;
    const scale = video.videoWidth > maxWidth ? maxWidth / video.videoWidth : 1;
    const width = Math.max(1, Math.round(video.videoWidth * scale));
    const height = Math.max(1, Math.round(video.videoHeight * scale));

    if (canvas.width !== width || canvas.height !== height) {
        canvas.width = width;
        canvas.height = height;
    }

    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    if (!ctx) {
        return null;
    }

    ctx.drawImage(video, 0, 0, width, height);
    const imageData = ctx.getImageData(0, 0, width, height);
    const result = jsQR(imageData.data, width, height, {
        inversionAttempts: 'attemptBoth',
    });

    return result && result.data ? result.data : null;
}

window.ArqamQrScanner = {
    decodeQrFromVideo,
};
