<?php

namespace OrckidLab\FileManager\Core;


use Illuminate\Http\Request;
use OrckidLab\FileManager\Core\Model\Upload;

/**
 * Class FileManagerPayload
 * @package OrckidLab\Dashboard\Core\FileManager
 */
class FileManagerPayload
{
	/**
	 * Root directory for uploads.
	 * @var string
	 */
	protected $root_path = 'public/uploads';

	/**
	 * @var mixed|string
	 */
	protected $path;

	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * FileManagerPayload constructor.
	 * @param Request $request
	 */
	public function __construct(Request $request)
	{
		$this->request = $request;

		$this->path = $request->has('path') ? $request->path : Upload::find(1)->path;
	}

	/**
	 * Returns the payload required for the file manager component.
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|array
	 */
	public function handle()
	{
		$directory = Upload::directory($this->path);

		$current_path = $directory->format();

		$breadcrumb[] = $directory;
		while (end($breadcrumb)->parent_id != null) {
			$breadcrumb[] = Upload::find(end($breadcrumb)->parent_id);
		}
		
		$payload = [
			'payload' => collect([
				'current_path' => $current_path,
				'root' => $this->request->has('path') ? Upload::find(1)->format() : $current_path,
				'breadcrumb' => array_reverse($breadcrumb)
			])
		];
		
		if ($this->request->ajax()) {
			return $payload;
		}
		
		return view('file-manager::index')
			->with($payload);
	}
}