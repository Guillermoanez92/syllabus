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
<div class="aspect-ratio-tiles">
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
<div class="css-animation">
	<p>2 b</p>
	<div class="container">
		<div class="container-inner">
			<?php foreach($data->div_repeater as $block){ ?>
				<div class="empty-div">

				</div>
			<?php } ?>
		</div>
	</div>

</div>
<div class="css-animation perspective">
	<p>2 c</p>
	<div class="container">
		<div class="container-inner">
			<?php foreach($data->div_repeater as $block){ ?>
				<div class="empty-div">

				</div>
			<?php } ?>
		</div>
	</div>
</div>
<div class="functions">
	<button class="function-btn">Hello</button>
	<button class="function-btn">Bye</button>
</div>
<div class="new-prep-tabs c-24">
	<div class="left">
		<button class="active" data-dog="all">All</button>
		<button data-dog="real">Real Dogs</button>
		<button data-dog="comic">Comic Dogs</button>
	</div>
	<div class="right">
		<div class="tile" data-dog="comic">
			<img class="preload bg" src="https://images.prismic.io/syllabus/1082628c-df4b-4c1d-a810-8639941789f0_CatDog.jpeg?auto=compress,format">
		</div>
		<div class="tile" data-dog="real">
			<img class="preload bg" src="https://images.prismic.io/syllabus/a2740883-4f08-4ac9-8cf1-95f650efa5d7_AustralianCattleDog-FeaturedImage-1024x615.webp?auto=compress,format">
		</div>
		<div class="tile" data-dog="real">
			<img class="preload bg" src="https://images.prismic.io/syllabus/4cb2a621-48aa-4cd2-8daa-d71b5365e51e_husky-wolf-1.webp?auto=compress,format">
		</div>
		<div class="tile" data-dog="real">
			<img class="preload bg" src="https://images.prismic.io/syllabus/02638aa3-a025-4528-9a80-e0ce8c7b413a_Pug_FeaturedImage.avif?auto=compress,format">
		</div>
	</div>
</div>
<?php
/* ----- Home JSON+LD Schema ----- */
include(views_dir() . "/schema/schema-home.php"); ?>


