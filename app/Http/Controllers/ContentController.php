<?php

namespace App\Http\Controllers;

use App\Jobs\VideoContentToHlcConvertJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
	public function upload(Request $request): JsonResponse
	{
		try {
			if (!$request->hasFile('content')) {
				return response()->json([
					'error' => 'Файл не найден'
				], 400);
			}

			$file = $request->file('content');
			$fileName = $file->hashName(); // Генерирует уникальное имя файла

			// Сохраняем файл
			$file->storeAs('', $fileName, 'public');

			// Запускаем обработку
			VideoContentToHlcConvertJob::dispatch($fileName);

			return response()->json([
				'fileName' => $fileName,
				'message' => 'Видео загружено и поставлено в очередь на конвертацию'
			]);

		} catch (\Exception $e) {
			Log::error('Upload failed: ' . $e->getMessage());
			return response()->json([
				'error' => 'Ошибка при загрузке файла'
			], 500);
		}
	}

	public function getConversionProgress(string $fileName): JsonResponse
	{
		$fileName = pathinfo($fileName, PATHINFO_FILENAME);

		$cacheKey = "video_conversion_{$fileName}";

		$progress = Cache::get($cacheKey, ['progress' => 0, 'status' => 'unknown']);

		return response()->json([
			'progress' => (int)($progress['progress'] ?? 0),
			'status' => $progress['status'] ?? 'unknown'
		]);
	}

	public function read(string $fileName): JsonResponse
	{
		try {
			$filename = pathinfo($fileName, PATHINFO_FILENAME);
			$m3u8Path = storage_path('app/public/' . $filename . '.m3u8');

			if (!file_exists($m3u8Path)) {
				return response()->json([
					'error' => 'Видео еще не готово'
				], 404);
			}

			if (!is_readable($m3u8Path)) {
				return response()->json([
					'error' => 'Ошибка доступа к файлу'
				], 500);
			}

			return response()->json([
				'content' => Storage::url($filename . '.m3u8'),
			]);

		} catch (\Exception $e) {
			Log::error('Error reading video file: ' . $e->getMessage());
			return response()->json([
				'error' => 'Ошибка при получении видео'
			], 500);
		}
	}
}