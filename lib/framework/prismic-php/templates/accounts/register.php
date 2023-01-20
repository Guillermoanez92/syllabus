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
    <h1>Register</h1>
    <form id="account-register" class="ajax-form" method="POST" action="/v1/app/customer/create/">
      <div class="input-block errors">
          <?php if($customerError != ""){ ?><p class="server-error"><?php echo $customerError; ?></p><?php } ?>
      </div>
      <div class="input-block">
        <label></label>
        <input class="input validate" type="email" placeholder="Email" name="customer[email]" />
      </div>
      <div class="input-block">
        <label></label>
        <input class="input validate" type="text" placeholder="First Name" name="customer[firstName]" />
      </div>
      <div class="input-block">
        <label></label>
        <input class="input validate" type="text" placeholder="Last Name" name="customer[lastName]" />
      </div>
        <?php /* CURRENTLY EXCLUDED BECAUSE SHOPIFY REQUIRES PROPER FORMATTING: E.164 standard
	<div class="input-block">
		<label></label>
		<input class="input" type="text" placeholder="Phone" name="customer[phone]" value="<?php echo $customer->phone; ?>" />
	</div> */ ?>
      <div class="input-block">
        <label></label>
        <input class="input validate" type="password" placeholder="New Password" name="customer[password]" autocomplete="off" autocorrect="off" autocapitalize="off" />
      </div>
      <div class="input-block">
        <label></label>
        <input class="input validate" type="password" placeholder="Confirm Password" name="customer[confirmPassword]" autocomplete="off" autocorrect="off" autocapitalize="off" />
      </div>
      <div class="input-block checkbox">
        <input id="acceptsMarketing" class="input checkbox" type="checkbox" name="customer[acceptsMarketing]" />
        <label for="acceptsMarketing">Accepts Marketing</label>
      </div>
      <div class="input-block submit-block">
        <button type="submit" class="btn submit">Register</button>
      </div>
    </form>
    <div class="bottom-links">
      <a href="/account/login/">Already Have An Account?</a>
    </div>
  </div>
</section>

<footer data-smooth class="footer default">
    <?php include dirname(__DIR__) . "/parts/footer.php"; ?>
</footer>
