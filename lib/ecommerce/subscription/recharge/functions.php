<?php
/**
 * ReCharge Request Function
 */
function rechargeRequest($endpoint, $post = 0, $custom = null, $query = null){

	$headers = array(
		"Accept: application/json",
		"Content-type: application/json",
		"X-Recharge-Access-Token: " . SUBSCRIPTION_API_KEY
	);

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "https://api.rechargeapps.com" . $endpoint);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_POST, $post);

	if(!empty($custom)){
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
	}

	if(!empty($query)){
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
	}

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	$result = curl_exec($ch);
	$info = curl_getinfo($ch);
	$data = json_decode($result);

    if(curl_error($ch)){
    	
        $error_msg = curl_error($ch);
		/* --------------- *
		echo "<pre>";
		print_r($error_msg);
		echo "</pre><br>";
		/* --------------- */
        error_log($error_msg);
    }

	curl_close($ch);

	return $data;
}
?>