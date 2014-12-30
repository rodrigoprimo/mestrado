<?php

/**
 * Script to get the hometown of WordPress developers
 * from their profile page at wordpress.org.
 */

// Composer autoload file
require 'vendor/autoload.php';

class GetLocation {
	public function __construct() {
		$this->users_path = 'users';
		$this->users_to_skip_path = 'users_to_skip';
		$this->users_data_path = 'users_data.csv';
	}

	/**
	 * Get hometown of WordPress developers and write the
	 * results to a CSV file.
	 *
	 * @return null
	 */
	public function run() {
		$users_data_handler = fopen($this->users_data_path, 'a');

		if (!file_exists($this->users_to_skip_path)) {
			touch($this->users_to_skip_path);
		}

		$users = file($this->users_path);
		$users_to_skip = file($this->users_to_skip_path);

		foreach ($users as $user) {
			if (in_array($user, $users_to_skip)) {
				echo "Skipping user $user";
				continue;
			}

			$user = trim($user);
			$user_location = $this->get_location_from_wp($user);

			fputcsv($users_data_handler, array($user, $user_location));
			file_put_contents($this->users_to_skip_path, $user . "\n", FILE_APPEND);
			echo $user . " | " . $user_location . "\n";;
		}
	}

	/**
	 * Get user location from profiles.wordpress.org
	 *
	 * @param string $user
	 * @return string user location
	 */
	private function get_location_from_wp($user) {
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

		return $user_location;
	}
}

$get_location = new GetLocation();
$get_location->run();