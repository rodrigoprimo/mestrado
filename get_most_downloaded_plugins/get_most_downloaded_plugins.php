<?php

/**
 * Return a list of the most downloaded WordPress plugins.
 * WP-CLI is necessary to run this command.
 */

class Mestrado_Command extends WP_CLI_Command {
    function get_most_downloaded_plugins() {
        require_once('src/wp-admin/includes/plugin-install.php');
	
        $page = 1;
        $plugins = array();

        do {
            $res = plugins_api(
                'query_plugins',
                array(
                    'page' => $page,
                    'fields' => array(
                        'downloaded'    => true,
                    ),
                    'per_page' => 100,
                    'browse' => 'popular',
                )
            );

            if (!is_wp_error($res)) {
                $plugins = array_merge($plugins, $res->plugins);

                if (!isset($pages)) {
                    $pages = $res->info['pages'];
                }

                $page++;
            }

            sleep(2);
            var_dump($page);
        } while ($page <= $pages);

        usort($plugins, function($a, $b) {
            return $a->downloaded - $b->downloaded;
        });

        foreach ($plugins as $plugin) {
            var_dump($plugin->slug, $plugin->downloaded);
        }

        var_dump(count($plugins));
    }
}

WP_CLI::add_command('mestrado', 'Mestrado_Command');
