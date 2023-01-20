<?php
global $BUILDINFO;
global $STOREFRONT;

if(!empty($BUILDINFO->lib->ecommerce)){
	$STOREFRONT->indices->indexCollection($data);
}
?>