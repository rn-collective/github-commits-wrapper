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
	if (count($data['commits']) <= 0) {
		exit;
	}

	$author = $data['sender']['login'];
	$avatar_url = $data['sender']['avatar_url'];
	$repo = $data['repository']['full_name'];
	$branch = explode('/', $data['ref'])[2];
	$branch_url = "https://github.com/$repo/tree/$branch";

	$commit_list = "";
	foreach ($data['commits'] as $commit) {
		$message = $commit['message'];
		if (substr($message, 0, 1) == '!') {
			$first_line = ':detective: confidential commit';
		} else {
			$first_line = explode("\n", $message)[0];
		}
		$commit_list .= "- $first_line\n";
	}

	$content = "[$author on $repo:$branch](<$branch_url>)\n\n$commit_list";

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