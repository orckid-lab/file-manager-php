<?php

namespace OrckidLab\FileManager\Core\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OrckidLab\FileManager\Core\ProcessUploadUpdate;


/**
 * Class UpdateFileRequest
 * @package OrckidLab\Dashboard\Core\Requests
 */
class UpdateUploadRequest extends FormRequest
{
	protected $persistUpload;

	public function __construct()
	{

		parent::__construct();

		$this->persistUpload = new ProcessUploadUpdate($this, $this->upload);
	}

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
		    'action' => 'required|validate_update_action',
		    'items' => 'required',
	    ];
    }

	/**
	 * Logic to persist values to database.
	 *
	 */
	public function persist()
    {
	    $this->upload = $this->persistUpload->handle();
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

		return $this->upload;
	}
}
