<?php
global $STOREFRONT;

$document = $PAGE["document"];
$cart = $PAGE["cart"];
$checkout = $PAGE["checkout"];
/* --------------- *
echo "<br><pre>";
print_r($document);
echo "</pre>";
/* --------------- */
/* --------------- *
echo "<br><pre>";
print_r($cart);
echo "</pre>";
/* --------------- */
if($cart && count($cart) > 0){
?>

<form id="cart-form" class="cart-form" method="post" action="/v1/app/cart/update/" enctype="multipart/form-data">
	<input type="hidden" name="type" value="update-cart" />
	<input type="hidden" name="token" value="update-cart" />
	<div class="labels">
		<div class="thumbnail">Product</div>
		<div class="title"></div>
		<div class="quantity">Quantity</div>
		<div class="price">Price</div>
		<div class="total">Total</div>
		<div class="actions"></div>
	</div>
	<?php 
	$total = 0;
	foreach($cart as $key => $item){ 
		$subtotal = (int)$item->quantity * floatval($item->price);
		$total += $subtotal;
	?>
		<article class="line-item" data-id="<?php echo $item->variant_id; ?>" data-quantity="<?php echo $item->quantity; ?>">
			<div class="hidden visuallyhidden">
				<input type="hidden" name="[cart][<?php echo $key; ?>][variantId]" value="<?php echo $item->variant_id; ?>" />
				<?php foreach($item->attributes as $name => $val){ ?>
					<input type="hidden" name="[cart][<?php echo $key; ?>][properties][<?php echo $name; ?>]" value='<?php echo $val; ?>' />
				<?php } ?>
				<input type="number" name="[cart][<?php echo $key; ?>][quantity]" value="<?php echo $item->quantity; ?>" />
			</div>
			<img class="thumbnail preload" data-preload-desktop="<?php echo $item->image; ?>" data-preload-mobile="<?php echo $item->image; ?>" />
			<div class="title">
				<h1><?php echo $item->product_title; ?></h1>
				<h2></h2>
			</div>
			<div class="quantity increment-wrapper">
				<button name="decrease item quantity" aria-label="decrease item quantity" type="button" class="increment" data-type="minus">-</button>
				<span class="count"><?php echo $item->quantity; ?></span>
				<button name="increase item quantity" aria-label="increase item quantity" type="button" class="increment" data-type="plus">+</button>
			</div>
			<div class="price">
				<p class="product-price"><?php echo $STOREFRONT->format->formatCurrency($item->price, $STOREFRONT->shop->data->shop->currencyCode); ?></p>
			</div>
			<div class="total">
				<p class="product-total"><?php echo $STOREFRONT->format->formatCurrency($subtotal, $STOREFRONT->shop->data->shop->currencyCode); ?></p>
			</div>
			<div class="actions">
				<button name="remove item" aria-label="remove item" type="button" class="cart-remove">Remove X</button>
			</div>
		</article>
	<?php } ?>
	<div class="cart-summary">
		<div class="thumbnail"></div>
		<div class="title"></div>
		<div class="quantity"></div>
		<div class="price">Total:</div>
		<div class="total cart-total"><?php echo $STOREFRONT->format->formatCurrency($total, $STOREFRONT->shop->data->shop->currencyCode); ?></div>
		<div class="actions"></div>
	</div>
	<?php if($checkout && isset($checkout->url)){ ?>
		<a href="<?php echo $checkout->url; ?>" class="btn">Checkout</a>
	<?php } ?>
</form>

<?php } else { ?>

<h1>Your cart is empty</h1>

<?php } ?>