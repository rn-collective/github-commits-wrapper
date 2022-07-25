<?php
	include_once('../config.php');
	include_once('../webhook.php');

	//if ($secret !== $himac) {
	//	exit;
	//};

	// signature checks
	$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'];
	$payload = file_get_contents('php://input');
	$hash = hash_hmac('sha256', $payload, $secret);

	$data = json_decode($payload);

	$embed = json_encode([
	    "embeds" => [
	        [
	            "title" => $data['repository']['name'],
	            "type" => "rich",
	            "description" =>  sprintf('Новые коммиты в %s', $data['ref']),
	            "url" => $data['head_commit']['url'],
	            "timestamp" => date('c', strtotime('now')),
	            "color" => hexdec('fd7a61'),
	            "footer" => [
	                "text" => $data['sender']['login'],
	                "icon_url" => $data['sender']['avatar_url']
	            ],
	            "fields" => [
	                [
	                    "name" => "Field #1 Name",
	                    "value" => "Field #1 Value",
	                    "inline" => false
	                ]
	            ]
	        ]
	    ]
	], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

	$curl = curl_init( $webhook );
	curl_setopt( $curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
	curl_setopt( $curl, CURLOPT_POST, 1);
	curl_setopt( $curl, CURLOPT_POSTFIELDS, $embed);
	curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt( $curl, CURLOPT_HEADER, 0);
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);

	$response = curl_exec( $curl );
	curl_close( $curl )
?>