<?php

namespace OrckidLab\FileManager\Core\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use OrckidLab\FileManager\Core\Model\Upload;


class DownloadController extends Controller
{
	public function index(Request $request)
	{
		$upload = Upload::whereToken($request->items[0])->first();

		if ($upload->type == 'directory') {
			return App::abort();
		}
		return response()->download(storage_path('app/' . $upload->full_path));

    }
}
