<?php

namespace OrckidLab\FileManager\Core\Requests;

use App\User;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Foundation\Http\FormRequest;
use OrckidLab\FileManager\Core\Model\Upload;

class CreateFileRequest extends FormRequest
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
            'file' => 'required|file',
            'path' => 'file_exists',
        ];
    }

    /**
     * Logic to persist values to database.
     *
     */
    public function persist()
    {
        $parent_directory = Upload::directory($this->path);

        $file = $this->file('file');

        $file_name = strtolower((preg_replace('/[^A-Za-z0-9\-]/', '-', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))) . '.' . pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));

        $file->storeAs($this->path, $file_name);

        if (str_contains($file->getMimeType(), 'image')) {
            $thumbnail = substr($file_name, 0,
                    strrpos($file_name, '.')) . '-thumb' . '.' . substr($file_name,
                    strrpos($file_name, '.') + 1);

            $img = Image::make($file);
            $img->resize(320, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save(storage_path() . '/app/' . $this->path . '/' . $thumbnail);
        }

        $this->offsetSet('name', $file_name);
        $this->offsetSet('type', $file->getMimeType());
        $this->offsetSet('size', $file->getSize());
        $this->offsetSet('parent_id', $parent_directory->id);
        $this->offsetSet('token', md5($file_name . $this->path . strtotime(date('Y-m-d H:i:s'))));
	    $this->offsetSet('created_by', Auth::id());
	    $this->offsetSet('updated_by', Auth::id());

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
