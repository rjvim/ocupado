<?php

namespace Betalectic\FileManager\Helpers;
use Illuminate\Support\Facades\File;

class CloudinaryHelper {

    public function createUrl($path, $w = 100, $h = 100)
    {

        $ext = pathinfo(basename($path), PATHINFO_EXTENSION);
        $path = str_replace('.'.$ext, '', basename($path));

        return cloudinary_url($path,[
            'width'=> $w,
            'height'=> $h,
            'secure' => true,
            "crop"=> "thumb",
        ]);
    }

    public function uploadFile($localPath)
    {
        $result = \Cloudinary\Uploader::upload($localPath);

        unlink($localPath);

        return $result;
    }

}