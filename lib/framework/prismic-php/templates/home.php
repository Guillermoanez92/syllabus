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
<h1>Guille's Syllabus</h1>
<div class="aspect-ratio-tiles" style="display: none">
	<p>1 b</p>
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
<div class="input-elements">
	<p>1 C</p>
	<form class="input-wrapper">
		<div class="input-block">
			<label>Name</label>
			<input type="text" name="name" id="name" placeholder="Name">
		</div>
		<div class="input-block">
			<label>Email</label>
			<input type="email" name="email" id="email" placeholder="Email">
		</div>
		<div class="input-block">
			<textarea type="text" name="textarea" id="textarea" placeholder="textarea"></textarea>
		</div>
		<div class="input-block checkbox">
			<input id="checkbox" class="input checkbox" type="checkbox" name="checkbox" checked/>
		</div>
	</form>
</div>
<?php
/* ----- Home JSON+LD Schema ----- */
include(views_dir() . "/schema/schema-home.php"); ?>


