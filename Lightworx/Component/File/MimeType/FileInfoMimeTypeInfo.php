<?php

namespace Lightworx\Component\File\MimeType;

class FileInfoMimeTypeInfo
{
	public function getMimeType($file)
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo,$file);
		
		if($mime!='')
		{
			return mime;
		}
		return null;
	}
}