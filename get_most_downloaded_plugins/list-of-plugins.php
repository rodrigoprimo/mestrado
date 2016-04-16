<?php

/**
 * Verifica os plugins que existem no repositório github.com/wp-plugins
 */

$plugins = file('list-of-plugins', FILE_IGNORE_NEW_LINES);

foreach ($plugins as $plugin) {
	$curl_handle=curl_init();
	curl_setopt($curl_handle, CURLOPT_URL,"https://api.github.com/repos/wp-plugins/{$plugin}/commits?per_page=1&client_id=2f99d22b78e1accbf74a&client_secret=db2c56ca319c766e3238332ed29a69d3216531d9");
	curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Your application name');
	$query = json_decode(curl_exec($curl_handle));
	curl_close($curl_handle);
	
	if (isset($query->message)) {
		echo $plugin . "\n";
	}
}

