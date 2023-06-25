<?php
	include_once('../config.php');

	$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'];
	$payload = file_get_contents('php://input');
	$hash = hash_hmac('sha256', $payload, $secret);
	$hash = 'sha256=' . $hash;

	if ($hash !== $signature) {
		exit;
	}

	$data = json_decode($payload, true);
	if ($data['action'] != 'closed' && $data['action'] != 'opened') {
		exit;
	}

    $title = $data['pull_request']['title'];
    $is_confidential = substr($title, 0, 1) == '!';
    if ($is_confidential) {
        $title = ':detective: confidential pull request';
    }

	$embed = json_encode([
	    'embeds' => [
	        [
	            'type' => 'rich',
	            'title' => sprintf('🗂 %s ~ %s → %s', $data['repository']['name'], $data['pull_request']['head']['ref'], $data['pull_request']['base']['ref']),
	            'description' => '',
	            'url' => $data['pull_request']['html_url'],
	            'timestamp' => date('c', strtotime('now')),
	            'color' => hexdec('009800'),
	            'footer' => [
	                'text' => $data['sender']['login'],
	                'icon_url' => $data['sender']['avatar_url']
	            ],
	            'fields' => [
	            	[
	            		'name' => sprintf('Merge request №%d %s', $data['pull_request']['number'], $data['action']),
	            		'value' => $title,
	            		'inline' => false
	            	]
	            ],
	            'thumbnail' => [
	            	'url' => 'https://i.imgur.com/yCZeuXq.png'
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
