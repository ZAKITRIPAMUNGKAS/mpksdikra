<?php

namespace App\Services;

use App\Models\EventPeserta;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrService
{
    public function generateToken(): string
    {
        return Str::random(48);
    }

    public function payload(EventPeserta $ep): string
    {
        $token = (string) ($ep->qr_token ?: Str::random(32));
        $signature = $this->sign((int) $ep->event_id, (int) $ep->anggota_id, $token);

        return base64_encode(json_encode([
            'e' => (int) $ep->event_id,
            'a' => (int) $ep->anggota_id,
            't' => $token,
            's' => $signature,
        ]));
    }

    public function verify(string $qrCode): ?EventPeserta
    {
        $decoded = json_decode(base64_decode($qrCode, true), true);

        if (! is_array($decoded)) {
            return null;
        }

        foreach (['e', 'a', 't', 's'] as $key) {
            if (! isset($decoded[$key])) {
                return null;
            }
        }

        $expected = $this->sign((int) $decoded['e'], (int) $decoded['a'], (string) $decoded['t']);

        if (! hash_equals($expected, (string) $decoded['s'])) {
            return null;
        }

        return EventPeserta::where('event_id', (int) $decoded['e'])
            ->where('anggota_id', (int) $decoded['a'])
            ->where('qr_token', (string) $decoded['t'])
            ->first();
    }

    private function sign(int $eventId, int $anggotaId, string $token): string
    {
        return hash_hmac('sha256', $eventId.'|'.$anggotaId.'|'.$token, config('app.key'));
    }

    public function qrFor(EventPeserta $ep): string
    {
        return QrCode::format('svg')
            ->size(180)
            ->margin(1)
            ->generate($this->payload($ep));
    }

    public function qrPngDataUri(EventPeserta $ep): string
    {
        return $this->qrPngDataUriForText($this->payload($ep));
    }

    public function qrPngDataUriForText(string $text, int $scale = 8, int $margin = 2): string
    {
        $matrix = Encoder::encode(
            $text,
            ErrorCorrectionLevel::forBits(0)
        )->getMatrix();

        $size = $matrix->getWidth();
        $px = ($size + $margin * 2) * $scale;

        $img = imagecreatetruecolor($px, $px);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 43, 64);
        imagefilledrectangle($img, 0, 0, $px, $px, $white);

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                if ($matrix->get($x, $y) === 1) {
                    imagefilledrectangle(
                        $img,
                        ($x + $margin) * $scale,
                        ($y + $margin) * $scale,
                        ($x + $margin + 1) * $scale - 1,
                        ($y + $margin + 1) * $scale - 1,
                        $black
                    );
                }
            }
        }

        ob_start();
        imagepng($img);
        imagedestroy($img);

        return 'data:image/png;base64,'.base64_encode(ob_get_clean());
    }
}
