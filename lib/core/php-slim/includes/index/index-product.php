<?php
global $BUILDINFO;
global $STOREFRONT;

if(!empty($BUILDINFO->lib->ecommerce) && !empty($BUILDINFO->lib->ecommerce->platform)){
	$STOREFRONT->indices->indexProduct($data);
}
?>