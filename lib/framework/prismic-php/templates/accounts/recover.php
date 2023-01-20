<?php
$document = $PAGE["document"];
$customerError = $PAGE["customerError"];
/* --------------- *
echo "<br><pre>";
print_r($customerError);
echo "</pre>";
/* --------------- */
?>
<section class="container-120 logged-out" data-smooth>
	<div class="page-content">
		<h1>Recover Password</h1>
		<form id="account-login" class="ajax-form" method="POST" action="/v1/app/password/recover/">
			<input type="hidden" name="type" value="recover-password" />
			<input type="hidden" name="token" value="recover-password" />
			<div class="input-block errors">
				<?php if($customerError != ""){ ?><p class="server-error"><?php echo $customerError; ?></p><?php } ?>
			</div>
			<div class="input-block">
				<label></label>
				<input class="input validate" type="email" placeholder="Email" name="customer[email]" autocomplete="email" autocorrect="off" autocapitalize="off" />
			</div>
			<div class="input-block submit-block">
				<button type="submit" class="button submit">Recover Password</button>
			</div>
		</form>
		<div class="bottom-links">
			<a href="/account/register/">Create Account</a>
			<div class="separator"></div>
			<a href="/account/login/">Login</a>
		</div>
	</div>
</section>