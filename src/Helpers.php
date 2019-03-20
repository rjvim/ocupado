<?php

namespace Betalectic\FileManager;

class Helpers {

	public static function getDynamicController() {

		if(class_exists(\Laravel\Lumen\Routing\Controller::class))
		{
			return 'Laravel\Lumen\Routing\Controller';
		}

		if(class_exists(\Illuminate\Routing\Controller::class))
		{
			return 'Illuminate\Routing\Controller';
		}

	}

}

