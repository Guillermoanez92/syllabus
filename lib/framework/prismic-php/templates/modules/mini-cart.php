<div id="mini-cart">
	<div id="mini-cart-banner">
		<p id="mini-cart-header">Your Cart (<span class="cart-quantity"></span>)</p>
		<button class="mini-cart-trigger"><?php include(views_dir() . "/parts/svgs/icon-close.php"); ?></button>
	</div>
	<div id="mini-cart-full">
		<div id="mini-cart-products"></div>
		<div id="mini-cart-summary">
			<p>You&rsquo;ve unlocked <span class="cart-discounts discount"></span> in savings.</p>
			<a href="javascript:void(0);" class="btn cart-checkout">Total:&nbsp;<span class="cart-total"></span>&nbsp;| Checkout</a>
		</div>
	</div>
	<div id="mini-cart-empty">
		Empty Cart
	</div>
</div>
<div id="mini-cart-background" class="mini-cart-trigger"></div>