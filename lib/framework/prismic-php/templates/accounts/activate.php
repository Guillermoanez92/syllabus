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
        <h1>Activate Account</h1>
        <form id="account-activate" method="POST" action="/account/login/">
            <input type="hidden" name="type" value="activate" />
            <input type="hidden" name="id" value="<?php if(isset($_GET["id"])){ echo $_GET["id"]; } ?>" />
            <input type="hidden" name="token" value="<?php if(isset($_GET["k"])){ echo $_GET["k"]; } ?>" />
            <div class="input-block errors">
                <?php if($customerError != ""){ ?><p class="server-error"><?php echo $customerError; ?></p><?php } ?>
            </div>
            <div class="input-block">
                <label></label>
                <input class="input validate" type="password" placeholder="Password" value="" name="customer[password]" autocomplete="current-password" />
            </div>
            <div class="input-block">
                <label></label>
                <input class="input validate" type="password" placeholder="Confirm Password" name="customer[confirmPassword]" autocomplete="off" autocorrect="off" autocapitalize="off" />
            </div>
            <div class="input-block submit-block">
                <button type="submit" class="button submit">Activate Account</button>
            </div>
        </form>
        <div class="bottom-links">
            <a href="/account/login/">Already Have An Account?</a>
        </div>
    </div>
</section>
