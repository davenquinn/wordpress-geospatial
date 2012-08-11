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

function geospatial_map($post = null) {
	if (is_null($post)) global $post;
	if (!is_georeferenced($post)) return;

	$json = $post->geometry->geom->out('json');
	$map_id = "map_".$post->ID;
?>
<div class="geospatial map" id="<?php echo $map_id; ?>"></div>

<script type="text/javascript">

jQuery(document).ready(function($){
	json = '<?php echo $json; ?>';
	data = new Geospatial.Data(json, 'json');
    map = new Geospatial.Map(
    	"<?php echo $map_id; ?>",
    	data, 
    	false
    );
    console.log(map);


});
</script>

<?php } ?>