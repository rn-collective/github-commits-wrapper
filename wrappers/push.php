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

	$data = json_decode($payload, true);
	$commits_array = array();

	foreach ($data['commits'] as $commit => $value) {
		$commit_array = [
			"name" => sprintf('%s `%s` +%d -%d ~%d', $value['author']['name'], substr($value['id'], 0, 7), count($value['added']), count($value['removed']), count($value['modified']) ),
			"value" => $value['message'],
			"inline" => false
		];

		array_push($commits_array, $commit_array);
	}

	$embed = json_encode([
	    "embeds" => [
	        [
	            "type" => "rich",
	            "title" => sprintf('🗂 %s ~ %s', $data['repository']['name'], $data['ref']) ,
	            "description" => '',
	            "url" => $data['head_commit']['url'],
	            "timestamp" => date('c', strtotime('now')),
	            "color" => hexdec('2f3136'),
	            "footer" => [
	                "text" => $data['sender']['login'],
	                "icon_url" => $data['sender']['avatar_url']
	            ],
	            "fields" => $commits_array
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