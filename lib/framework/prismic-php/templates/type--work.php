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

<div class="projects-section-inner">
	<div class="project-block">
		<div class="project-block-inner">
			<div class="project-block-left">
				<p class="project-title"><?php echo $data->project_title[0]->text ?></p>
				<p class="project-subtitle"><?php echo $data->project_sub_title[0]->text ?></p>
				<div class="service-list">
					<?php echo RichText::asHtml($data->service_list); ?>
				</div>
			</div>
			<div class="all-media<?php if($data->full_height_image === true) { ?> image-right<?php } ?>">
				<div class="image-wrapper full-height mw">
					<?php foreach($slice->items as $key => $item) {
						if ($key % 3 === 0) { ?>
							<img class="preload" data-preload-desktop="<?php echo $item->image->url ?>" data-preload-mobile="<?php echo $item->image->url ?>">
						<?php } ?>
					<?php } ?>
				</div>
				<div class="image-wrapper split-height mw">
					<?php foreach($slice->items as $key => $item) {
						if ($key % 3 !== 0) { ?>
							<img class="preload" data-preload-desktop="<?php echo $item->image->url ?>" data-preload-mobile="<?php echo $item->image->url ?>">
						<?php } ?>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>


