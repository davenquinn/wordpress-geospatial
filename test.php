<?php

function test() {
	global $wpdb;
	$value = file_get_contents(GEOSPATIAL_PLUGINDIR."/test.json");
	$value = json_decode($value);
	echo "<pre>";
	print_r($value);
	echo "</pre>";
	$geometry = geoPHP::load($value, $this->type);

	$insert_string = $geometry->out('wkt');
	$wpdb->query("INSERT INTO ". GEOSPATIAL_TABLE_NAME . "(post_id, name, type, geom) values (". 
		$wpdb->escape($this->post_id).", ".
		"'".$wpdb->escape($this->name)."', ".
		"'".$wpdb->escape($this->type)."', ".
		"GeomFromText('".$wpdb->escape($insert_string)."'))");
}



?>