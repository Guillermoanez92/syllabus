<?php
/* --- index Associate Array Reference -- "url" => "function" --- */
$indexArray = array(
	"/v1/index/content/"		=> "content",
	"/v1/index/product/"		=> "product",
	"/v1/index/collection/"		=> "collection"
);

/* --- Loop through our indexs --- */
foreach($indexArray as $url => $index){

	$GLOBAL->app->post($url, function($request, $response) use ($GLOBAL, $index){

		/* --- Get our data --- */
		$data = json_decode($request->getBody());

		include(dirname(__DIR__) . "/index/index-" . $index . ".php");

		return $response->withStatus(200);
	});
}
?>