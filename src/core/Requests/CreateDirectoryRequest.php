<?php

namespace OrckidLab\FileManager\Core\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use OrckidLab\FileManager\Core\Model\Upload;


/**
 * Class CreateFileRequest
 * @package OrckidLab\Dashboard\Core\Requests
 */
class CreateDirectoryRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'name' => 'required',
			'path' => 'required|directory_exists:' . $this->name,
		];
	}

	/**
	 * Logic to persist values to database.
	 *
	 */
	public function persist()
	{
		$parent_directory = Upload::directory($this->path);

		$folder_name = strtolower((preg_replace('/[^A-Za-z0-9\-]/', '-', trim($this->name))));

		$full_path = $this->path . '/' . $folder_name;

		Storage::makeDirectory($full_path);

		$this->offsetSet('name', $folder_name);
		$this->offsetSet('type','directory');
		$this->offsetSet('parent_id', $parent_directory->id);
		$this->offsetSet('token', md5($folder_name . $full_path . strtotime(date('Y-m-d H:i:s'))));

		$this->upload = Upload::create($this->all());
	}

	/**
	 * Successful message on redirect.
	 *
	 * @return string
	 */
	public function message()
	{
		return 'Operation completed successfully.';
	}

	/**
	 * Additional tokens to return on redirect.
	 *
	 * @return array
	 */
	public function tokens()
	{
		return [

		];
	}

	/**
	 * Handle persisting values to database and redirect.
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function handle()
	{
		$this->persist();

		return $this->upload->format();
	}
}
