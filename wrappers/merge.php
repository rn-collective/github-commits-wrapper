<?php
	include_once('../config.php');
	include_once('../webhook.php');

	$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'];
	$payload = file_get_contents('php://input');
	$hash = hash_hmac('sha256', $payload, $secret);

	// https://i.imgur.com/Fq9pSRd.png

	file_put_contents('merge_payload', $payload);

	//$curl = curl_init( $webhook );
	//curl_setopt( $curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
	//curl_setopt( $curl, CURLOPT_POST, 1);
	//curl_setopt( $curl, CURLOPT_POSTFIELDS, $embed);
	//curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 1);
	//curl_setopt( $curl, CURLOPT_HEADER, 0);
	//curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);

	//$response = curl_exec( $curl );
	//curl_close( $curl )
?>