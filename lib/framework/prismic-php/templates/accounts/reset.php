<?php
$document = $PAGE["document"];
$customerError = $PAGE["customerError"];
/* --------------- *
echo "<br><pre>";
print_r($customerError);
echo "</pre>";
/* --------------- */
/*
 *	Excepted URL String
 *	 - site.com/account/reset/?id=1000000888881&k=62eace4f2f428f94e0f5a87842f5595-1546891371
 *	 - In the Shopify reset email, add this at the top: {% assign resetURLArray = customer.reset_password_url | split: "/" %}
 *	 - In the Shopify reset email, add this to the button link: https://robingolf.com/account/reset/?id={{ resetURLArray[5] }}&k={{ resetURLArray[6] }}
 */
?>
<section class="container-120 logged-out" data-smooth>
    <div class="page-content">
        <h1>Reset Password</h1>
        <form id="account-login" class="ajax-form" method="POST" action="/v1/app/password/reset/">
            <input type="hidden" name="type" value="reset-password" />
            <input type="hidden" name="id" value="<?php if(isset($_GET["id"])){ echo $_GET["id"]; } ?>" />
            <input type="hidden" name="token" value="<?php if(isset($_GET["k"])){ echo $_GET["k"]; } ?>" />
            <div class="input-block errors">
                <?php if($customerError != ""){ ?><p class="server-error"><?php echo $customerError; ?></p><?php } ?>
            </div>
            <div class="input-block">
                <label></label>
                <input class="input validate" type="password" placeholder="New Password" name="customer[password]" autocomplete="off" autocorrect="off" autocapitalize="off" />
            </div>
            <div class="input-block">
                <label></label>
                <input class="input validate" type="password" placeholder="Confirm Password" name="customer[confirmPassword]" autocomplete="off" autocorrect="off" autocapitalize="off" />
            </div>
            <div class="input-block submit-block">
                <button type="submit" class="btn submit">Reset Password</button>
            </div>
        </form>
        <div class="bottom-links">
            <a href="/account/register/">Create Account</a>
            <div class="separator"></div>
            <a href="/account/login/">Login</a>
        </div>
    </div>
</section>

<footer data-smooth class="footer default">
    <?php include dirname(__DIR__) . "/parts/footer.php"; ?>
</footer>
