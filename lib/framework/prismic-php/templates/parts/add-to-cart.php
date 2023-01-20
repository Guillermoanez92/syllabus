<?php
if(!empty($product)){

	$productData = new stdClass();
	$productData->product_id = $product->id;
	$productData->variant_id = (isset($_GET) && isset($_GET["variant"])) ? $_GET["variant"] : $product->variants[0]->id;
	$productData->variant = $product->variants[0]->title;
	$productData->sku = $product->variants[0]->sku;
	$productData->category = str_replace("'", "’", $product->product_type);
	$productData->name = str_replace("'", "’", $product->title);
	$productData->brand = (isset($product->vendor)) ? $product->vendor : "Super Coffee";
	$productData->price = $product->variants[0]->price;
	$productData->currency = "USD";
	$productData->url = "/products/" . slugify($product->product_type) . "/" . $product->handle . "/";
	$productData->image_url = (!empty($product->image)) ? $product->image->src : "";
?>
<form class="add-to-cart-form" data-product-id="<?php echo $product->id; ?>" method="post" action="/v1/app/cart/add/" enctype="multipart/form-data">
	<input type="hidden" name="quantity" value="1" />
	<input type="hidden" name="data" value='<?php echo json_encode($productData); ?>' />

	<?php foreach($product->options as $key => $option){ ?>
		<div class="hidden option option-<?php echo $key; ?>"><?php echo $option->name; ?></div>
	<?php } ?>

	<?php
	/**
	 * Increment Counter
	 *
	 *	- This can be moved, but needs to maintain
	 *	  the same hierarchy within the HTML
	 */
	?>
	<div class="increment-wrapper">
		<button name="decrease item quantity" aria-label="decrease item quantity" type="button" class="increment minus disabled" data-type="minus">-</button>
		<div class="count">1</div>
		<button name="increase item quantity" aria-label="increase item quantity" type="button" class="increment plus" data-type="plus">+</button>
	</div>

	<?php
	/**
	 * Product Select
	 *
	 *	- Used to determind variant selection targetted
	 *	  in javascript
	 */
	if(isset($product->variants) && count($product->variants) > 0){
	?>
	<select id="product-<?php echo $product->id; ?>" class="product-select<?php if(count($product->variants) <= 1){ ?> hidden<?php } ?>" name="id">
		<?php 
		$select = false;

		foreach($product->variants as $key => $variant){

			$imageId = $variant->image_id;
			$image = "";

			foreach($product->images as $key => $img){

				if($img->id == $imageId){

					$image = $img->src;
					break;
				}
			}

			if($variant->inventory_policy == "continue" || $variant->inventory_quantity > 0){
				$available = true;
			} else {
				$available = false;
			}

			$variantId = base64_encode("gid://shopify/ProductVariant/" . $variant->id);
			
			if($variant->compare_at_price){ 
		?>
				<option <?php if($select === false && $available === true){ $select = true; ?> selected="selected"<?php } ?> data-raw-id="<?php echo $variant->id; ?>" value="<?php echo $variantId; ?>" data-original-price="<?php echo $variant->compare_at_price; ?>" data-inventory="<?php if($available === false){ ?>0<?php } else { echo $variant->inventory_quantity; } ?>"><?php echo $variant->title; ?>||<?php echo $variant->price; ?>||<?php echo shopifyReturnImage($image, "1680x"); ?>||<?php echo shopifyReturnImage($image, "768x"); ?></option>
			<?php } else { ?>
				<option <?php if($select === false && $available === true){ $select = true; ?> selected="selected"<?php } ?> data-raw-id="<?php echo $variant->id; ?>" value="<?php echo $variantId; ?>" data-inventory="<?php if($available === false){ ?>0<?php } else { echo $variant->inventory_quantity; } ?>"><?php echo $variant->title; ?>||<?php echo $variant->price; ?>||<?php echo shopifyReturnImage($image, "1680x"); ?>||<?php echo shopifyReturnImage($image, "768x"); ?></option>
			<?php } ?>
		<?php } ?>
	</select>
	<?php } ?>
	<div class="fake-selects-wrapper"></div>
	<div class="subscription-wrapper" data-price="<?php echo $product->variants[0]->price; ?>"></div>

	<?php
	/**
	 * Add to cart button
	 */
	?>
	<?php if($select === true){ ?>
		<button name="add to bag" aria-label="add to bag" class="btn btn--orange add-to-cart" type="submit">Add To Bag</button>
	<?php } else { ?>
		<button class="btn btn--orange disabled">Sold Out</button>
	<?php } ?>
</form>
<?php } ?>