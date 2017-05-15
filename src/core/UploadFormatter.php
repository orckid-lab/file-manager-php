<?php


namespace OrckidLab\FileManager\Core;

use Illuminate\Support\Facades\Storage;
use OrckidLab\FileManager\Core\Model\Upload;


/**
 * Class UploadFormatter
 * @package OrckidLab\Dashboard\Core\FileManager
 */
class UploadFormatter
{
	/**
	 * @var
	 */
	protected $attributes;

	/**
	 * @var Upload
	 */
	protected $upload;

	/**
	 * UploadFormatter constructor.
	 * @param Upload $upload
	 */
	public function __construct(Upload $upload)
	{
		$this->upload = $upload;

		$this->defineAttributes();
	}

	/**
	 * Setter
	 *
	 * @param $name
	 * @param $value
	 * @return $this
	 */
	protected function set($name, $value)
	{
		$this->attributes[$name] = $value;

		return $this;
	}

	/**
	 * Set attributes.
	 *
	 */
	protected function defineAttributes()
	{
		$this
			->set('token', $this->upload->token)
			->set('path', $this->upload->path)
			->set('type', $this->upload->type)
			->set('selected', false)
			->set('updated_at', $this->upload->updated_at->format('Y/m/d'));

		if ($this->upload->type == 'directory') {
			$this->setDirectoryAttributes();
		} else {
			$this
				->set('name', substr($this->upload->name, 0, strrpos($this->upload->name, '.')))
				->set('size', Storage::size($this->upload->full_path))
				->set('icon', $this->upload->icon)
				->set('url', $this->upload->url)
				->set('thumbnail', $this->upload->thumbnail_url)
				->set('preloaded', null);
		}
	}

	/**
	 * Set attributes for an upload of directory type.
	 *
	 */
	protected function setDirectoryAttributes()
	{
		$directories = $this->upload->directories();

		$files = $this->upload->files();

		$this
			->set('name', $this->upload->name)
			->set('size', $files->get()->sum('size'))
			->set('icon', 'icon-folder-network-1')
			->set('hasDirectories', $directories->count())
			->set('hasFiles', $files->count())
			->set('subDirectories', $directories->get())
			->set('files', $this->upload->items);
	}


	/**
	 * Return attributes.
	 *
	 * @return mixed
	 */
	public function toArray()
	{
		return $this->attributes;
	}
}