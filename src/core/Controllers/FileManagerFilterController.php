<?php


namespace OrckidLab\FileManager\Core\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OrckidLab\FileManager\Core\Model\Upload;


class FileManagerFilterController extends Controller
{
	public function index(Request $request)
	{
		$result = Upload::where('name', 'LIKE', '%' . $request->search . '%')->get();
		return $result;
	}
}