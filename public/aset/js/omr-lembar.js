/*
 * Pembaca lembar jawaban (OMR) tanpa AI dan tanpa upload: foto diproses di browser.
 * Koordinat lembar berasal dari App\Support\LembarJawabanLayout (satuan pt).
 */
(function () {
    'use strict';

    const MAX_DIM = 1600;
    const BATAS_ISI = 0.4;
    const BATAS_RAGU = 0.55;

    function checksum(eventId, tipe, anggotaId) {
        const s = eventId + '|' + tipe + '|' + anggotaId;
        let h = 0x811c9dc5;
        for (let i = 0; i < s.length; i++) {
            h = Math.imul(h ^ s.charCodeAt(i), 0x01000193) >>> 0;
        }
        return ((h ^ (h >>> 16)) & 0xfff) >>> 0;
    }

    function muatGambar(file) {
        if (window.createImageBitmap) {
            return createImageBitmap(file, { imageOrientation: 'from-image' }).catch(() => muatViaImg(file));
        }
        return muatViaImg(file);
    }

    function muatViaImg(file) {
        return new Promise((resolve, reject) => {
            const url = URL.createObjectURL(file);
            const img = new Image();
            img.onload = () => { URL.revokeObjectURL(url); resolve(img); };
            img.onerror = () => { URL.revokeObjectURL(url); reject(new Error('File bukan gambar yang bisa dibaca.')); };
            img.src = url;
        });
    }

    function keCanvas(src) {
        const sw = src.width, sh = src.height;
        const k = Math.min(1, MAX_DIM / Math.max(sw, sh));
        const c = document.createElement('canvas');
        c.width = Math.round(sw * k);
        c.height = Math.round(sh * k);
        c.getContext('2d').drawImage(src, 0, 0, c.width, c.height);
        return c;
    }

    function siapkan(imageData) {
        const { width: w, height: h, data } = imageData;
        const gray = new Uint8Array(w * h);
        for (let i = 0, j = 0; i < gray.length; i++, j += 4) {
            gray[i] = (data[j] * 299 + data[j + 1] * 587 + data[j + 2] * 114) / 1000;
        }
        const iw = w + 1;
        const integral = new Float64Array(iw * (h + 1));
        for (let y = 0; y < h; y++) {
            let row = 0;
            for (let x = 0; x < w; x++) {
                row += gray[y * w + x];
                integral[(y + 1) * iw + x + 1] = integral[y * iw + x + 1] + row;
            }
        }
        const rad = Math.max(8, Math.round(Math.max(w, h) / 24));
        const mean = (x, y) => {
            const x0 = Math.max(0, x - rad), y0 = Math.max(0, y - rad);
            const x1 = Math.min(w, x + rad + 1), y1 = Math.min(h, y + rad + 1);
            const s = integral[y1 * iw + x1] - integral[y0 * iw + x1] - integral[y1 * iw + x0] + integral[y0 * iw + x0];
            return s / ((x1 - x0) * (y1 - y0));
        };
        return { w, h, gray, mean };
    }

    function cariBlob(img) {
        const { w, h, gray, mean } = img;
        const dark = new Uint8Array(w * h);
        for (let y = 0; y < h; y++) {
            for (let x = 0; x < w; x++) {
                const i = y * w + x;
                dark[i] = gray[i] < mean(x, y) * 0.72 && gray[i] < 170 ? 1 : 0;
            }
        }
        const label = new Int32Array(w * h);
        const stack = new Int32Array(w * h);
        const blobs = [];
        const minArea = Math.max(25, (w * h) / 40000);
        const maxArea = (w * h) * 0.02;
        let id = 0;
        for (let s = 0; s < dark.length; s++) {
            if (!dark[s] || label[s]) continue;
            id++;
            let top = 0, n = 0, sx = 0, sy = 0, x0 = w, x1 = 0, y0 = h, y1 = 0;
            stack[top++] = s;
            label[s] = id;
            while (top) {
                const p = stack[--top];
                const x = p % w, y = (p - x) / w;
                n++; sx += x; sy += y;
                if (x < x0) x0 = x; if (x > x1) x1 = x; if (y < y0) y0 = y; if (y > y1) y1 = y;
                if (x > 0 && dark[p - 1] && !label[p - 1]) { label[p - 1] = id; stack[top++] = p - 1; }
                if (x < w - 1 && dark[p + 1] && !label[p + 1]) { label[p + 1] = id; stack[top++] = p + 1; }
                if (y > 0 && dark[p - w] && !label[p - w]) { label[p - w] = id; stack[top++] = p - w; }
                if (y < h - 1 && dark[p + w] && !label[p + w]) { label[p + w] = id; stack[top++] = p + w; }
            }
            if (n < minArea || n > maxArea) continue;
            const bw = x1 - x0 + 1, bh = y1 - y0 + 1;
            const aspect = bw / bh;
            const fill = n / (bw * bh);
            if (aspect < 0.4 || aspect > 2.5 || fill < 0.45) continue;
            blobs.push({ x: sx / n, y: sy / n, area: n });
        }
        return blobs;
    }

    function homografi(src, dst) {
        const A = [], b = [];
        for (let i = 0; i < 4; i++) {
            const [x, y] = src[i], [u, v] = dst[i];
            A.push([x, y, 1, 0, 0, 0, -u * x, -u * y]); b.push(u);
            A.push([0, 0, 0, x, y, 1, -v * x, -v * y]); b.push(v);
        }
        for (let c = 0; c < 8; c++) {
            let p = c;
            for (let r = c + 1; r < 8; r++) if (Math.abs(A[r][c]) > Math.abs(A[p][c])) p = r;
            if (Math.abs(A[p][c]) < 1e-9) return null;
            [A[c], A[p]] = [A[p], A[c]]; [b[c], b[p]] = [b[p], b[c]];
            for (let r = 0; r < 8; r++) {
                if (r === c) continue;
                const f = A[r][c] / A[c][c];
                for (let k = c; k < 8; k++) A[r][k] -= f * A[c][k];
                b[r] -= f * b[c];
            }
        }
        const H = b.map((v, i) => v / A[i][i]);
        return (x, y) => {
            const d = H[6] * x + H[7] * y + 1;
            return [(H[0] * x + H[1] * y + H[2]) / d, (H[3] * x + H[4] * y + H[5]) / d];
        };
    }

    function gelap(img, map, cx, cy, half, bulat, ambang) {
        const N = 9;
        let total = 0, hit = 0;
        for (let i = 0; i < N; i++) {
            for (let j = 0; j < N; j++) {
                const dx = ((i + 0.5) / N * 2 - 1) * half;
                const dy = ((j + 0.5) / N * 2 - 1) * half;
                if (bulat && dx * dx + dy * dy > half * half) continue;
                const [u, v] = map(cx + dx, cy + dy);
                const x = Math.round(u), y = Math.round(v);
                if (x < 0 || y < 0 || x >= img.w || y >= img.h) return -1;
                total++;
                if (img.gray[y * img.w + x] < img.mean(x, y) * ambang) hit++;
            }
        }
        return total ? hit / total : 0;
    }

    function gelapCincin(img, map, cx, cy, r1, r2) {
        const N = 32;
        let total = 0, hit = 0;
        for (const rr of [r1, (r1 + r2) / 2, r2]) {
            for (let i = 0; i < N; i++) {
                const a = (i / N) * Math.PI * 2;
                const [u, v] = map(cx + Math.cos(a) * rr, cy + Math.sin(a) * rr);
                const x = Math.round(u), y = Math.round(v);
                if (x < 0 || y < 0 || x >= img.w || y >= img.h) continue;
                total++;
                if (img.gray[y * img.w + x] < img.mean(x, y) * 0.62) hit++;
            }
        }
        return total ? hit / total : 0;
    }

    function luasQuad(pts) {
        let s = 0;
        for (let i = 0; i < 4; i++) {
            const [x1, y1] = pts[i], [x2, y2] = pts[(i + 1) % 4];
            s += x1 * y2 - x2 * y1;
        }
        return s / 2;
    }

    function cobaPeta(img, L, quad, blobs, ctx) {
        const sheet = L.markers.map(m => [m.x, m.y]);
        const map = homografi(sheet, quad.map(b => [b.x, b.y]));
        if (!map) return null;
        const m = L.marker / 2;
        for (let i = 0; i < 4; i++) {
            const c = L.markers[i];
            const pts = [[c.x - m, c.y - m], [c.x + m, c.y - m], [c.x + m, c.y + m], [c.x - m, c.y + m]].map(p => map(p[0], p[1]));
            const harap = Math.abs(luasQuad(pts));
            const rasio = blobs[i].area / harap;
            if (rasio < 0.45 || rasio > 1.7) return null;
        }
        const half = L.bitSize * 0.3;
        let nilai = 0;
        for (const b of L.bits) {
            const f = gelap(img, map, b.x, b.y, half, false, 0.72);
            if (f < 0) return null;
            nilai = nilai * 2 + (f > 0.5 ? 1 : 0);
        }
        const anggotaId = Math.floor(nilai / 4096);
        if (nilai % 4096 !== checksum(ctx.eventId, ctx.tipe, anggotaId)) return null;
        return { map, anggotaId };
    }

    function temukanLembar(img, L, ctx) {
        const blobs = cariBlob(img);
        if (blobs.length < 4) return null;
        const K = 6;
        const urut = (f) => blobs.slice().sort((a, b) => f(a) - f(b)).slice(0, K);
        const pojok = [
            urut(b => b.x + b.y),
            urut(b => -(b.x - b.y)),
            urut(b => -(b.x + b.y)),
            urut(b => b.x - b.y),
        ];
        const minLuas = img.w * img.h * 0.12;
        const kombinasi = [];
        for (let a = 0; a < pojok[0].length; a++)
            for (let b = 0; b < pojok[1].length; b++)
                for (let c = 0; c < pojok[2].length; c++)
                    for (let d = 0; d < pojok[3].length; d++)
                        kombinasi.push([a + b + c + d, [pojok[0][a], pojok[1][b], pojok[2][c], pojok[3][d]]]);
        kombinasi.sort((p, q) => p[0] - q[0]);

        for (const [, q] of kombinasi) {
            if (new Set(q).size < 4) continue;
            if (Math.abs(luasQuad(q.map(b => [b.x, b.y]))) < minLuas) continue;
            for (let rot = 0; rot < 4; rot++) {
                const quad = [0, 1, 2, 3].map(i => q[(i + rot) % 4]);
                const hasil = cobaPeta(img, L, quad, quad, ctx);
                if (hasil) return hasil;
            }
        }
        return null;
    }

    function bacaJawaban(img, L, map) {
        const half = L.r * 0.55;
        const huruf = [];
        const ragu = [];
        const titik = [];
        L.soal.forEach((s, idx) => {
            const f = s.opsi.map(o => Math.max(0, gelap(img, map, o.x, o.y, half, true, 0.62)));
            let i1 = 0;
            f.forEach((v, i) => { if (v > f[i1]) i1 = i; });
            const sisa = f.filter((_, i) => i !== i1);
            const m1 = f[i1], m2 = sisa.length ? Math.max(...sisa) : 0;
            let c, status;
            const cincin = m1 < BATAS_ISI ? s.opsi.map(o => gelapCincin(img, map, o.x, o.y, L.r * 1.25, L.r * 1.55)) : [];
            let c1 = 0;
            cincin.forEach((v, i) => { if (v > cincin[c1]) c1 = i; });
            const cincinLain = cincin.filter((_, i) => i !== c1);
            if (m1 < BATAS_ISI && cincin.length && cincin[c1] >= 0.3 && cincin[c1] > 2 * Math.max(0, ...cincinLain) + 0.1) {
                i1 = c1; c = s.opsi[c1].label; status = 'lingkar';
            } else if (m1 < BATAS_ISI && m1 >= 0.15 && m1 > 2 * m2 + 0.05) {
                c = s.opsi[i1].label; status = 'ragu';
            } else if (m1 < BATAS_ISI) { c = '-'; status = 'kosong'; }
            else if (m2 >= BATAS_ISI && m2 >= m1 * 0.6) { c = '?'; status = 'ganda'; }
            else { c = s.opsi[i1].label; status = m1 < BATAS_RAGU ? 'ragu' : 'ok'; }
            if (status !== 'ok' && status !== 'kosong') ragu.push(idx + 1);
            huruf.push(c);
            s.opsi.forEach((o, i) => titik.push({ x: o.x, y: o.y, pilih: c !== '-' && (i === i1 || (c === '?' && f[i] >= BATAS_ISI)), status }));
        });
        return { jawaban: huruf.join(''), ragu, titik };
    }

    function pratinjau(canvas, L, map, titik) {
        const lebar = 360;
        const k = lebar / canvas.width;
        const c = document.createElement('canvas');
        c.width = lebar;
        c.height = Math.round(canvas.height * k);
        const g = c.getContext('2d');
        g.drawImage(canvas, 0, 0, c.width, c.height);
        const rr = Math.max(2.5, Math.abs(map(L.r, 0)[0] - map(0, 0)[0]) * k);
        for (const t of titik) {
            const [u, v] = map(t.x, t.y);
            g.beginPath();
            g.arc(u * k, v * k, rr, 0, Math.PI * 2);
            if (t.pilih) {
                g.fillStyle = t.status === 'ok' ? 'rgba(16,185,129,0.75)' : 'rgba(245,158,11,0.85)';
                g.fill();
            } else {
                g.strokeStyle = 'rgba(56,189,248,0.6)';
                g.lineWidth = 1;
                g.stroke();
            }
        }
        return c.toDataURL('image/jpeg', 0.8);
    }

    function bacaCanvas(canvas, L, ctx) {
        const g = canvas.getContext('2d', { willReadFrequently: true });
        const img = siapkan(g.getImageData(0, 0, canvas.width, canvas.height));
        const lembar = temukanLembar(img, L, ctx);
        if (!lembar) {
            return { ok: false, pesan: 'Lembar tidak dikenali. Pastikan keempat kotak hitam di pojok terlihat, foto lurus dari atas, terang, dan lembar ini milik tes yang sama.' };
        }
        const hasil = bacaJawaban(img, L, lembar.map);
        return {
            ok: true,
            anggotaId: lembar.anggotaId,
            jawaban: hasil.jawaban,
            ragu: hasil.ragu,
            pratinjau: ctx.pratinjau === false ? null : pratinjau(canvas, L, lembar.map, hasil.titik),
        };
    }

    async function baca(file, L, ctx) {
        const src = await muatGambar(file);
        return bacaCanvas(keCanvas(src), L, ctx);
    }

    window.OmrLembar = { baca, bacaCanvas, checksum };
})();
