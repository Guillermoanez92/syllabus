<?php
use Prismic\Dom\RichText;
?>
<div class="project-block">
	<div class="project-block-inner">
		<div class="project-block-left">
			<p class="project-title"><?php echo $thisData->project_title[0]->text ?></p>
			<p class="project-subtitle"><?php echo $thisData->project_sub_title[0]->text ?></p>
			<div class="service-list desktop-only marquee" data-at="767">
				<div class="inner" data-dur="<?php echo ($thisData->marquee_duration_mobile[0]->text) ?>">
					<div class="group"><?php echo RichText::asHtml($thisData->service_list); ?></div>
					<div class="group" aria-hidden="true"><?php echo RichText::asHtml($thisData->service_list); ?></div>
				</div>
			</div>
		</div>
		<div class="all-media<?php if($thisData->full_height_image === true) { ?> image-right<?php } ?>">
			<div class="image-wrapper full-height mw">
				<?php foreach($thisData->image as $key => $item) {
					if ($key % 3 === 0) { ?>
						<img class="preload" data-preload-desktop="<?php echo $item->image1->url ?>" data-preload-mobile="<?php echo $item->image1->url ?>">
					<?php } ?>
				<?php } ?>
			</div>
			<div class="image-wrapper split-height mw">
				<?php foreach($thisData->image as $key => $item) {
					if ($key % 3 !== 0) { ?>
						<img class="preload" data-preload-desktop="<?php echo $item->image1->url ?>" data-preload-mobile="<?php echo $item->image1->url ?>">
					<?php } ?>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
