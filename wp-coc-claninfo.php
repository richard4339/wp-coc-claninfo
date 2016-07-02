<?php

/*
Plugin Name: wp-coc-claninfo
Plugin URI: https://github.com/richard4339/wp-coc-claninfo
Description: A Wordpress plugin to add widgets for Clash of Clans info
Version: 1.0
Author: Richard Lynskey
Author URI: https://github.com/richard4339/
License: MIT
*/

//namespace wp_coc_claninfo;

require_once 'vendor/autoload.php';

use ClashOfClans\Client;

class WarLog extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'   => 'WarLog',
			'description' => 'Display war log',
		);
		parent::__construct( 'WarLog', 'COC War Log', $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		// outputs the content of the widget

		?>
		<h2 class="widget-title">War Log</h2>
		<?php

		$token = get_option( 'wp_coc_claninfo_api_key' );
		if ( empty( $token ) ) {
			?>API Key not configured!<?php
			return;
		}

		?>
		<ul>
			<?php


			$client = new Client( $token );

			/**
			 * @todo Make clan ID an option
			 */
			$log = $client->getWarLog( '#2VLU9P88' );

			/**
			 * @todo Make max an option
			 */
			$max   = 5;
			$count = 0;
			foreach ( $log as $i ) {

				$count ++;
				$q = $i->getAllValues();

				printf( '<li>%s against %s</li>', $q['result'], $q['opponentName'] );

				if ( $count >= $max ) {
					break;
				}
			}

			?>
		</ul>
		<?php

	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		// outputs the options form on admin
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
	}
}

class ClanInfo extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'   => 'ClanInfo',
			'description' => 'Display clan information',
		);
		parent::__construct( 'ClanInfo', 'COC Clan Info', $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		// outputs the content of the widget
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		// outputs the options form on admin
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
	}
}

add_action( 'widgets_init', function () {
	register_widget( 'WarLog' );
	register_widget( 'ClanInfo' );
} );

register_uninstall_hook( __FILE__, 'wp_coc_claninfo_uninstall' );
register_activation_hook( __FILE__, 'wp_coc_claninfo_activate' );

function wp_coc_claninfo_uninstall() {
	delete_option( 'wp_coc_claninfo_api_key' );
	delete_option( 'wp_coc_claninfo_first_notice' );
}

function wp_coc_claninfo_activate() {
	add_option( 'wp_coc_claninfo', '0' );
	add_option( 'wp_coc_claninfo_first_notice', '1' );
}

function wp_coc_claninfo_post_install() {
	if ( get_option( 'wp_coc_claninfo_first_notice' ) == '1' ) {
		echo '<div class="updated"><p>Click <a href="' . get_admin_url( null, 'options-general.php?page=wp_coc_claninfo' ) . '">here</a> to configure COC Clan Info.</p></div>';
		delete_option( 'wp_coc_claninfo_first_notice' );
	}
}

add_action( 'admin_notices', 'wp_coc_claninfo_post_install' );
add_action( 'admin_menu', 'wp_coc_claninfo_menu' );

function wp_coc_claninfo_menu() {
	add_options_page( __( 'COC Clan Info', 'wp_coc_claninfo' ), __( 'COC Clan Info', 'wp_coc_claninfo' ), 'manage_options', 'wp_coc_claninfo', 'wp_coc_claninfo' );
}

function wp_coc_claninfo() {
	?>
	<div>
		<h2><?php print __( 'COC Clan Info', 'wp_coc_claninfo' ); ?></h2>
		<form method="post" action="options.php">
			<?php settings_fields( 'wp_coc_claninfo_api_key' ); ?>
			<?php do_settings_sections( 'wp_coc_claninfo' ); ?>

			<input type="submit" name="Submit" value="<?php _e( 'Update' ) ?>"/>
		</form>
	</div>

	<?php
}

// add the admin settings and such
add_action( 'admin_init', 'plugin_admin_init' );

function plugin_admin_init() {
	register_setting( 'wp_coc_claninfo_api_key', 'wp_coc_claninfo_api_key', 'plugin_options_validate' );
	//register_setting('wp_coc_claninfo', 'wp_coc_claninfo');
	add_settings_section( 'wp_coc_claninfo_main', 'Main Options', 'wp_coc_claninfo_section_text', 'wp_coc_claninfo' );
	add_settings_field( 'wp_coc_claninfo_api_key', 'API Key', 'wp_coc_claninfo_setting_string', 'wp_coc_claninfo', 'wp_coc_claninfo_main' );
}

function wp_coc_claninfo_section_text() {
	echo '<p>An API key must be obtained from developer.clashofclans.com to access their data. Please enter the key below.</b>.</p>';
}

function wp_coc_claninfo_setting_string() {
	?>
	<input name="wp_coc_claninfo_api_key" id="wp_coc_claninfo_api_key" type="text"
	       value="<? echo get_option( 'wp_coc_claninfo_api_key' ); ?>" class="code"/>
	<?php
}

// validate our options
function plugin_options_validate( $input ) {
	$newinput = trim( $input );
//	if (!preg_match('/^[0-1]{1}$/i', $newinput)) {
//		$newinput = '0';
//	}
	return $newinput;
}

?>