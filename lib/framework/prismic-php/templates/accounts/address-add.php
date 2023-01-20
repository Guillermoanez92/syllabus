<form id="address-create" class="ajax-form" method="POST" action="/v1/app/address/create/">
	<input type="hidden" name="type" value="address-create" />
	<input type="hidden" name="token" value="<?php echo $_COOKIE["cat"]; ?>" />
	<div class="input-block errors">
		<p class="server-error"></p>
	</div>
	<div class="input-block-group">
		<div class="input-block">
			<label>First name</label>
			<input class="input validate" type="text" name="address[firstName]" value="<?php echo $customer->first_name; ?>" />
		</div>
		<div class="input-block">
			<label>Last name</label>
			<input class="input" type="text" name="address[lastName]" value="<?php echo $customer->last_name; ?>" />
		</div>
	</div>
	<div class="input-block">
		<label>Street address</label>
		<input class="input validate" type="text" name="address[address1]" />
	</div>
	<div class="input-block-group">
		<div class="input-block">
			<label>Apt./Suite</label>
			<input class="input validate" type="text" name="address[address2]" />
		</div>
		<div class="input-block">
			<label>Company</label>
			<input class="input" type="text" name="address[company]" />
		</div>
	</div>
	<div class="input-block-group">
		<div class="input-block select">
			<label>Country</label>
			<select class="input validate" name="address[country]">
				<option disabled>Select Country</option>
				<?php $options = returnCountryOptions(); echo $options; ?>
			</select>
		</div>
		<div class="input-block">
			<label>City</label>
			<input class="input validate" type="text" name="address[city]" />
		</div>
	</div>
	<div class="input-block-group">
		<div class="input-block">
			<label>State/Province</label>
			<input class="input validate" type="text" name="address[province]" />
		</div>
		<div class="input-block">
			<label>Zip/Postal code</label>
			<input class="input validate" type="text" name="address[zip]" />
		</div>
	</div>
	<?php if(isset($customer->default_address->address1)){ ?>
		<div class="input-block checkbox">
			<input id="editDefaultAddress" class="input checkbox" type="checkbox" name="address[default]" />
			<label for="editDefaultAddress">Set as default</label>
		</div>
	<?php } ?>
	<div class="input-block submit-block">
		<button type="submit" class="button submit">Save</button>
	</div>
</form>