<?php
global $GLOBAL;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Middlewares\TrailingSlash;

/**
 * Force trailing slashes on URLs
 *  - Need to define consistently for the router to work properly
 */
$GLOBAL->app->add(new TrailingSlash(true));

/**
 * Catch our 301 Redirects
 */
$GLOBAL->app->add(function(Request $request, RequestHandler $handler){

    $response = new Response();

    $uri = $request->getUri();
    $path = $uri->getPath();
    $query = $uri->getQuery();

    /* --- Direct Reroutes --- */
    switch($path){

        /* --- Home Page --- */
        case "/example-page/":

            return $response->withHeader("Location", "/")->withStatus(301);

            break;

        default:

            break;
    }

    return $handler->handle($request);
});

/**
 * Gzip our requests
 *  - this will pass accept headers to allow json/ajax requests to Gzip via Zlib
 */
$GLOBAL->app->add(function(Request $request, RequestHandler $handler){

    /* --- We really just want POST only --- */
    if($_SERVER["REQUEST_METHOD"] !== "POST") return $handler->handle($request);

    /* --- Check if browser accepts gzip, if not, return --- */
    if($request->hasHeader("Accept-Encoding") && stristr($request->getHeaderLine("Accept-Encoding"), "gzip") === false){

        return $handler->handle($request);
    }

    $response = $handler->handle($request);

    /* --- Return if already encoded --- */
    if($response->hasHeader("Content-Encoding")){

        return $handler->handle($request);
    }

    /* --- Compress --- */
    $deflate = deflate_init(ZLIB_ENCODING_GZIP);
    $compress = deflate_add($deflate, (string)$response->getBody(), \ZLIB_FINISH);

    $stream = fopen("php://memory", "r+");

    fwrite($stream, $compress);
    rewind($stream);

    $body = new \Slim\Psr7\Stream($stream);

    return $response->withHeader("Content-Encoding", "gzip")->withHeader("Content-Length", strlen($compress))->withBody($body);
});
?>