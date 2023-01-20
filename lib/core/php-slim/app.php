<?php
/*
 * This is the main file of the application, including routing and controllers.
 *
 * $app is a Slim application instance, see the framework documentation for more details:
 * http://docs.slimframework.com/
 *
 * The order of the routes matter, as it will define the priority of routes. For that reason we
 * need to keep the more "generic" routes, such as the pages route, at the end of the file.
 *
 */
global $GLOBAL;

/*
 * Includes
 */
require_once __DIR__ . "/includes/http.php";
require_once __DIR__ . "/includes/middleware.php";
require_once __DIR__ . "/includes/routing.php";
require_once __DIR__ . "/includes/errors.php";

/* --- Start the app --- */
$GLOBAL->app->run();
?>
