<?php

namespace Lightworx\Component\File\MimeType;

class FileBinaryMimeTypeInfo
{
	public function getMimeType($file)
	{
		if(function_exists('system') and strtolower(PHP_OS)!="windows")
		{
			ob_start();
			system("file -b --mime ".escapeshellarg($file));
			$mime = ob_get_contents();
			ob_clean();

			if($mime!="" and ($position = strpos($mime,';'))!==false)
			{
				return substr($mime,0,$position);
			}
		}
		return null;
	}
}