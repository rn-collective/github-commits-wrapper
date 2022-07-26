<?php
	include_once('../config.php');
	include_once('../webhook.php');

	$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'];
	$payload = file_get_contents('php://input');
	$hash = hash_hmac('sha256', $payload, $secret);
	$hash = 'sha256=' . $hash;

	if ($hash !== $signature) {
		exit;
	}

	$data = json_decode($payload, true);
	$commits_array = array();
	if (count($data['commits']) <= 0) {
		exit;
	}

	foreach ($data['commits'] as $commit => $value) {
		$is_confidential = substr($value['message'], 0, 1) == '!';
		$message = $value['message'];
		if ($is_confidential) {
			$message = ':detective: confidential commit';
		}

		$changed = '';
		if (count($value['added']) > 0) {
			$changed = $changed . ' `+' . $value['added'] . '`';
		}

		if (count($value['removed']) > 0) {
			$changed = $changed . ' `-' . $value['removed'] . '`';
		}

		if (count($value['modified']) > 0) {
			$changed = $changed . ' `~' . $value['modified'] . '`';
		}
		$changed = trim($changed);

		$commit_array = [
			"name" => sprintf('%s `%s` %s', $value['author']['name'], substr($value['id'], 0, 7), $changed),
			"value" => sprintf('[%s](%s)', $message, $value['url']),
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
	            "url" => $data['repository']['html_url'],
	            "timestamp" => date('c', strtotime('now')),
	            "color" => hexdec('7289da'),
	            "footer" => [
	                "text" => $data['sender']['login'],
	                "icon_url" => $data['sender']['avatar_url']
	            ],
	            "fields" => $commits_array,
	            "thumbnail" => [
	            	"url" => "https://i.imgur.com/HvMRwKU.png"
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