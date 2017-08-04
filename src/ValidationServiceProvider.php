<?php

namespace OrckidLab\FileManager;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use OrckidLab\FileManager\Core\Model\Upload;


class ValidationServiceProvider extends ServiceProvider
{
	protected $message;

	public function boot()
	{
		Validator::extend('file_exists', function ($attribute, $path) {
			$file_name = Input::file('file')->getClientOriginalName();
			$formatted_Name = strtolower((preg_replace('/[^A-Za-z0-9\-]/', '-', pathinfo($file_name, PATHINFO_FILENAME))) . '.' . pathinfo($file_name, PATHINFO_EXTENSION));
			$directory_id = Upload::directory($path)->id;
			return !Upload::whereParentId($directory_id)->whereName($formatted_Name)->first();
		});

		Validator::replacer('file_exists', function ($message, $attribute, $rule, $parameters) {
			$file = Input::file('file')->getClientOriginalName();
			return "File $file already exist in the selected directory.";
		});

		Validator::extend('directory_exists', function ($attribute, $path, $parameters) {
			$folder_name = strtolower((preg_replace('/[^A-Za-z0-9\-]/', '-', Input::get('name'))));
			$directory_id = Upload::directory($path)->id;
			return !Upload::whereParentId($directory_id)->whereName($folder_name)->first();
		});

		Validator::replacer('directory_exists', function () {
			return 'Directory with same name already exist';
		});

		Validator::extend('validate_update_action', function ($attribute, $value, $parameters) {
			switch ($value) {
				case (1):
					$upload = Upload::whereToken(Input::get('items')[0])->first();
					$updated_name = strtolower((preg_replace('/[^A-Za-z0-9\-]/', '-', trim(Input::get('name')))));

					if (!$upload->isDirectory()) {
						$updated_name = preg_replace('/$/', substr($upload->name, strrpos($upload->name, '.') + 0), $updated_name);
					}
					$this->message = 'Item with same name already exist in current directory';
					return !Upload::whereParentId($upload->parent_id)->whereName($updated_name)->first();
					break;
			}
			return true;
		});

		Validator::replacer('validate_update_action', function ($message, $attribute, $rule, $parameters) {
			return str_replace(':validate_update_action', 'SUCCESS', $this->message);
		});
	}
}