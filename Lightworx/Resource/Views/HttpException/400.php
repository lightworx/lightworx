<?php

echo '<div class="error_message">',$exception->getMessage(),'</div>';

if(strtolower(RUNNING_MODE)=='production'){return;}

foreach($traces as $trace)
{
	$code  = '<div class="debug_box">';
	$code .= '<h3>Stack Trace</h3>';
	$code .= '<div class="metainfo">';
	$code .= '<span class="meta_file">File:'.$trace['file'].'</span>';
	$code .= '<span class="meta_line">Line:'.$trace['line'].'</span>';
	$code .= array_key_exists('function',$trace) ? '<span class="meta_function">Function:'.$trace['function']."</span>" : '';
	$code .= array_key_exists('class',$trace) ? '<span class="meta_class">Class:'.$trace['class']."</span>" : '';
	$code .= '</div>';
	$code .= $trace['code'];
	$code .= '</div>';
	echo $code;
}