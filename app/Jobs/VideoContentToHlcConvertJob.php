<?php

namespace App\Jobs;

use FFMpeg\Format\Video\X264;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class VideoContentToHlcConvertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public string $fileName;

    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }

    public function handle(): void
    {
        $lowBitrate = (new X264)->setKiloBitrate(500);
        $midBitrate = (new X264)->setKiloBitrate(1500);
        $highBitrate = (new X264)->setKiloBitrate(4000);

        $filename = pathinfo($this->fileName, PATHINFO_FILENAME);

        FFMpeg::fromDisk('public')
            ->open($this->fileName)
            ->exportForHLS()
            ->setSegmentLength(3)
            ->setKeyFrameInterval(60)
            ->addFormat($lowBitrate)
            ->addFormat($midBitrate)
            ->addFormat($highBitrate)
            ->save($filename . '.m3u8');
    }
}
