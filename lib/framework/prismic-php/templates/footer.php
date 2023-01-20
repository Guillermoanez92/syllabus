		</div>
	</main>
	<?php include(views_dir() . "/parts/footer.php"); ?>
<?php
/* --------------- *
echo "<br><pre>";
print_r($settings);
echo "</pre>";
/* --------------- */
if(isset($settings->uid)){
?>
<div id="site-data" data-cid="<?php if(isset($STOREFRONT->customer) && isset($STOREFRONT->customer->id)){ echo $STOREFRONT->customer->id; } ?>" data-gtag="<?php echo (isset($settings->data) && isset($settings->data->ga_id)) ? $settings->data->ga_id : ""; ?>" data-fb-app="<?php echo (isset($settings->data) && isset($settings->data->fb_app_id)) ? $settings->data->fb_app_id : ""; ?>" data-fb-pixel="<?php echo (isset($settings->data) && isset($settings->data->fb_pixel)) ? $settings->data->fb_pixel : ""; ?>" data-asset-path="<?php echo $ASSETPATH; ?>"></div>
<?php
}
if(isset($settings->uid) && isset($settings->data->ga_id) && $settings->data->ga_id !== ""){ ?>
<!-- Google Analytics -->
<script>
(function(i,s,o,g,r,a,m){i["GoogleAnalyticsObject"]=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
ga("create", "<?php echo $settings->data->ga_id; ?>", "auto");
ga("send", "pageview");
</script>
<?php } ?>
<!-- Deferred CSS -->
<script type="text/javascript">
var stylesheet = document.createElement("link");
stylesheet.href = "<?php echo $ASSETPATH; ?>main.css?v=<?php echo $BUILDINFO->date; ?>";
stylesheet.rel = "stylesheet";
stylesheet.type = "text/css";
document.getElementsByTagName("head")[0].appendChild(stylesheet);
</script>

<script type="text/javascript" src="<?php echo $ASSETPATH; ?>main.js?v=<?php echo $BUILDINFO->date; ?>" defer></script>
</body>
