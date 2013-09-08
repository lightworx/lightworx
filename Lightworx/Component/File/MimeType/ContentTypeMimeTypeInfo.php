<?php

namespace Lightworx\Component\File\MimeType;

class ContentTypeMimeTypeInfo
{
	public function getMimeType($file)
	{
		if(function_exists('mime_content_type'))
		{
			return mime_content_type($this->file);
		}
		return null;
	}
}