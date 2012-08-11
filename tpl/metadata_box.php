<?php
global $post;

if (is_georeferenced()) $wkt = $post->geometry->geom->out('wkt');
else $wkt = null;
?>

<div id="geospatial_metabox_inside">
    <div>
        <h3>Import</h3>
        <div>
            <input name="geospatial_import_url" />
            <input type="button" name="geospatial_import" value="Import from Google Maps URL" />
            <div id="alert_panel"></div>
        </div>
        Choose a file to upload: <input name="uploadedfile" type="file" /><input type="submit" value="Upload File" />
        <input type="button" name="geospatial_delete" value="Delete Feature" />
        <input type="button" name="geospatial_edit" value="Edit Feature" />

    </div>
    <div>
        <h3>Map</h3>
        <div id="map"></div>
    </div>
</div>
<input type="hidden" name="geospatial_wkt" value="<?php echo $wkt; ?>"/>
<?php wp_nonce_field('geospatial_postadmin_nonce','geospatial_noncename'); ?>

<script type="text/javascript">

jQuery(document).ready(function($){
    
    wkt = $('input[name=geospatial_wkt]').val();
    var data = new Geospatial.Data(wkt);

    window.gmap = new Geospatial.Map('map', data, true);

    $('input[name=geospatial_import]').click(function(){
        url = $('input[name=geospatial_import_url]').val();
        data.import.google_maps(url);
    });

    $('input[name=geospatial_edit]').click(function(){
        //window.mapa.editor.start();
    });

    $('input[name=geospatial_delete]').click(function(){
        alert("This will delete all geodata for this post. Are you sure?");
    });


});
</script>