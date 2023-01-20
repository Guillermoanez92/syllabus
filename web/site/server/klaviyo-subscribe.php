<?php
ini_set("memory_limit", "1024M");
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

require_once dirname(dirname(__DIR__)) . "/config.php";

/**
 * Make sure we're running in UTC
 */
date_default_timezone_set("UTC");

/**
 * Setup our Storage Objects
 */
$error = "";
$return = new stdClass();

/**
 * Klaviyo Form Submission
 *
 *  - Your form markup will determine how this builds customers on Klaviyo's end
 *    and you must make sure to use the following default fields, and make sure
 *    your form "name" attributes match properly. The follonwing are reserved names:
 *
 *  - List ID:      name="g"
 *  - Email:        name="email"
 *  - Phone:        name="phone_number"
 *  - First Name:   name="first_name"
 *  - Last Name:    name="last_name"
 */

/* --- Grab Data --- */
if($json = json_decode(file_get_contents("php://input"), true)){
    $data = $json;
} else {
    $data = $_POST;
}

/* --- Check for Klaviyo API Key --- */
if(defined("KLAVIYO_API_KEY") && KLAVIYO_API_KEY !== "" && isset($data["email"]) && $data["email"] !== ""){

    $klaviyo_url = "https://a.klaviyo.com/api/v2/list/" . $data["g"] . "/subscribe";
    $klaviyo_obj = new stdClass();
    $klaviyo_obj->profiles = array();
    $klaviyo_obj->profiles[0] = new stdClass();
    $klaviyo_obj->profiles[0]->consent = "web";

    /* --- Loop to grab keys dynamically --- */
    foreach($data as $key => $value){
        if($key !== "g"){
            $klaviyo_obj->profiles[0]->{$key} = $value;
        }
    }

    /* --- Set our headers --- */
    $headers = array(
        "Accept: application/json",
        "Content-Type: application/json",
        "api-key: " . KLAVIYO_API_KEY
    );

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $klaviyo_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($klaviyo_obj));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    /* --- Store the data: --- */
    $json = curl_exec($ch);

    /* --------------- *
    echo "<br><pre>";
    print_r($json);
    echo "</pre>";
    /* --------------- */

    $result = json_decode($json, true);
    $return->result = $result;

    if(curl_error($ch)){
        $error_msg = curl_error($ch);
        /* --------------- *
        echo "<br><pre>";
        print_r($error_msg);
        echo "</pre>";
        /* --------------- */
        $error = $error_msg;
    }

    curl_close($ch);

} else {
    $error = "KLAVIYO_API_KEY is undefined";
}

echo json_encode($return);
?>
