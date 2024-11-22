<?php

namespace App\Jobs;

use App\Enums\KiloBitrates;
use FFMpeg\Format\Video\X264;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Throwable;

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
		try {
			$lowBitrate = (new X264)->setKiloBitrate(KiloBitrates::Low->value)
				->setAudioKiloBitrate(64);;

			$midBitrate = (new X264)->setKiloBitrate(KiloBitrates::Mid->value)
				->setAudioKiloBitrate(96);

			$hdBitrate = (new X264)->setKiloBitrate(KiloBitrates::HD->value)
				->setAudioKiloBitrate(128);

			$fullHdBitrate = (new X264)->setKiloBitrate(KiloBitrates::FullHD->value)
				->setAudioKiloBitrate(192);

			$filename = pathinfo($this->fileName, PATHINFO_FILENAME);
			$cacheKey = "video_conversion_{$filename}";

			Cache::put($cacheKey, [
				'progress' => 0,
				'status' => 'processing',
				'start_time' => now()->timestamp
			], 3600);

			FFMpeg::fromDisk('public')
				->open($this->fileName)
				->exportForHLS()
				->setSegmentLength(4)
				->setKeyFrameInterval(60)
				->addFormat($lowBitrate)
				->addFormat($midBitrate)
				->addFormat($hdBitrate)
				->addFormat($fullHdBitrate)
				->onProgress(function ($progress) use ($cacheKey) {
					Cache::put($cacheKey, [
						'progress' => $progress,
						'status' => 'processing',
						'current_time' => now()->timestamp
					], 3600);
				})
				->save($filename . '.m3u8');

			Cache::put($cacheKey, [
				'progress' => 100,
				'status' => 'completed',
				'end_time' => now()->timestamp
			], 3600);
		} catch (Throwable $exception) {
			Log::error('Video conversion failed', [
				'filename' => $this->fileName,
				'error' => $exception->getMessage()
			]);

			Cache::put($cacheKey, [
				'status' => 'error',
				'error_message' => $exception->getMessage(),
				'error_time' => now()->timestamp
			], 3600);

			Storage::disk('public')->delete($this->fileName);
			Storage::disk('public')->delete($filename . '.m3u8');

			throw $exception;
		}
    }
}
