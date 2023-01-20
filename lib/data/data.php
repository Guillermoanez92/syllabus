<?php
global $BUILDINFO;
global $DATABASE;

/**
 *    Data includes
 */
require_once __DIR__ . "/_interfaces.php";
require_once __DIR__ . "/" . $BUILDINFO->lib->data . "/init.php";

/**
 *    Data functions
 */
$DATABASE = new stdClass();
$DATABASE->app = new Database();
$DATABASE->client = $DATABASE->app::Init();

/* --------------- *
echo "<br><pre>";
print_r($DATABASE);
echo "</pre>";
/* --------------- */

$tables = array(
	"content",
	"shop"
);

$DATABASE->app->CheckDatabases($tables);

/* --------------- *
echo "<br><pre>";
var_dump($DATABASE->tables);
echo "</pre>";
/* --------------- */
?>