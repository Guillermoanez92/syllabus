<?php
$document = $PAGE["document"];
$customer = $PAGE["customer"];
/* --------------- */
echo "<br><pre data-smooth>";
print_r($customer);
echo "</pre>";
/* --------------- */
?>
<section class="logged-in" data-smooth>
	<div class="logged-in-header">
		<div>
			<span class="eyebrow">Hello,</span>
			<h1><?php echo $customer->first_name; ?></h1>
		</div>
		<a href="/account/login/?logout=true" data-router-disabled>Logout</a>
	</div>
	<div class="tab-anchors">
		<ul>
			<li class="tab-anchor active">Order History</li>
			<li class="tab-anchor">Account Details</li>
			<li class="tab-anchor">Addresses</li>
		</ul>
	</div>
	<div class="all-tabs container-120">
		<div class="tab orders active">
			<?php include dirname(__DIR__) . "/accounts/orders.php"; ?>
		</div>
		<div class="tab profile">
			<?php include dirname(__DIR__) . "/accounts/edit.php"; ?>
		</div>
		<div class="tab addresses">
			<?php include dirname(__DIR__) . "/accounts/addresses.php"; ?>
		</div>
	</div>
</section>