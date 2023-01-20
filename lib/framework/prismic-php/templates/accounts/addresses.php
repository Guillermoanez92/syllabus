<?php
$document = $PAGE["document"];
$customer = $PAGE["customer"];
/* --------------- *
echo "<br><pre>";
print_r($customer);
echo "</pre>";
/* --------------- */
?>
<?php if(isset($customer->default_address->zip)){ ?>
	<div class="tab-summary">
		<h2>Addresses</h2>
		<div class="all-addresses">
			<span class="default-eyebrow">Default address</span>
			<div class="address default">
				<p>
					<?php echo $customer->default_address->firstName . " " . $customer->default_address->lastName; ?><br>
					<?php foreach($customer->default_address->formatted as $line){ ?>
						<?php echo $line; ?><br>
					<?php } ?>
				</p>
				<div class="edit-triggers">
					<p class="edit-address">Edit address</p>
					<a class="address-delete" href="javascript:void(0);" data-address-id="default">Delete Address</a>
				</div>
			</div>
			<?php
			if(count($customer->addresses) > 0){

				foreach($customer->addresses as $key => $address){
			?>
				<div class="address">
					<p>
						<?php echo $address->firstName . " " . $address->lastName; ?><br>
						<?php foreach($address->formatted as $line){ ?>
							<?php echo $line; ?><br>
						<?php } ?>
					</p>
					<div class="edit-triggers">
						<p class="edit-address">Edit address</p>
						<a class="address-delete" href="javascript:void(0);" data-address-id="<?php echo $key; ?>">Delete Address</a>
					</div>
				</div>
			<?php
				}
			}
			?>
		</div>
	</div>
<?php } ?>
<div class="address-forms">
	<p class="errors"></p>
	<div class="add-form">
		<h3>Add New Address</h3>
		<?php include __DIR__ . "/address-add.php"; ?>
	</div>

	<div class="edit-forms">
		<h3>Edit Address <div class="close"><span></span><span></span></div></h3>
		<form id="address-edit_default" class="ajax-form" method="POST" action="/v1/app/address/update/">
			<input type="hidden" name="type" value="address-edit" />
			<input type="hidden" name="address[id]" value="<?php echo $customer->default_address->id; ?>" />
			<input type="hidden" name="token" value="<?php echo $_COOKIE["cat"]; ?>" />
			<div class="input-block errors">
				<p class="server-error"></p>
			</div>
			<div class="input-block-group">
				<div class="input-block">
					<label>First name</label>
					<input class="input validate" type="text" name="address[firstName]" value="<?php echo $customer->default_address->firstName; ?>" />
				</div>
				<div class="input-block">
					<label>Last name</label>
					<input class="input" type="text" name="address[lastName]" value="<?php echo $customer->default_address->lastName; ?>" />
				</div>
			</div>
			<div class="input-block">
				<label>Street address</label>
				<input class="input validate" type="text" name="address[address1]" value="<?php echo $customer->default_address->address1; ?>" />
			</div>
			<div class="input-block-group">
				<div class="input-block">
					<label>Apt./Suite</label>
					<input class="input validate" type="text" name="address[address2]" value="<?php echo $customer->default_address->address2; ?>" />
				</div>
				<div class="input-block">
					<label>Company</label>
					<input class="input" type="text" name="address[company]" value="<?php echo $customer->default_address->company; ?>" />
				</div>
			</div>
			<div class="input-block-group">
				<div class="input-block select">
					<label>Country</label>
					<select class="input validate" name="address[country]">
						<option disabled>Select Country</option>
						<?php $options = returnCountryOptions($customer->default_address->countryCodeV2); echo $options; ?>
					</select>
				</div>
				<div class="input-block">
					<label>City</label>
					<input class="input validate" type="text" name="address[city]" value="<?php echo $customer->default_address->city; ?>" />
				</div>
			</div>
			<div class="input-block-group">
				<div class="input-block">
					<label>State/Province</label>
					<input class="input validate" type="text" name="address[province]" value="<?php echo $customer->default_address->province; ?>" />
				</div>
				<div class="input-block">
					<label>Zip/Postal code</label>
					<input class="input validate" type="text" name="address[zip]" value="<?php echo $customer->default_address->zip; ?>" />
				</div>
			</div>
			<div class="input-block submit-block">
				<button type="submit" class="button submit">Save</button>
			</div>
		</form>

		<?php
		if(count($customer->addresses) > 0){
			foreach($customer->addresses as $index => $address){
				include __DIR__ . "/address-edit.php";
			}
		}
		?>
	</div>
</div>