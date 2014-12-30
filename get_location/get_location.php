<?php

/**
 * Script to get the hometown of WordPress developers
 * from their profile page at wordpress.org.
 */

$users_path = 'users';
$users_to_skip_path = 'users_to_skip';
$users_data_path = 'users_data.csv';
$users_data_handler = fopen($users_data_path, 'a');

if (!file_exists($users_to_skip_path)) {
	touch($users_to_skip_path);
}

$users = file($users_path);
$users_to_skip = file($users_to_skip_path);

foreach ($users as $user) {
	if (in_array($user, $users_to_skip)) {
		echo "Skipping user $user";
		continue;
	}

	$user = trim($user);

	$html = file_get_contents('https://profiles.wordpress.org/' . $user);

	if ($http_response_header[0] == 'HTTP/1.1 200 OK') {
		$doc = new DOMDocument();
		libxml_use_internal_errors(true);
		$doc->loadHTML($html);

		$element = $doc->getElementById('user-location');
		if (!is_null($element)) {
			$user_location = trim($element->textContent);
		} else {
			$user_location = "No location";
		}
	} else {
		$user_location = "User not found";
	}

	fputcsv($users_data_handler, array($user, $user_location));
	file_put_contents($users_to_skip_path, $user . "\n", FILE_APPEND);
	echo $user . " | " . $user_location . "\n";;
}
