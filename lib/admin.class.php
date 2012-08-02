<?php

class GeospatialAdmin {
	function __construct() {

		// WP Admin Panel / Write / Activity Hooks
		add_action('admin_init', array(&$this, '_post_editor_setup'));
		add_action('edit_post', array(&$this, '_on_edit_publish_save_post'));
		add_action('publish_post', array(&$this, '_on_edit_publish_save_post'));
		add_action('save_post', array(&$this, '_on_edit_publish_save_post'));
		add_action('delete_post', array(&$this, '_on_delete_post'));

		// Plugin Activation, Deactivation
		register_activation_hook(GEOSPATIAL_PLUGINDIR . '/geospatial.php', array(&$this, '_on_activate_pluginurl'));
		register_deactivation_hook(GEOSPATIAL_PLUGINDIR . '/geospatial.php', array(&$this, '_on_deactivate_pluginurl'));

	}

	function _post_editor_setup() {
		$post_types = get_post_types(array("public" => true));
    	wp_enqueue_script('openlayers', GEOSPATIAL_URL . "lib/openlayers/OpenLayers.js");
    	wp_enqueue_script('openlayers-editor-loader', GEOSPATIAL_URL . "lib/openlayers/editor/loader.js");
    	wp_enqueue_script('openlayers-editor', GEOSPATIAL_URL . "lib/openlayers/editor/Editor/Lang/en.js");
    	wp_enqueue_script('jquery-ui-accordion');
    	wp_enqueue_script('metadata_box', GEOSPATIAL_URL . "tpl/metadata_box.js");
    	wp_enqueue_style('openlayers-editor-style', GEOSPATIAL_URL . "lib/openlayers/editor/theme/geosilk/geosilk.css");
    	wp_enqueue_style('openlayers-style', GEOSPATIAL_URL . "lib/openlayers/theme/default/style.css");
    	wp_enqueue_style('metadata-box-style', GEOSPATIAL_URL . "tpl/metadata_box.css");

	   	foreach (array("post","place") as $type) {
	        add_meta_box('geojson_metabox', 'Geospatial', array(&$this,'_metabox_setup'), $type, 'normal', 'high');
	    }
	}

	function _metabox_setup() {
		global $post;
		if (isset($post->ID)) {
			$geom = PostGeometry::getInstanceFromDb($post->ID);
		}
		$geometries = geoPHP::geometryList();
		//$nonce = wp_create_nonce('geospatial_postadmin_nonce');

		require_once(GEOSPATIAL_TPL_PATH . "/metadata_box.php");

	}

	function _on_edit_publish_save_post($post_id) { 
		$executeAction = true;

		// Don't execute this action if it was called for a post revision
		// Function wp_is_post_revision was introduced in WP 2.6

		if (function_exists('wp_is_post_revision')) 
			$executeAction = $executeAction && !wp_is_post_revision($post_id);

		// Don't execute this action if it was called for an autosave
		// Function wp_is_post_autosave was introduced in WP 2.6

		if (function_exists('wp_is_post_autosave'))
			$executeAction = $executeAction && !wp_is_post_autosave($post_id);

		if ($executeAction) {
			global $_POST;
			// check if geodata is provided
			if (!isset($_POST['geospatial_wkt']) || empty($_POST['geospatial_wkt']))
				return $post_id;

		    // make sure data came from our meta box
		    if (!wp_verify_nonce($_POST['geospatial_noncename'],'geospatial_postadmin_nonce')) return $post_id;
		    // check user permissions
		    if ($_POST['post_type'] == 'page') {
		        if (!current_user_can('edit_page', $post_id)) return $post_id;
		    } else {
		        if (!current_user_can('edit_post', $post_id)) return $post_id;
		    }


			$wkt = $_POST['geospatial_wkt'];

		    if (!is_null($geom = PostGeometry::getInstanceFromDb($post_id))) {

		    	$geom->geom = geoPHP::load($wkt, "wkt");

		    	$geom->persist();
		    } else {
		    	$geom = new PostGeometry(null, $post_id, $wkt, 'wkt');
		    }

		}
	}

	function _on_delete_post($post_id) {
		if (!is_null($geom = PostGeometry::getInstanceFromDb($post_id))) {
			$geom->delete();
		}
	}
	//
	// Installation/Activation
	//

	/**
	 * Action for hook: activate_pluginurl
	 */
	function _on_activate_pluginurl() {

		// Create Geospatial Table if not existant

		global $wpdb;

		if($wpdb->get_var('show tables like "' . GEOSPATIAL_TABLE_NAME . '"') != GEOSPATIAL_TABLE_NAME) {

			$sql = 'CREATE TABLE ' . GEOSPATIAL_TABLE_NAME . ' ( ' .
				'id BIGINT NOT NULL AUTO_INCREMENT, ' .
				'post_id BIGINT NOT NULL, ' .
				'geom GEOMETRY NOT NULL, '.
				'PRIMARY KEY (id), ' .
				'INDEX idx_01(post_id) ' .
			');';

			require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
			dbDelta($sql);

		}


	}
	function _on_deactivate_pluginurl() { }
}

?>