<?php

return [
	'cloudinary_cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
	'cloudinary_api_key' => env('CLOUDINARY_API_KEY'),
	'cloudinary_api_secret' => env('CLOUDINARY_API_SECRET'),
	'file_prefix' => '',
	'file-system' => 's3', // Options: s3
	'save_images_to' => 'cloudinary', // Options: cloudinary, s3,
	'access' => 'public',
	'soft_delete' => false
];
