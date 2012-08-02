<?php

function get_from_url($url) {
	$crl = curl_init();
	$timeout = 5;
	curl_setopt ($crl, CURLOPT_URL,$url);
	curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
	$out = curl_exec($crl);
	curl_close($crl);
	return $out;
}


add_action( 'wp_ajax_geospatial-google-import', 'geospatial_google_import' );

function geospatial_google_import() {
	if (!wp_verify_nonce($_POST['nonce'],'geospatial_postadmin_nonce')) return $post_id;
	$url = $_POST['url'];
	//echo "Downloading ".$url;

	$kml = get_from_url($url);

	$geom = geoPHP::load($kml, 'kml');
	foreach($geom->components as $component) {
		if ($component->geometryType() == "LineString")
			echo $component->out('wkt');
			//error_log($component->out('wkt'));
	}

	die();
}

class Geospatial_Bulk_Importer {
	function __construct($parent) {
		$this->parent = $parent;
		add_action('admin_menu', array(&$this, 'setup'), 20);


	}

	function setup() {
		add_submenu_page(
			'edit.php?post_type=place',
			'Bulk Import Geospatial Data',
			'Bulk Import',
			'manage_options',
			'geospatial_import_places',
			array(&$this,'create_page')
		);

		// loads for entire admin. Not necessary.

    	wp_enqueue_script('bulk-import-js', GEOSPATIAL_URL . "tpl/bulk_import/bulk_import.js");
    	wp_enqueue_style('bulk-import-css', GEOSPATIAL_URL . "tpl/bulk_import/bulk_import.css");

    	add_action( 'wp_ajax_geospatial-prep-import', array(&$this,'prep_import') );

	}

	function create_page() {

		require_once(GEOSPATIAL_TPL_PATH . "/bulk_import/bulk_import.php");
	}

	function prep_import($input) {
		print_r($input);


		foreach ($_FILES["images"]["error"] as $key => $error) {
			if ($error == UPLOAD_ERR_OK) {
				print $_FILES["images"]["tmp_name"][$key];
			}
		}
		echo "<h2>Successfully Uploaded Images</h2>";
	}
}

?>