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

		$this->load_argument_parser();
	}

	private function load_argument_parser() {
		$arguments = new \cli\Arguments();
		$arguments->addFlag('get-location', 'Get users location from wordpress.org');
		$arguments->addFlag('get-country', 'Get users countries from OSM');
		$arguments->addFlag(array('help', 'h'), 'Show this help screen');
		$arguments->parse();

		if ($arguments['get-location']) {
			$this->run();
		} else if ($arguments['get-country']) {
			$this->get_country();
		} else {
			echo $arguments->getHelpScreen();
		}
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

		fclose($users_data_handler);
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

	/**
	 * Attempt to determine users countries by querying
	 * OSM database. Writes the results back to the CSV file.
	 *
	 * @return null
	 */
	public function get_country() {
		$users = $this->load_users_from_csv();
		$curl = new Buzz\Client\Curl();
		$curl->setTimeout(15);
		$client = new Buzz\Browser($curl);
		$consumer = new \Nominatim\Consumer($client, 'http://nominatim.openstreetmap.org');

		foreach ($users as $key => $user) {
			if ($user[1] != 'No location' && $user[1] != 'User not found' && !isset($user[2])) {
				$query = new \Nominatim\Query();
				$query->setParam('addressdetails', 1);
				$query->setParam('limit', 1);
				$query->setParam('accept-language', 'pt_BR');
				$query->setQuery($user[1]);
				$result = $consumer->search($query);

				if (isset($result[0]) && isset($result[0]['address']) && isset($result[0]['address']['country'])) {
					$user[2] = $result[0]['address']['country'];
					echo $user[0] . ' | ' . $user[2] . "\n";
					$this->write_user_to_csv($user);
				}
			}
		}
	}

	/**
	 * Update user data in a CSV file.
	 *
	 * @param array $user
	 * @return null
	 */
	private function write_user_to_csv($updated_user) {
		$users = $this->load_users_from_csv();
		$file = fopen($this->users_data_path, 'w');

		foreach ($users as $user) {
			if ($user[0] == $updated_user[0]) {
				$user = $updated_user;
			}

			fputcsv($file, $user);
		}

		fclose($file);
	}

	/**
	 * Return an array of arrays with the users and their locations
	 * loaded from the CSV file.
	 *
	 * @return array
	 */
	private function load_users_from_csv() {
		$users = array();
		$file = fopen($this->users_data_path, 'r');

		while (($line = fgetcsv($file)) !== FALSE) {
			$users[] = $line;
		}

		fclose($file);

		return $users;
	}
}

$get_location = new GetLocation();