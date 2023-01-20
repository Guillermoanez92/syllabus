<?php
$document = $PAGE["document"];
$customer = $PAGE["customer"];
$orders = $PAGE["orders"];
/* --------------- *
echo "<br><pre>";
print_r($orders);
echo "</pre>";
/* --------------- */
?>
<h2>Your orders</h2>
<?php
if(count($customer->orders) > 0){

    foreach($orders as $order){
?>
	<article class="drawer">
		<div class="drawer-label">
			<div class="line-item-left">
				<p>Order details<?php // echo $order->orderNumber; ?></p>
			</div>
			<div class="line-item-right">
				<div class="date"><?php
				$dateSubstr = substr($order->processedAt, 0, strpos($order->processedAt, "T"));
				echo $dateSubstr; ?></div>
				<div class="price">$<?php echo $order->totalPrice; ?></div>
			</div>
		</div>
		<div class="drawer-items">
			<div class="labels">
				<p class="first">Product</p>
				<div class="line-item-right">
					<p>Quantity</p>
					<p class="last">Price</p>
				</div>
			</div>
			<?php foreach($order->lineItems->edges as $lineItem){ ?>
				<article class="line-item <?php echo $productType ?>">
					<div class="line-item-left">
						<div class="thumbnail-wrapper">
							<img class="thumbnail preload-critical" data-preload-desktop="<?php echo $lineItem->variant->image->src; ?>" data-preload-mobile="<?php echo $lineItem->variant->image->src; ?>" />
						</div>
						<div class="wrapper">
							<h1><?php echo $lineItem->title; ?></h1>
							<p>The essentials</p>
							<p><?php echo $setIncludes; ?></p>
						</div>
					</div>
					<div class="line-item-right">
						<div class="quantity increment-wrapper">
							<div class="count-wrapper"><span class="count"><?php echo $lineItem->quantity; ?></span></div>
						</div>
						<div class="price">
							<p class="product-price"><?php
							$format = numfmt_create("en_US", NumberFormatter::CURRENCY);
							$subtotal = (int)$lineItem->quantity * (int)$lineItem->variant->price;
							echo numfmt_format_currency($format, $subtotal, "USD"); ?></p>
						</div>
					</div>
				</article>
			<?php } ?>
			<div class="bottom-wrapper">
			<div class="shipping-address">
			<h4>Shipping</h4>
			<p>
			<?php echo $order->shippingAddress->name; ?><br>
			<?php foreach($order->shippingAddress->formatted as $line){ ?>
			<?php echo $line; ?><br>
			<?php } ?>
			</p>
			</div>
			<div class="summary-checkout">
			<div class="checkout-item">
			<div class="item">Subtotal</div>
			<div class="total">$<?php echo $order->subtotalPrice; ?></div>
			</div>
			<div class="checkout-item">
			<div class="item">Shipping</div>
			<div class="total">$<?php echo $order->totalShippingPrice; ?></div>
			</div>
			<div class="checkout-item">
			<div class="item">Tax</div>
			<div class="total">$<?php echo $order->totalTax; ?></div>
			</div>
			<div class="checkout-item">
			<div class="item">Total Price</div>
			<div class="total">$<?php echo $order->totalPrice; ?></div>
			</div>
			</div>
			</div>
		</div>
	</article>
<?php
	}
} else {
?>
	<p class="no-orders-text">Currently no orders to display</p>
<?php } ?>