<?php 

namespace Betalectic\FileManager\Http\Controllers;

use Illuminate\Http\Request;
use Betalectic\FileManager\Models\Library;
use Betalectic\FileManager\Http\Resources\File as FileResource;
use Betalectic\FileManager\FileManager;

class FileController extends DynamicController {

	public function index(Request $request)
	{	
		$libraryBuilder = Library::orderBy('created_at','DESC');

		if($request->has('type'))
		{
			switch ($request->type) {
				case 'images':
					$libraryBuilder = $libraryBuilder->where('mime_type','LIKE','image%');
					break;

				case 'documents':
					$libraryBuilder = $libraryBuilder->where('mime_type','NOT LIKE','image%')
						->orWhereNull('mime_type');
					break;
				
				default:
					# code...
					break;
			}
		}

		$files = $libraryBuilder->paginate(20);

		return FileResource::collection($files);
	}

	public function update(Request $request, $code)
	{
		$file = Library::whereUuid($code)->first();
		$file->meta = $request->except('tags');
		$file->tags = $request->get('tags',NULL);
		$file->save();

		return response('success',200);
	}

	public function destroy($code)
	{
		$fileManager = new FileManager();
		$fileManager->delete($code);

		return response('success',200);
	}

	public function tags()
	{
		$tagsCollection = Library::select('tags')->get()->pluck('tags');

		$tagsCollection = $tagsCollection->map(function ($item, $key) {
		    return $item;
		});

		$tagsCollection = $tagsCollection->flatten()->unique();

		$tagsCollection = $tagsCollection->map(function ($item, $key) {
		    return $item;
		});

		$tags = [];

		foreach($tagsCollection as $singleTag)
		{
			$tags[$singleTag] = $singleTag;
		}

		return $tags;

		return json_encode($tagsCollection,JSON_FORCE_OBJECT);
	}

}
