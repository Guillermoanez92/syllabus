<?php
global $BUILDINFO;
global $CONTENT;

if(!empty($BUILDINFO->lib->content_management)){
	$CONTENT->indices->indexContent($data);
}
?>