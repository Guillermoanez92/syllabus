<?php
/* ----- Home Schema ----- */
?>
<script type="application/ld+json">
{
	"@context": "http://schema.org",
	"@type": "WebPage",
	"name": "<?php echo $title; ?>",
	"description": "<?php echo $description; ?>",
	"url": "<?php echo PROTOCOL . HOST; ?>/",
	"publisher": {
		"@type": "Organization",
		"name": "<?php echo SITE_TITLE; ?>"
	}
}
</script>