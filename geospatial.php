<?php
/*
Plugin Name: Wordpress Geospatial Plugin
Plugin URI: http://wordpress.org/extend/plugins/
Description: This plugin adds capabilities for advanced geospatial geometries.
Version: 0.1
Author URI: http://davenquinn.com/
*/

global $wpdb;
define('GEOSPATIAL_TABLE_NAME',	$wpdb->prefix . 'geospatial');
define('GEOSPATIAL_PLUGINDIR', plugin_dir_path(__FILE__));
define('GEOSPATIAL_TPL_PATH',	GEOSPATIAL_PLUGINDIR."/tpl");
define('GEOSPATIAL_URL', plugin_dir_url(__FILE__));

include_once('lib/functions.php');
include_once('lib/import.php');
include_once('lib/geophp/geoPHP.inc');
include_once('lib/admin.class.php');
include_once('lib/geometry.class.php');
include_once('lib/geospatial.class.php');

$geospatial = new Geospatial();



?>