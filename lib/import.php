<?php

function pre_print($input) {
	echo "<pre>";
	print_r($input);
	echo "</pre>";
}

function get_from_url($url) {
	$crl = curl_init();
	$timeout = 5;
	curl_setopt ($crl, CURLOPT_URL,$url);
	curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
	$out = curl_exec($crl);
	curl_close($crl);
	error_log($out);
	return $out;
}


add_action( 'wp_ajax_geospatial-google-import', 'geospatial_google_import' );

function geospatial_google_import() {
	if (!wp_verify_nonce($_POST['nonce'],'geospatial_postadmin_nonce')) return $post_id;
	$url = $_POST['url'];
	$json = get_from_url($url."&output=json");
	//$json = file_get_contents(plugin_dir_path(__FILE__)."/test3.json"); // For testing

	$match = array();
	preg_match_all('/points:"([^"]+)"/i', $json, $match);
	
	$polylines = $match[1];

	$polyline = implode("",$polylines);
	
	$geom = geoPHP::load($polyline, 'encoded_polyline');


	echo $geom->out('wkt');

	die();
}

class GeospatialBulkImporter {
	function __construct($parent) {
		$this->parent = $parent;
		add_action('admin_menu', array(&$this, 'setup'), 10);
		add_action( 'wp_ajax_geospatial-start-import', array(&$this,'start_import'));
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


	}

	function create_page() {

		require_once(GEOSPATIAL_TPL_PATH . "/bulk_import/bulk_import.php");
	}

	function start_import() {
		if (!wp_verify_nonce($_POST['nonce'],'geospatial_bulk_import_nonce')) return $post_id;
		//$input = $_POST['input'];
		$namebase = "Feature";
		$output = array();
		ob_start();
		

		$input = file_get_contents(plugin_dir_path(__FILE__)."/bike_rides.json"); // For testing
		$collection = json_decode($input);

		if ($collection->type == "FeatureCollection") {
			$features = array();
			$num = count($collection->features);
			if ($num==1) $str = $num." feature";
			else $str = $num." features";
			echo "<h4>".$str." found.</h4>";
			echo "<input type='submit' value='Import All Features' name='validate_submit' /><input type='submit' value='Cancel' name='validate_cancel' />";
			echo "<ul>";
			foreach ($collection->features as $key => $feature) {
				echo "<li>";
				$out = array();
				if ($feature->name) {
					echo "<h4 class='name'><span>Name:</span>".$feature->name."</h4>";
					$out['name'] = $feature->name;
				} else {
					echo "<h4 class='name not-found'><span>Name:</span>".$namebase." <em>+ index</em></h4>";
				}
				if ($feature->date) {
					$date = strtotime($feature->date);
					if (!$date) $strdate = "Invalid format";
					else $strdate = strftime("%B %e, %Y", $date);
					echo "<p class='date'><span>Date:</span>".$strdate."</p>";
				} else {
					echo "<p class='date not-found'><span>Date:</span><em>now</em></p>";
				}
				$geom = geoPHP::load($feature, 'json');
				echo "<p class='type'><span>Type:</span>".$geom->geometryType()."</p>";
				echo "</li>";
			}
			echo "</ul>";
			$output["status"] = Geospatial::status_message("Everything looks good! Please validate the data.");
		}

		$output["data"] = ob_get_contents();
		ob_end_clean();

		
		echo json_encode($output);

		die();

	}

	function finish_import() {
		echo 
	}
}

?>