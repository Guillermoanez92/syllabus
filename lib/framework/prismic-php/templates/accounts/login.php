<?php
$document = $PAGE["document"];
$customerError = $PAGE["customerError"];
/* --------------- *
echo "<br><pre>";
print_r($customerError);
echo "</pre>";
/* --------------- */
?>
<section class="container logged-out" data-smooth>
	<div class="page-content">
		<h1>Login</h1>
		<form id="account-login" class="account-form login" method="POST" action="/account/login/">
			<input type="hidden" name="type" value="login" />
			<div class="input-block errors">
				<?php if($customerError != ""){ ?><p class="server-error"><?php echo $customerError; ?></p><?php } ?>
			</div>
			<div class="input-block">
				<label></label>
				<input class="input validate" type="email" placeholder="Email" name="customer[email]" autocomplete="email" autocorrect="off" autocapitalize="off" />
			</div>
			<div class="input-block">
				<label></label>
				<input class="input validate" type="password" placeholder="Password" value="" name="customer[password]" autocomplete="current-password" />
			</div>
			<div class="input-block submit-block">
				<button type="submit" class="button submit">Login</button>
			</div>
		</form>
		<div class="bottom-links">
			<a href="/account/register/">Create Account</a>
			<div class="separator"></div>
			<a href="/account/recover/">Forgot Password?</a>
		</div>
	</div>
</section>