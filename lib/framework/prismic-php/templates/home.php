<?php
$document = $PAGE["document"];
$data = $document->data;
global $CONTENT;
use Prismic\Dom\RichText;
/* ---------------
echo "<pre>";
print_r($document);
echo "</pre>";w
/* --------------- */
?>
<h1>Guille's Syll iii.abus</h1>
<div class="aspect-ratio-tiles">
	<p>1 b. i. ii.</p>
	<div class="grid">
		<div class="tile"></div>
		<div class="tile"></div>
		<div class="tile"></div>
		<div class="tile"></div>
		<div class="tile"></div>
		<div class="tile"></div>
		<div class="tile"></div>
		<div class="tile"></div>
		<div class="tile"></div>
	</div>
</div>
<?php
/* ----- Home JSON+LD Schema ----- */
include(views_dir() . "/schema/schema-home.php"); ?>


