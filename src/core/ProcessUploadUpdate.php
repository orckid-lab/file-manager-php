<?php

namespace OrckidLab\FileManager\Core;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use OrckidLab\FileManager\Core\Model\Upload;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProcessUploadUpdate
{
	protected $request;

	protected $upload;

	protected $actions = [
		1 => 'rename',
		2 => 'move',
		3 => 'edit',
		4 => 'compress',
	];

	protected $imageOptimizer;

	public function __construct(Request $request, Upload $upload = null)
	{
		$this->request = $request;

		$this->upload = $upload;
	}

	public function handle()
	{
		return $this->{$this->getMethod()}();
	}

	protected function getMethod()
	{
		return $this->actions[$this->request->action];
	}

	protected function rename()
	{
		$formatted_name = strtolower((preg_replace('/[^A-Za-z0-9\-]/', '-', trim($this->request->name))));
		$this->upload = Upload::whereToken($this->request->items[0])->first();

		$updated_name = $this->upload->isDirectory()
			? $formatted_name
			: preg_replace('/$/', substr($this->upload->name, strrpos($this->upload->name, '.') + 0), $formatted_name);

		$path_regex = '/[^\/|\\\]+$/';

		if (Storage::move($this->upload->full_path, preg_replace($path_regex, $updated_name, $this->upload->full_path))) {

			if (str_contains($this->upload->type, 'image')) {
				$new_thumbnail = preg_replace('/(\.[^.]+)$/', sprintf('%s$1', '-thumb'), $updated_name);
				Storage::move($this->upload->thumbnail_path, preg_replace($path_regex, $new_thumbnail, $this->upload->thumbnail_path));
			}
			$this->upload->update([
				'name' => $updated_name,
				'updated_by' => Auth::id()
			]);

			return $this->upload;
		}
	}

	protected function move()
	{
		$destination = Upload::whereToken($this->request->destination_token)->first();

		$output = collect($this->request->items)->reduce(function ($carry, $token) use ($destination) {

			$upload = Upload::whereToken($token)->first();
			$current_path = $upload->full_path;

			if ($upload->parent_id == $destination->id || Storage::exists($destination->path . '/' . $upload->name)) {
				$carry['errors'][] = "The item $upload->name could not be moved as it is already in the destination directory.";
			} else {

				if (str_contains($upload->type, 'image')) {
					Storage::move($upload->thumbnail_path, $destination->path . '/' . $upload->thumbnail);
				}

				$success = Storage::move($current_path, $destination->path . '/' . $upload->name);

				if (!$success) {
					$carry['errors'][] = "The item $upload->name could not be moved.";
				} else {
					if ($upload->type == 'directory') {
						$carry['moved']['directories'][] = $upload->token;
						$carry['moved']['directories_parents'][] = $upload->parent->token;
						$current_path = $destination->path . '/' . $upload->name;
					} else {
						$carry['moved']['files_parents'][] = $upload->parent->token;
						$carry['moved']['files'][] = $upload->token;
						$current_path = $destination->path;
					}
					$upload->update([
						'path' => $current_path,
						'parent_id' => $destination->id,
						'updated_by' => Auth::id()
					]);
				}
			}
			return $carry;
		}, [
				'errors' => [],
				'moved' => [
					'directories' => [],
					'directories_parents' => [],
					'files' => [],
					'files_parents' => [],
				],

			]
		);
		$output['moved']['destination'] = $destination->format();
		return $output;
	}

	protected function edit()
	{
		$this->upload = Upload::whereToken($this->request['items'])->first();
		$file = $this->request->file('file');

		$file->storeAs($this->upload->path, $this->upload->name);

		$img = Image::make($file);
		$img->resize(320, null, function ($constraint) {
			$constraint->aspectRatio();
		});
		$img->save(storage_path() . '/app/' . $this->upload->path . '/' . $this->upload->thumbnail);

		$this->upload->update([
			'size' => $file->getSize(),
			'updated_at' => new \DateTime(),
			'updated_by' => Auth::id()
		]);
		return $this->upload;
	}

	protected function optimise()
	{
		$this->imageOptimizer = new ImageOptimizer();

		$output = collect($this->request->items)->reduce(function ($carry, $token) {

			$this->upload = Upload::whereToken($token)->first();

			$image = new UploadedFile(
				storage_path('app/' . $this->upload->full_path),
				$this->upload->name,
				$this->upload->type,
				Storage::size($this->upload->full_path),
				null,
				true);

			$this->imageOptimizer->optimizeUploadedImageFile($image);
			$image->storeAs($this->upload->path, $this->upload->name);

			$this->upload->update(['size' => $image->getSize(), 'updated_at' => new \DateTime()]);

			return $carry;

		}, [
				'errors' => [],
				'optimise' => [
					'files' => [],
				],
			]

		);

		return $output;
	}

}