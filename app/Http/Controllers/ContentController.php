<?php

namespace App\Http\Controllers;

use App\Models\Content;
use FFMpeg\Format\Video\X264;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class ContentController extends Controller
{
	public function create(Request $request): void
	{
		$filePath = $request->file('content')->store('', 'public');

		$fileName = basename($filePath);

		Content::query()->create([
			'file_name' => $fileName
		]);
	}

	public function convert()
	{
		$content = Content::first();

		$lowBitrate = (new X264)->setKiloBitrate(500);
		$midBitrate = (new X264)->setKiloBitrate(1500);
		$highBitrate = (new X264)->setKiloBitrate(4000);

		FFMpeg::fromDisk('public')
			->open($content->file_name)
			->exportForHLS()
			->setSegmentLength(2) // optional
			->setKeyFrameInterval(60) // optional
			->addFormat($lowBitrate)
			->addFormat($midBitrate)
			->addFormat($highBitrate)
			->save($content->file_name . '.m3u8');
	}

	/**
	 * @return JsonResponse
	 */
	public function read()
	{
//		$content = Content::first();
//
		return response()->json([
			'content' => Storage::url('adaptive_steve.m3u8'),
		]);
	}
}