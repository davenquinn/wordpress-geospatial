<?php

class PostGeometry {

	var $id = null;
	var $post_id = null;
	var $geom = null; // Well-known-text geometry

	function __construct($id=null, $post_id, $geometry, $format) {
		$this->id = $id;
		$this->post_id = $post_id;
		$this->geom = geoPHP::load($geometry, $format);

		if (is_null($id)) $this->persist(); 

	}

	static function getInstanceFromDbRow($rowObject) {
		return new PostGeometry($rowObject->id, $rowObject->post_id, $rowObject->{"AsWKT(geom)"}, "wkt");
	}

	static function getInstanceFromDb($post_id = null) {
		if (($post_id != null) && ($post_id != '')) {
			global $wpdb;
			if (!is_null($row = $wpdb->get_row('SELECT id, post_id, AsWKT(geom) FROM ' . GEOSPATIAL_TABLE_NAME . ' WHERE post_id = ' . $post_id))) {
			//if (!is_null($row = $wpdb->get_row('SELECT * FROM ' . GEOSPATIAL_TABLE_NAME . ' WHERE post_id = ' . $post_id))) {
				return PostGeometry::getInstanceFromDbRow($row);
			} else {
				return null;
			}
		} else {
			return null;
		}
	}

	/**
	 * This method persists the image data to the database
	 * If no id was given before, an insert will be preformed
	 * Else this method does an update
	 **/

	function persist() {
		global $wpdb;

		$insert_string = $this->geom->out('wkt');
		if ($this->enable) $enable = "TRUE";
		else $enable = "FALSE";
		// If this image hasn't got an id yet
		if (is_null($this->id)) {
			// Image not persisted yet - We do an insert
			$query_string = "INSERT INTO ". GEOSPATIAL_TABLE_NAME . "(post_id, geom) values (". 
				$wpdb->escape($this->post_id).", ".
				"GeomFromText('".$wpdb->escape($insert_string)."'))";
			$wpdb->query($query_string);			

			// Additionally we want the primary id in case we need it afterwards
			$this->id = $wpdb->get_var('SELECT LAST_INSERT_ID() as id');
		} else {
			// We have this image already - We do an update
			$query_string = "UPDATE ". GEOSPATIAL_TABLE_NAME." SET ".
				"geom = GeomFromText('".$wpdb->escape($insert_string)."') ".
				"WHERE id = " . $this->id .";";
			$wpdb->query($query_string);
		}

	}

	function getGeomType() {
		return $this->geom->getGeomType();
	}

	function getJSONCoords() {
		$json_array = $this->geom->out('json', TRUE);
		return json_encode($json_array['coordinates']);

	}

	function delete() {
		global $wpdb;
		$wpdb->query('DELETE FROM ' . GEOSPATIAL_TABLE_NAME . ' WHERE id = ' . $this->id);
	}

}
?>