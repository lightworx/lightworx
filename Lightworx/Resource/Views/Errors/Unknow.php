<div class="error_message">
	<?php echo $message;?> in file:<?php echo $file;?> at line:<?php echo $line;?>
</div>

<?php
if(strtolower(RUNNING_MODE)!='production')
{
	$debug  = '<div class="debug_box">';
	$debug .= '<h3>Issue information</h3>';
	$debug .= '<div class="metainfo">';
	$debug .= '<span class="meta_file">File:'.$file.'</span>';
	$debug .= '<span class="meta_line">Line:'.$line.'</span></div>';
	$debug .= $code;
	$debug .= '</div>';
	echo $debug;
}