<?php

namespace OrckidLab\FileManager\Core\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OrckidLab\FileManager\Core\FileManagerPayload;
use OrckidLab\FileManager\Core\Model\Upload;
use OrckidLab\FileManager\Core\Requests\UpdateUploadRequest;


class FileManagerController extends Controller
{
	public function index(FileManagerPayload $fileManagerPayload)
	{
		return $fileManagerPayload->handle();
	}


	public function update(UpdateUploadRequest $request)
	{
		return $request->handle();
	}


	public function destroy(Request $request)
	{
		$items = Upload::whereIn('token', $request->items)->get();

		$output = $items->reduce(function ($carry, $item) {

			if (str_contains($item->type, 'image')) {
				Storage::delete($item->path . '/' . $item->thumbnail);
			}

			$success = $item->type == 'directory' ? Storage::deletedirectory($item->path) : Storage::delete($item->full_path);

			if (!$success) {
				$carry['errors'][] = "The item $item->path could not be deleted.";
			} else {
				if ($item->type == 'directory') {
					$carry['deleted']['directories'][] = $item->token;
					$carry['deleted']['directories_parents'][] = $item->parent->token;
				} else {
					$carry['deleted']['files_parents'][] = $item->parent->token;
					$carry['deleted']['files'][] = $item->token;
				}
				Upload::destroy($item->id);
			}

			return $carry;
		}, [
				'errors' => [],
				'deleted' => [
					'directories' => [],
					'directories_parents' => [],
					'files' => [],
					'files_parents' => [],
				],
			]
		);

		return $output;
	}
	
}