<?php

namespace Betalectic\FileManager\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use Betalectic\FileManager\Helpers\CloudinaryHelper;

class File extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [];

        $data['id'] = $this->uuid;
        $data['disk'] = $this->disk;
        $data['path'] = $this->path;
        $data['uploaded_by'] = $this->uploaded_by;
        $data['owner_id'] = $this->owner_id;
        $data['owner_type'] = $this->owner_type;
        $data['mime_type'] = $this->mime_type;
        $data['tags'] = $this->tags;
        $data['meta'] = $this->meta;
        $data['name'] = isset($this->meta['name']) ? $this->meta['name'] : '--';
        $data['icon'] = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAAAAABVicqIAAAACXBIWXMAABcRAAAXEQHKJvM/AAAAB3RJTUUH4wMSCTgDYPPwhgAAAKtJREFUaN7t07EOglAMRmHe/4E6SoyTsyE6CgnBiYQIO5tO3hZablTO2Zsv/9BizFABAgICAgICAvKriGidnxkQs+JCrEoauTR187G6tCpp5J68PVm3eBGT4kYsih8xKAGIrkQgqhKCaEoMoihBSFrxIO3r9w8it42Qd0eRahtkyoGMICAgICAgICAgICAgf4ZcH6a60oMsaPdIv6hhHRIXCAgICAgICMh3IzM0caWf/rMHBQAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAAASUVORK5CYII=";

        if($this->disk == 'cloudinary')
        {

            $cloudinaryHelper = new CloudinaryHelper();

            $img_sizes = [];

            if($request->has('img_sizes')){
                $img_sizes = explode(',', $request->get('img_sizes'));
            }

            $data['wh_100_100'] = $cloudinaryHelper->createUrl($this->path,100,100);
            $data['icon'] = $cloudinaryHelper->createUrl($this->path,100,100);

            foreach($img_sizes as $img_size)
            {
                list($w,$h) = explode("-", $img_size);
                $data['wh_'.$w.'_'.$h] = $cloudinaryHelper->createUrl($this->path,$w,$h);
            }

        }

        return $data;
        
    }
}
