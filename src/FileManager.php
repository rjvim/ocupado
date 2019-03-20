<?php

namespace Betalectic\FileManager;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Betalectic\FileManager\Helpers\CloudinaryHelper;
use Intervention\Image\ImageManagerStatic;
use Betalectic\FileManager\Models\Library;
use Betalectic\FileManager\Models\Attachment;

class FileManager {

	public function __construct()
	{

	}

	public function saveBase64ToDisk($base64String)
	{
		$extension = explode('/', explode(':', substr($base64String, 0, strpos($base64String, ';')))[1])[1];

		$filename = microtime(true).'.'.$extension;

        ImageManagerStatic::make($base64String)->save(storage_path('app/files/') . $filename);

		$storeOnDisk = Storage::url('files/'.$filename);

		return ['path' => $storeOnDisk, 'name' => $filename];
	}

	public function upload($pathToFile)
	{
		$file = new File($pathToFile);

		$mimeType = $file->getMimeType();

		$isImage = substr($mimeType,0,5) == 'image' ? true : false;

		if($isImage && config('file-manager.save_images_to') == 'cloudinary')
		{
			$cloudinaryHelper = new CloudinaryHelper();
			$result = $cloudinaryHelper
							->uploadFile($pathToFile);
			$url = $result['secure_url'];
			$disk = 'cloudinary';
		}
		else
		{

			$s3Key = config('file-manager.file_prefix');
			$s3Key = Storage::disk('s3')->putFileAs($s3Key,$file,$file->getFilename());
			$url = Storage::disk('s3')->url($s3Key);
			unlink($pathToFile);
			$disk = 's3';
		}

		return ['url' => $url, 'disk' => $disk, 'mime_type' => $mimeType];
	}

	public function save($path, $disk = NULL, $mimeType = NULL, $uploader = NULL, $tags = NULL, $meta = [])
	{
		$file = Library::firstOrCreate(['path' => $path]);
		$file->fill([
			'disk' => $disk,
			'mime_type' => $mimeType,
			'uploaded_by' => $uploader,
			'meta' => $meta,
			'tags' => $tags
		]);

		$file->save();

		return $file;
	}

	public function setOwner($code, $owner_id, $owner_type)
	{
		$file = Library::whereUuid($code)->first();
		$file->owner_id = $owner_id;
		$file->owner_type = $owner_type;
		$file->save();
	}

	public function delete($uuid)
	{
		$file = Library::withTrashed()->whereUuid($uuid)->first();

		$ext = pathinfo($file->path, PATHINFO_EXTENSION);

		if($file->disk == 's3')
		{
			$key = config('file-manager.file_prefix').'/'.basename($file->path);
			Storage::disk('s3')->delete($key);
		}

		if($file->disk == 'cloudinary')
		{
			$cloudinaryId = basename($file->path, ".".$ext);
			\Cloudinary\Uploader::destroy($cloudinaryId);
		}

		$file->forceDelete();

		return true;
	}

}