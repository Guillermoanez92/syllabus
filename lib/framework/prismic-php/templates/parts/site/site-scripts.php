<?php 
/*  
	Site Scripts
-------------------------------------------------- *
	Need to embed anything like Hotjar, or Klaviyo?
	Put it in here
-------------------------------------------------- */
?>
<?php
/* --- IE Polyfill for Promise Script --- */
if(preg_match("~MSIE|Internet Explorer~i", $_SERVER["HTTP_USER_AGENT"]) || preg_match("~Trident/7.0(; Touch)?; rv:11.0~",$_SERVER["HTTP_USER_AGENT"])){
?>
<script type="text/javascript" src="https://cdn.jsdelivr.net/bluebird/latest/bluebird.min.js"></script>
<?php } ?>