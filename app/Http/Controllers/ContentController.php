<?php

namespace App\Http\Controllers;

use App\Jobs\VideoContentToHlcConvertJob;
use App\Models\Content;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
	public function create(Request $request): void
	{
		$filePath = $request->file('content')->store('', 'public');

		$fileName = basename($filePath);

        VideoContentToHlcConvertJob::dispatch($fileName);

		Content::query()->create([
			'file_name' => $fileName
		]);
	}

	/**
	 * @return JsonResponse
	 */
	public function read()
	{
		$content = Content::first();

        $filename = pathinfo($content->file_name, PATHINFO_FILENAME);

		return response()->json([
			'content' => Storage::url($filename . '.m3u8'),
		]);
	}
}