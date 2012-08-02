<?php 

function is_georeferenced ($post=null) {
   if (is_null($post)) global $post;
	if (!property_exists($post, 'geometry')) {
		if (!is_null($geometry = PostGeometry::getInstanceFromDb($post->ID))) {
			
			$post->geometry = $geometry;
			$result = true;
			
		}
	} elseif (!is_null($post->geometry)) {
		$result = true;
		
	}

	return $result;
}

?>