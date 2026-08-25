<?php

declare(strict_types=1);

function writeWav(string $path, float $seconds, float $freq, float $volume = 0.35, int $sampleRate = 22050): void
{
    $n = (int) round($seconds * $sampleRate);
    $data = '';
    for ($i = 0; $i < $n; $i++) {
        $t = $i / $sampleRate;
        $env = min(1.0, $i / ($sampleRate * 0.01), ($n - $i) / ($sampleRate * 0.02));
        $sample = (int) round($volume * $env * 32767 * sin(2 * M_PI * $freq * $t));
        $data .= pack('v', $sample & 0xFFFF);
    }

    $dataSize = strlen($data);
    $hdr = 'RIFF'.pack('V', 36 + $dataSize).'WAVEfmt '
        .pack('VvvVVvv', 16, 1, 1, $sampleRate, $sampleRate * 2, 2, 16)
        .'data'.pack('V', $dataSize);

    file_put_contents($path, $hdr.$data);
    echo basename($path).' '.filesize($path).PHP_EOL;
}

$dir = dirname(__DIR__).DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'audio';
if (! is_dir($dir)) {
    mkdir($dir, 0755, true);
}

writeWav($dir.DIRECTORY_SEPARATOR.'timer-warn.wav', 0.18, 880.0, 0.42);
writeWav($dir.DIRECTORY_SEPARATOR.'timer-end.wav', 0.65, 320.0, 0.5);
