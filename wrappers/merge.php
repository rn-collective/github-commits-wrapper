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

	$author = $data['sender']['login'];
	$avatar_url = $data['sender']['avatar_url'];
	$title = $data['pull_request']['title'];
	if (substr($title, 0, 1) == '!') {
		$title = ':detective: confidential pull request';
	}
	$url = $data['pull_request']['html_url'];
	$action = $data['action'];

	$source_branch = $data['pull_request']['head']['ref'];
	$target_branch = $data['pull_request']['base']['ref'];

	$content = "PR: **[$title](<$url>)** ($action) `$source_branch → $target_branch`";

	$payload = json_encode([
		"username" => $author,
		"avatar_url" => $avatar_url,
		"content" => $content
	], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

	$curl = curl_init($webhook);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl, CURLOPT_HEADER, 0);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

	$response = curl_exec($curl);
	curl_close($curl);
?>