<?php
/* ----- Define Lowest / Highest Prices ----- */
$lowestPrice = 10000000000;
$highestPrice = 0;

foreach($product->variants as $v){

	if($v->price > $highestPrice){
		$highestPrice = $v->price;
	}

	if($v->price < $lowestPrice){
		$lowestPrice = $v->price;
	}
}

/* ----- Product Schema ----- */
?>
<script type="application/ld+json">
{
	"@context": "https://schema.org/",
	"@type": "Product",
	"name": "<?php echo str_replace('"', "", $PAGE["title"]); ?>",
	"image": [
		<?php
		foreach($product->images as $i => $im){
			if($i !== 0){ echo ","; }
		?>"<?php echo $im->src; ?>"
		<?php } ?>
	],
	"description": "<?php echo str_replace('"', "", $PAGE["description"]); ?>",
	"brand": {
		"@type": "Thing",
		"name": "<?php echo $STOREFRONT->shop->data->shop->name; ?>"
	},
	<?php if(count($product->variants) === 1){ ?>
	"sku": "<?php echo $product->variants[0]->sku; ?>",
	<?php } ?>
	<?php if(!is_null($reviewRating)){ ?>
		"aggregateRating": {
			"@type": "AggregateRating",
			"ratingValue": "<?php echo $reviewRating; ?>",
			"reviewCount": "<?php echo $reviewCount; ?>"
		},
	<?php } ?>
	"offers": {
		"@type": "AggregateOffer",
		"lowPrice": "<?php echo $lowestPrice; ?>",
		"highPrice": "<?php echo $highestPrice; ?>",
		"priceCurrency": "<?php echo $STOREFRONT->shop->data->shop->currencyCode; ?>",
		"url": "<?php echo PROTOCOL . HOST; ?>/products/<?php echo slugify($product->product_type); ?>/<?php echo $product->handle; ?>/",
		"seller": {
			"@type": "Organization",
			"name": "<?php echo str_replace('"', "", $product->vendor); ?>"
		},
		"offerCount": "<?php echo count($product->variants); ?>",
		"offers": [
			<?php
			foreach($product->variants as $i => $v){
				if($i !== 0){ echo ","; }
			?>{
				"@type": "Offer",
				"category": "<?php echo str_replace('"', "", $product->product_type); ?>",
				"url": "<?php echo PROTOCOL . HOST; ?>/products/<?php echo slugify($product->product_type); ?>/<?php echo $product->handle; ?>/?variant=<?php echo $v->id; ?>",
				"priceCurrency": "<?php echo $STOREFRONT->shop->data->shop->currencyCode; ?>",
				"price": "<?php echo $v->price; ?>",
				"sku": "<?php echo $v->sku; ?>",
				"availability": "<?php if($variant->inventory_policy === "deny" && $variant->inventory_quantity >= 1 || $variant->inventory_policy !== "deny"){ echo "http://schema.org/InStock"; } else { echo "http://schema.org/OutOfStock"; } ?>"
			}<?php } ?>
		]
	}
}
</script>