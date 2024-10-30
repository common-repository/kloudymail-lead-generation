<?php
/*
Plugin Name: Kloudymail Lead generation
Plugin URI: https://www.kloudymail.com/
Description: Integrate kloudymail in your wordpress website
Author: Wekloud
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: kloudymail
Domain Path /languages
Version: 1.0
*/
	global $wpdb;
	
	$kmlg_url = "https://panel.kloudymail.com";
	$kmlg_db_version = '1.0';
	$kmlg_subs_table_name = "{$wpdb->prefix}kloudymail_subscribers";
	$kmlg_shortcode_widgets = "{$wpdb->prefix}kloudymail_shortcode_widgets";

	define( 'PLUGIN_DIR', dirname(__FILE__).'/' ); 
	include PLUGIN_DIR . 'km_lib.v2.php';
	include PLUGIN_DIR . 'admin/class-wp-list-table.php';
	include PLUGIN_DIR . 'widgets.php'; 
	include PLUGIN_DIR . 'admin/menu.php';
	include PLUGIN_DIR . 'admin/ajax.php';
	include PLUGIN_DIR . 'public/ajax.php';

	register_activation_hook(__FILE__, 'kmlgInstall');
	add_action('plugins_loaded','kmlgLoad');
	add_action('widgets_init',function(){return register_widget('kmlgWidget');});
	add_action('wp_enqueue_scripts', 'kmlgScripts');
	register_activation_hook(__FILE__, 'kmlgActivation');
	register_deactivation_hook(__FILE__, 'kmlgDeactivation');
	add_action('kmlgUpdateFields','kmlgUpdateFieldsCron');

	add_shortcode('kloudymail','kmlgShortcodeHandler');
	
	function kmlgScripts() {
	    wp_enqueue_script('jquery');

	    wp_register_script( 'subscribe', plugins_url( 'public/js/subscribe.js', __FILE__ ), array('jquery'), null, true );
	    wp_register_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js');
	    add_filter('script_loader_tag', 'kmlgAddAsyncDefer', 10, 2);

	    wp_enqueue_style('kmlg_form_style', plugins_url( 'public/css/form_style.css', __FILE__ ));
	    wp_enqueue_script('recaptcha');
	    wp_enqueue_script('subscribe');
	}

	function kmlgActivation(){
		if ( !wp_next_scheduled( 'kmlgUpdateFields' ) )
			wp_schedule_event(time(), 'hourly', 'kmlgUpdateFields');
	}

	function kmlgDeactivation(){
		global $wpdb,$kmlg_subs_table_name,$kmlg_shortcode_widgets;

		wp_unschedule_event( wp_next_scheduled('kmlgUpdateFields'), 'kmlgUpdateFields');
		$wpdb->query("DROP TABLE IF EXISTS $kmlg_subs_table_name;");
		$wpdb->query("DROP TABLE IF EXISTS $kmlg_shortcode_widgets;");
		delete_option('Kloudymail');
		delete_option('widget_kloudymail');
	}

	function kmlgUpdateFieldsCron(){
		global $wpdb,$kmlg_shortcode_widgets;
		$settings=get_option('Kloudymail');

		$widget_instances=null;
		$widget_instances=get_option('widget_kloudymail');
		foreach ($widget_instances as $key=>$value) {
			if (!is_scalar($widget_instances[$key]))
				// Se è stata settato e l'account con cui è stata creato è lo stesso
				if ($widget_instances[$key]['list']!='0' && $widget_instances[$key]['account']==$settings['account'])
					$widget_instances[$key]['fields'] = kmlgGetFields($widget_instances[$key]['list']);
		}

		$widgets = $wpdb->get_results("SELECT * FROM $kmlg_shortcode_widgets;",'ARRAY_A');
		foreach ($widgets as $key=>$value) {
			// Se è stata settato e l'account con cui è stata creato è lo stesso
			if ($widgets[$key]['list']!='0' && $widgets[$key]['account']==$settings['account'])
				$wpdb->update($kmlg_shortcode_widgets,array('fields'=>kmlgGetFields($widgets[$key]['list'])),array('id'=>$widgets[$key]['id']));
		}
		update_option('widget_kloudymail',$widget_instances);
	}

	function kmlgAddAsyncDefer($tag,$handle){
		if ( 'recaptcha' !== $handle )
			return $tag;
		return str_replace(' src',' async defer src', $tag);
	}

	function kmlgInstall(){
		global $wpdb, $kmlg_db_version, $kmlg_subs_table_name, $kmlg_shortcode_widgets;
		$installedVersion = get_option('kmlg_db_version');

		if ($installedVersion != $kmlg_db_version){
			$charset_collate = $wpdb->get_charset_collate();

			$tableName = $kmlg_subs_table_name;
			$query = "CREATE TABLE IF NOT EXISTS $tableName(
				id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				address varchar(512) NOT NULL,
				data LONGTEXT NOT NULL,
				account char(36) NOT NULL,
				list char(36) NOT NULL,
				listName varchar(128) NOT NULL,
				sent boolean NOT NULL,
				notified boolean NOT NULL,
				PRIMARY KEY (id)
			)$charset_collate;";

			require_once(ABSPATH.'wp-admin/includes/upgrade.php');
			dbDelta( $query );

			$tableName = $kmlg_shortcode_widgets;
			$query = "CREATE TABLE IF NOT EXISTS $tableName(
				id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				name char(30) NOT NULL,
				account char(36) NOT NULL,
				list char(36) NOT NULL,
				listName varchar(128) NOT NULL,
				notify boolean NOT NULL,
				fields LONGTEXT,
				PRIMARY KEY (id)
			)$charset_collate;";

			require_once(ABSPATH.'wp-admin/includes/upgrade.php');
			dbDelta( $query );

			update_option('kmlg_db_version', $kmlg_db_version);
		}
	}

	function kmlgLoad(){
		global $kmlg_db_version;

		if (get_site_option('kmlg_db_version') != $kmlg_db_version){
			kmlgInstall(); // per un eventuale aggiornamento del db
		}
		load_plugin_textdomain('kloudymail',false,basename( dirname( __FILE__ )) . '/languages');
	}
?>