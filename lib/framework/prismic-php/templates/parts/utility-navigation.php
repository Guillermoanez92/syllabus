<nav id="utility-navigation">
	<ul>
		<li><a href="<?php echo (!empty($_COOKIE["cat"])) ? "/account/" : "/account/login/"; ?>"><?php include(views_dir() . "/parts/svgs/icon-account.php"); ?></a></li>
		<li><a href="javascript:void(0);" class="mini-cart-trigger"><?php include(views_dir() . "/parts/svgs/icon-cart.php"); ?><span class="cart-quantity"></span></a></li>
	</ul>
</nav>