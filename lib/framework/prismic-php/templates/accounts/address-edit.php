<form id="address-edit_<?php echo $index; ?>" class="ajax-form" method="POST" action="/v1/app/address/update/">
	<input type="hidden" name="type" value="address-edit" />
	<input type="hidden" name="address[id]" value="<?php echo $address->id; ?>" />
	<input type="hidden" name="token" value="<?php echo $_COOKIE["cat"]; ?>" />
	<div class="input-block errors">
		<p class="server-error"></p>
	</div>
	<div class="input-block-group">
		<div class="input-block">
			<label>First name</label>
			<input class="input validate" type="text" name="address[firstName]" value="<?php echo $address->firstName; ?>" />
		</div>
		<div class="input-block">
			<label>Last name</label>
			<input class="input" type="text" name="address[lastName]" value="<?php echo $address->lastName; ?>" />
		</div>
	</div>
	<div class="input-block">
		<label>Street address</label>
		<input class="input validate" type="text" name="address[address1]" value="<?php echo $address->address1; ?>" />
	</div>
	<div class="input-block-group">
		<div class="input-block">
			<label>Apt./Suite</label>
			<input class="input validate" type="text" name="address[address2]" value="<?php echo $address->address2; ?>" />
		</div>
		<div class="input-block">
			<label>Company</label>
			<input class="input" type="text" name="address[company]" value="<?php echo $address->company; ?>" />
		</div>
	</div>
	<div class="input-block-group">
		<div class="input-block select">
			<label>Country</label>
			<select class="input validate" name="address[country]">
				<option disabled>Select Country</option>
				<?php $options = returnCountryOptions($address->countryCodeV2); echo $options; ?>
			</select>
		</div>
		<div class="input-block">
			<label>City</label>
			<input class="input validate" type="text" name="address[city]" value="<?php echo $address->city; ?>" />
		</div>
	</div>
	<div class="input-block-group">
		<div class="input-block">
			<label>State/Province</label>
			<input class="input validate" type="text" name="address[province]" value="<?php echo $address->province; ?>" />
		</div>
		<div class="input-block">
			<label>Zip/Postal code</label>
			<input class="input validate" type="text" name="address[zip]" value="<?php echo $address->zip; ?>" />
		</div>
	</div>
	<div class="input-block checkbox">
		<input id="editAddress_<?php echo $index; ?>" class="input checkbox" type="checkbox" name="address[default]" />
		<label for="editAddress_<?php echo $index; ?>">Set as default</label>
	</div>
	<div class="input-block submit-block">
		<button type="submit" class="button submit">Save</button>
	</div>
</form>