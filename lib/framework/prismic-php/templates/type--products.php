<?php
global $STOREFRONT;

$document = $PAGE["document"];
$data = $document->data;
$product = $PAGE["product"];
use Prismic\Dom\RichText;
/* --------------- *
echo "<br><pre>";
print_r($document);
echo "</pre>";
/* --------------- */
/* --------------- *
echo "<br><pre>";
print_r($product);
echo "</pre>";
/* --------------- */
$productId = $product->id;
/* ----- Review Rating & Count - DO NOT REMOVE ----- */
// $reviewOverview = getReviewOverview();
// $reviewRating = $reviewOverview->avg;
// $reviewCount = $reviewOverview->count;
/* ----- END - DO NOT REMOVE ----- */
?>
<?php include(views_dir() . "/parts/navigation.php"); ?>
<section class="product-section mw">
	<div class="full-bleed-bg preload" data-preload-desktop="<?php echo ($data->hero_background->url) ?>" data-preload-mobile="<?php echo ($data->hero_background->url) ?>"></div>
	<div class="product-content c-40">
		<div class="left">
			<div class="top">
				<div class="eyebrow"><span><?php include(views_dir() . "/parts/svgs/ratio.php"); ?></span><p><?php echo ($data->ratio[0]->text) ?></p></div>
				<div class="eyebrow"><span><?php include(views_dir() . "/parts/svgs/grind.php"); ?></span><p><?php echo ($data->molienda[0]->text) ?></p>	</div>
			</div>
			<div class="image-wrapper mw">
				<img class="preload" data-preload-desktop="<?php echo $data->imagen_producto->url ?>" data-preload-mobile="<?php echo $data->imagen_producto->url ?>">
			</div>
		</div>
		<div class="right">
			<div class="top">
			<div class="header-l"><?php echo ($data->titulo_producto[0]->text) ?></div>
			<div class="btn"><?php echo ($data->gramos[0]->text) ?></div>
			</div>
			<div class="body-copy"><?php echo ($data->precio_producto[0]->text) ?></div>
			<div class="product-info">
			<div class="body-copy"><?php echo RichText::asHtml($data->proceso_cafe); ?></div>
			<div class="light-copy"><?php echo ($data->tostado_cafe[0]->text) ?></div>
			<div class="body-copy"><?php echo ($data->descripcion_producto[0]->text) ?></div>
			</div>
			<div class="body-copy-small"><?php echo ($data->presentacion[0]->text) ?></div>
			<div class="body-copy-small"><?php echo ($data->cart_button_text[0]->text) ?></div>
			<div class="bottom">
				<div class="body-copy-small"><?php echo RichText::asHtml($data->varietal); ?></div>
				<div class="body-copy-small"><?php echo RichText::asHtml($data->altura); ?></div>
			</div>
		</div>
	</div>
</section>
<section class="info-marquee">
	<div class="body-copy-small"><?php echo RichText::asHtml($data->info_marquee); ?></div>
</section>
<section class="map-section c-40">
	<div class="map-copy">
		<div class="header-xl"><?php echo ($data->map_header[0]->text) ?></div>
		<div class="body-copy"><?php echo ($data->map_eyebrow[0]->text) ?></div>
		<div class="body-copy-small"><?php echo ($data->map_copy[0]->text) ?></div>
		<div class="regular-copy"><?php echo RichText::asHtml($data->map_eyebrow1); ?></div>
	</div>
	<div class="image-wrapper mw">
		<img class="preload" data-preload-desktop="<?php echo $data->imagen_mapa->url ?>" data-preload-mobile="<?php echo $data->imagen_mapa->url ?>">
	</div>
</section>



<?php
/* ----- Add To Cart ----- */
include(views_dir() . "/parts/add-to-cart.php");
?>

<?php
/* ----- Product JSON+LD Schema ----- */
include(views_dir() . "/schema/schema-product.php");
?>
