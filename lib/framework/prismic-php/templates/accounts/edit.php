<h2>Account</h2>
<div class="tab-summary">
	<p><?php echo $customer->display_name; ?></p>
	<p><?php echo $customer->email; ?></p>
	<?php if(isset($customer->phone) && $customer->phone !== ""){ ?>
		<p><?php echo $customer->phone; ?></p>
	<?php } ?>
</div>
<h3>Edit Account</h3>
<form id="account-update" class="ajax-form" method="POST" action="/v1/app/customer/update/">
	<input type="hidden" name="token" value="<?php echo $_COOKIE["cat"]; ?>" />
	<input type="hidden" name="customer[id]" value="<?php echo $customer->id; ?>" />
	<div class="input-block errors">
		<p class="server-error"></p>
	</div>
	<div class="input-block-group">
		<div class="input-block">
			<label>First name</label>
			<input class="input validate" type="text" placeholder="First Name" name="customer[firstName]" value="<?php echo $customer->first_name; ?>" />
		</div>
		<div class="input-block">
			<label>Last name</label>
			<input class="input" type="text" placeholder="Last Name" name="customer[lastName]" value="<?php echo $customer->last_name; ?>" />
		</div>
	</div>
	<div class="input-block-group">
		<div class="input-block">
			<label>Email</label>
			<input class="input validate" type="email" placeholder="Email" name="customer[email]" value="<?php echo $customer->email; ?>" />
		</div>
		<div class="input-block">
			<label>Phone</label>
			<input class="input" type="text" placeholder="Phone" name="customer[phone]" value="<?php echo $customer->phone; ?>" />
		</div>
	</div>
	<div class="input-block-group">
		<div class="input-block">
			<label>New password</label>
			<input class="input validate" type="password" placeholder="New Password" name="customer[password]" autocomplete="off" autocorrect="off" autocapitalize="off" />
		</div>
		<div class="input-block">
			<label>Confirm password</label>
			<input class="input validate" type="password" placeholder="Confirm Password" name="customer[confirmPassword]" autocomplete="off" autocorrect="off" autocapitalize="off" />
		</div>
	</div>
	<div class="input-block checkbox">
		<input id="acceptsMarketing" type="checkbox" name="customer[acceptsMarketing]" <?php if($customer->accepts_marketing && $customer->accepts_marketing == true){ echo "checked"; } ?> />
		<label for="acceptsMarketing">Accepts Marketing</label>
	</div>
	<div class="input-block submit-block">
		<button type="submit" class="button submit">Save</button>
		<p class="submit-message"></p>
	</div>
</form>