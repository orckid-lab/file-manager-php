<?php

namespace OrckidLab\FileManager\Core\Controllers;

use App\Http\Controllers\Controller;
use OrckidLab\FileManager\Core\Requests\CreateFileRequest;


/**
 * Class FileController
 * @package OrckidLab\Dashboard\Core\Controllers\FileManager
 */
class FileController extends Controller
{
	/**
	 * @param CreateFileRequest $request
	 * @return Upload
	 */
	public function store(CreateFileRequest $request)
	{
		return $request->handle();
	}
}