<?php
global $GLOBAL;

use Slim\Interfaces\ErrorRendererInterface;

/**
 * Setup our custom 404 handler
 */
class Custom404ErrorRenderer implements ErrorRendererInterface {

    public function __invoke(Throwable $exception, bool $displayErrorDetails): string {

    	if($exception->getCode() === 404){
    		not_found_include();
    	} else {
    		not_found_include($exception);
    	}

        return "";
    }
}

/**
 * Catch our 404 Pages
 */
$errorMiddleware = $GLOBAL->app->addErrorMiddleware(true, true, true);

$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->registerErrorRenderer("text/html", Custom404ErrorRenderer::class);
?>