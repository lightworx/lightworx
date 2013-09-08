<?php

namespace Lightworx\Helper\ArrayHelper;

/**
 * The function iin_array would be checks if a value exists in an array,
 * and this function different with in_array are case-insensitive.
 * @param string $needle
 * @param array $haystack
 * @param boolean $strict defaults to false
 * @return boolean
 */
function iin_array($needle,array $haystack,$strict=false)
{
	return in_array(strtolower($needle),array_map('strtolower',$haystack),$strict);
}