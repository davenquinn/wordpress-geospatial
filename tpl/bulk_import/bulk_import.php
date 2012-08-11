<div class="wrap">
	<h2>Bulk Import Geospatial Data</h2>
	<div id="bulk_import">
		<form id="importPanel">
			<ul>
				<li><a href="#url-field">URL from the web</a></li>
				<li><a href="#upload-field">File to upload</a></li>
			</ul>
			<div id="url-field">
				<input type="text" name="url" />
			</div>
			<div id="upload-field">
				<input type="file" name="upload" />
			</div>
			<input id="bulk_import_submit" type="button" value="Go" />

		</form>
		<div id="geospatial_bulk_import_options" class="postbox">
			<h3>Options</h3>
			<div class="inside">
				<form>
					<label for="baseName">Base Name for Imported Features</label><br/>
					<input type="text" name="baseName" />
					<fieldset>
						<legend>GeoJSON import</legend>
						<input type="checkbox" name="includeTitleDate" />
						<label for="includeTitleDate">Try to find title and date</label><br/>
						<input type="checkbox" name="includeAttrs" />
						<label for="includeAttrs">Include attributes as custom fields</label>
					</fieldset>
				</form>
			</div>
		</div>
	</div>
	<?php wp_nonce_field('geospatial_bulk_import_nonce','geospatial_noncename'); ?>
</div>