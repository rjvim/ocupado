<?php

namespace Betalectic\FileManager\Helpers;
use Illuminate\Support\Facades\Storage;
use Auth;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic;

use Betalectic\FileManager\Models\MediaLibrary;

class FileUploadHelper {

    public $user;

    function __construct()
    {
        $this->user = Auth::user();
    }

    public function uploadBase64Image($source_file,$file_name = '')
    {
        $sourceFile = $source_file;
        $extension = explode('/', explode(':', substr($sourceFile, 0, strpos($sourceFile, ';')))[1])[1];

        $filename = $file_name.'-'.time().'.'.$extension;
        
        ImageManagerStatic::make($sourceFile)->save(storage_path('uploads') . $filename);
        $source_path =  storage_path('uploads') . $filename;

        $cloudinaryHelper = new CloudinaryHelper();
        $cloudinary_uploaded_data = $cloudinaryHelper->uploadFile($source_path);

        $mediaLibraryData = [];
        $mediaLibraryData['format'] = $extension;
        $mediaLibraryData['uploaded_by'] = $this->user->id;
        $mediaLibraryData['filename'] = $filename;
        $mediaLibraryData['provider'] = 'cloudinary';
        $mediaLibraryData['path'] = $cloudinary_uploaded_data['secure_url'];
        $mediaLibraryData['type'] = 'image';
        
        if(array_key_exists('path', $mediaLibraryData)) {
            return MediaLibrary::create($mediaLibraryData);
        } else {
            return [];
        }
        
    }
    
    public function upload($source_file,$file_name = '')
    {
        $mediaLibraryData = [];
        $sourceFile = $source_file;
        $extension = $sourceFile->getClientOriginalExtension();

        if($file_name != '') {
            $filename = $file_name.'-'.time().'.'.$extension;
        } else {
            $filename = $sourceFile->getClientOriginalName().'-'.time().'.'.$extension;
        }
        $sourceFile = $sourceFile->move(storage_path('uploads'), $filename);
        $data['key'] = env('FILE_PREFIX','ahamlearning').'/files/'.$filename;
        $data['source'] = storage_path('uploads/'.$filename);
        $mimeType = $sourceFile->getMimeType();
        
        $mediaLibraryData['format'] = $extension;
        $mediaLibraryData['uploaded_by'] = $this->user ? $this->user->id : null;
        $mediaLibraryData['filename'] = $filename;
        if(substr($mimeType, 0, 5) == 'image'){

            $cloudinary_uploaded_data = [];
            $cloudinaryHelper = new CloudinaryHelper();
            $cloudinary_uploaded_data = $cloudinaryHelper->uploadFile($data['source']);
            $mediaLibraryData['provider'] = 'cloudinary';
            $mediaLibraryData['path'] = $cloudinary_uploaded_data['secure_url'];
            $mediaLibraryData['type'] = 'image';

        } else {

            Storage::disk('s3')->put($data['key'],$data['source'],'public');
            $mediaLibraryData['provider'] = 's3';
            $mediaLibraryData['path'] = Storage::disk('s3')->url($data['key']);
            $mediaLibraryData['type'] = $extension;
            File::delete($data['source']);
        }
        
        if(array_key_exists('path', $mediaLibraryData)) {
            return MediaLibrary::create($mediaLibraryData);
        } else {
            return [];
        }

    }

}