<?php

namespace OrckidLab\FileManager\Core\Controllers;


use App\Http\Controllers\Controller;
use OrckidLab\FileManager\Core\Requests\CreateDirectoryRequest;


/**
 * Class FileController
 * @package OrckidLab\Dashboard\Core\Controllers\FileManager
 */
class DirectoryController extends Controller
{
	/**
	 * @param CreateDirectoryRequest $request
	 * @return Upload
	 */
	public function store(CreateDirectoryRequest $request)
	{
		return $request->handle();
	}
}