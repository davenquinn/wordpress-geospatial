<?php

require('../../../wp-blog-header.php');
$query_vars = $_SERVER['QUERY_STRING'];

function geojson_query ($query_vars) {
	query_posts( $query_vars );
	//apply_filters
	$features = array();
	while ( have_posts() ) : the_post();
		global $post;
		if (!is_null($post->geometry)) {
			echo "<pre>";
			print_r($post->geometry);
			echo "</pre>";
			$features[] = $post->geometry->geom->out('json', TRUE);
		}
	endwhile;

	wp_reset_query();

	$arr = array(
		"type"=> "FeatureCollection",
		"features" => $features
		);

	echo json_encode($arr);
}

geojson_query($query_vars);

?>