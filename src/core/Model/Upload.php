<?php

namespace OrckidLab\FileManager\Core\Model;

use Illuminate\Database\Eloquent\Model;
use OrckidLab\FileManager\Core\UploadFormatter;

/**
 * Class Upload
 * @package OrckidLab\Dashboard\Core\Model
 */
class Upload extends Model
{
	/**
	 * @var array
	 */
	protected $fillable = [
		'name',
		'type',
		'parent_id',
		'size',
		'token'
	];

	/**
	 * @return mixed|string
	 */
	public function getFullPathAttribute()
	{
		return $this->isDirectory() ? $this->path : $this->path . '/' . $this->name;
	}

	/**
	 * @return string
	 */
	public function getThumbnailAttribute()
	{
		return substr($this->name, 0,
			strrpos($this->name, '.')) . '-thumb' . '.' . substr($this->name,
			strrpos($this->name, '.') + 1);
	}

	/**
	 * @return string
	 */
	public function getThumbnailUrlAttribute()
	{
		$storage_path = preg_replace('/public/', 'public/storage', $this->path, 1);
		return ltrim($storage_path . '/' . $this->getThumbnailAttribute(), 'public');
		//return ltrim($this->path . '/' . $this->getThumbnailAttribute(), 'public');
	}

	/**
	 * @return string
	 */
	public function getThumbnailPathAttribute()
	{
		return $this->parent->path . '/' . $this->thumbnail;
	}

	/**
	 * @return mixed
	 */
	public function getPathAttribute()
	{
		$parent_id = $this->parent_id;
		$paths[] = $this->name;

		while ($parent_id != null){
			$item = Upload::whereId($parent_id)->first();
			$paths[] = $item->name;
			$parent_id = $item->parent_id;
		}

		if(!$this->isDirectory()){
			array_shift($paths);
		}


		return collect(array_reverse($paths))->reduce(function ($carry, $name) {
			return $carry . '/' . $name;
		}, 'public');
		
	}

	/**
	 * @return string
	 */
	public function getUrlAttribute()
	{
		$storage_path = preg_replace('/public/', 'public/storage', $this->path,1);
		return ltrim($storage_path . '/' . $this->name, 'public');
		//return ltrim($this->path . '/' . $this->name, 'public');
	}

	/**
	 * @return mixed|string
	 */
	public function getIconAttribute()
	{
		$icon = [
			'directory' => 'icon-folder-network-1',
			'application' => 'icon-file-new-2',
			'text' => 'icon-file-notepad',
			'txt' => 'icon-file-notepad',
			'woff' => 'icon-font-size',
			'otf' => 'icon-font-size',
			'eot' => 'icon-font-size',
			'ttf' => 'icon-font-size',
			'indd' => 'icon-font-size',
			'tiff' => 'icon-file-format-tiff',
			'eps' => 'icon-file-format-eps',
			'dmg' => 'icon-file-format-dmg',
			'video' => 'icon-video-clip-2',
			'wmv' => 'icon-video-clip-2',
			'mp4' => 'icon-video-clip-2',
			'mpeg' => 'icon-video-clip-2',
			'flv' => 'icon-video-clip-2',
			'mp3' => 'icon-music-note-1',
			'wav' => 'icon-music-note-1',
			'ogg' => 'icon-music-note-1',
			'pdf' => 'icon-file-pdf',
			'spreadsheet' => 'icon-file-excel',
			'docx' => 'icon-file-words',
			'presentation' => 'icon-file-powerpoint',
			'csv' => 'icon-file-format-csv',
			'rar' => 'icon-file-format-rar',
			'zip' => 'icon-file-format-zip',
			'html' => 'icon-file-format-html',
			'xml' => 'icon-file-format-xml',
			'js' => 'icon-file-format-javascript',
			'css' => 'icon-file-format-css',
			'php' => 'icon-file-format-php',
			'exe' => 'icon-file-format-exe',
			'sql' => 'icon-file-format-sql',
			'apk' => 'icon-file-format-apk',
			'photoshop' => 'icon-file-format-photoshop',
			'ai' => 'icon-file-format-illustrator'
		];
		$ext = pathinfo($this->name, PATHINFO_EXTENSION);
		return array_key_exists($ext, $icon) ? $icon[$ext] : 'icon-copy-1';
	}

	/**
	 * @return mixed
	 */
	public function directories()
	{
		return $this->hasMany(Upload::class, 'parent_id')->whereType('directory');
	}

	/**
	 * @return mixed
	 */
	public function parent()
	{
		return $this->hasOne(Upload::class, 'id', 'parent_id')->whereType('directory');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function items()
	{
		return $this->hasMany(Upload::class, 'parent_id');
	}

	/**
	 * @return mixed
	 */
	public function files()
	{
		return $this->hasMany(Upload::class, 'parent_id')->where('type', '!=', 'directory');
	}

	/**
	 * @return mixed
	 */
	public function jsonSerialize()
	{
		return $this->format();
	}

	/**
	 * @return mixed
	 */
	public function format()
	{
		return (new UploadFormatter($this))->toArray();
	}

	/**
	 * @return bool
	 */
	public function isDirectory()
	{
		return $this->type == 'directory';
	}

	/**
	 * @param $query
	 * @param $path
	 * @return mixed
	 */
	public static function scopeDirectory($query, $path)
	{
		$segments = explode('/', $path);
		return Upload::whereName(end($segments))->whereType('directory')->get()->filter(function ($upload, $key) use ($path) {
			return $upload->path == $path;
		})->first();
	}

}
