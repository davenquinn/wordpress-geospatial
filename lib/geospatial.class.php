<?php

class Geospatial {

	function __construct() {
		add_action( 'init', array(&$this, '_create_types'));
		add_action( 'init', array(&$this, 'enqueue_scripts'));
		add_filter('the_posts', array(&$this, '_filter_the_posts'));

		$this->admin = new GeospatialAdmin($this);

	}

	static function status_message($message, $error=false) {
		return array(
				"error" => $error,
				"message" => $message
			);
	}

	function enqueue_scripts () {
		wp_register_script('openlayers', GEOSPATIAL_URL . "js/openlayers/OpenLayers.js");
    	wp_enqueue_script('geospatial', GEOSPATIAL_URL . "js/geospatial.js", array("openlayers", "jquery"));
    	wp_enqueue_style('global-style', GEOSPATIAL_URL . "css/global.css");

    	wp_enqueue_style('openlayers-style', GEOSPATIAL_URL . "js/openlayers/theme/default/style.css");

    }

	function _create_types() {

		register_post_type('place', array(
			'label' => __('Places'),
			'singular_label' => __('Place'),
			'public' => true,
			'show_ui' => true,
			'taxonomies' => array('geotype'),
			'capability_type' => 'post',
			'hierarchical' => false,
			'has_archive' => false,
			'rewrite' => false, ///array('slug'=>'geo', 'with_front'=>false),
			'query_var' => true,
			'supports' => array('title', 'author', 'custom-fields', 'geojson_meta')
		));


		$labels = array(
			'name' => _x( 'Geodata Type', 'taxonomy general name' ),
			'singular_name' => _x( 'Geodata Type', 'taxonomy singular name' ),
		); 	

		register_taxonomy( 'geotype', array( 'place' ), array(
			'hierarchical' => true,
			'labels' => $labels,
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => false,
		));

		$this->_initialize_post_relationships();
	}

	function _initialize_post_relationships() {

		//if (!is_admin()) include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		//if(!is_plugin_active('posts-to-posts')) return;
		if(!function_exists("p2p_register_connection_type")) return;

		/* Register connection */
 
	    $connection_args = array(
	        'name' => 'post_geodata',
	        'from' => 'post',
	        'to'   => 'place',
	        'sortable' => 'any',
	        'reciprocal' => false,
	        'admin_box' => array(
	            'show' => 'any',
	            'context' => 'normal',
	            'can_create_post' => false
	        ),
	        'admin_column' => 'any'
	    );
	 
	    p2p_register_connection_type($connection_args);

	}

	function _filter_the_posts($posts) {
		for ($i=0, $len=count($posts); $i<$len; $i++) {
			$post = &$posts[$i];
			if (!is_null($geom = PostGeometry::getInstanceFromDb($post->ID))) {
				$post->geometry = $geom;
			}
		}
		return $posts;
	}

	function bulk_import_features() {
		//strtotime();
	}

}