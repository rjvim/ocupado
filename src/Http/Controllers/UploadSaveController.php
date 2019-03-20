<?php 

namespace Betalectic\FileManager\Http\Controllers;

use Illuminate\Http\Request;
use Betalectic\FileManager\FileManager;
use Illuminate\Support\Facades\Storage;

class UploadSaveController extends DynamicController {

	public function store(Request $request)
	{
		$fileManager = new FileManager();

		if($request->has('base64'))
		{
			$savedFile = $fileManager->saveBase64ToDisk($request->get('base64'));
			$pathOnDisk = $savedFile['path'];
			$fileName = $savedFile['name'];
		}

		if($request->has('file'))
		{
			$pathOnDisk = $request->file('file')->store('files');
			$fileName = $request->file('file')->getClientOriginalName();
			$pathOnDisk = Storage::url($pathOnDisk);
		}

		$fullPath = storage_path('app/files/'.basename($pathOnDisk));
		$uploadedFile = $fileManager->upload($fullPath);

		$file = $fileManager->save(
			$uploadedFile['url'],
			$uploadedFile['disk'],
			$uploadedFile['mime_type'],
			$request->get('uploader',NULL),
			$request->get('tags',NULL),
			[
				'file_name' => $fileName
			]
		);

		$fileManager->setOwner(
			$file->uuid, 
			$request->get('owner_id',NULL),
			$request->get('owner_type',NULL)
		);

		return $file;

	}


}
