<?php
/**
 * Views dir
 */
function views_dir(){
    return dirname(dirname(dirname(dirname(__DIR__)))) . "/app/views";
}

/**
 * Template render
 */
function render($app, $path, $data = array()){

    global $PAGE;
    global $ASSETPATH;
    global $BUILDINFO;

    /* --- Load data into page variable --- */
    foreach($data as $key => $value){
        $PAGE[$key] = $value;
    }

    $file_path = views_dir() . "/" . $path . ".php";

    if(isset($PAGE["type"]) && $PAGE["type"] == "page" && !file_exists($file_path)){

        /* --- Check for template override --- */
        if(isset($PAGE["document"]) && isset($PAGE["document"]->data->page_template) && $PAGE["document"]->data->page_template && $PAGE["document"]->data->page_template !== "page"){
            $file_path = views_dir() . "/page--" . strtolower($PAGE["document"]->data->page_template) . ".php";

            if(!file_exists($file_path)){
                $file_path = views_dir() . "/page.php";
            }
        } else {
            $file_path = views_dir() . "/page.php";
        }
    }

    if(isset($PAGE["type"]) && $PAGE["type"] == "single" && !file_exists($file_path)){
        $file_path = views_dir() . "/single.php";
    }

    /* --- Header --- */
    require_once(views_dir() . "/header.php");

    /* --- Get Template --- */
    if(file_exists($file_path)){
        require($file_path);
    } else {
        not_found($app);
    }

    /* --- Footer --- */
    require_once(views_dir() . "/footer.php");
}

/**
 * 404 inside template render
 */
function not_found($app){

    $file_path = views_dir() . "/404.php";

    $PAGE["namespace"] = "not-found";
    $PAGE["title"] = "Not Found";

    /* --- Get Template --- */
    if(file_exists($file_path)){ // Avoid an infinite loop
        require($file_path);
    } else {
        echo "<h1>404 Not found</h1>";
    }
}

/**
 * 404 outside of template render
 */
function not_found_include($exception = null){

    global $PAGE;
    global $ASSETPATH;
    global $BUILDINFO;

    $PAGE["namespace"] = "not-found";
    $PAGE["title"] = "Not Found";
    $PAGE["description"] = "The requested resource could not be found.";

    /* --- Let's get our code --- */
    $code = 404;

    /* --- Print our exception code --- */
    if($exception && $exception->getCode() != 0){
        $code = $exception->getCode();
        $message = $exception;
    }

    /* --- Print our error code --- */
    if($exception && $exception->getMessage()){
        $message = $exception->getMessage();
    }

    $file_path = views_dir() . "/404.php";

    /* --- Send our response code to the server --- */
    http_response_code($code);

    /* --- Header --- */
    require_once(views_dir() . "/header.php");

    /* --- Get Template --- */
    if(!$exception && file_exists($file_path)){

        require($file_path);

    } else {

        /* --- Print remaining stack trace --- */
        echo "<div class='container' data-smooth><pre>";
        echo $message;
        echo "</pre></div>";
    }

    /* --- Footer --- */
    require_once(views_dir() . "/footer.php");
}
?>
