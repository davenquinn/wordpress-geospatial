var proj = new OpenLayers.Projection("EPSG:4326");
var $ = jQuery;

function google_maps_import(url) {
	nonce = $('input#geospatial_noncename').val();
    $.ajax({
        url: ajaxurl,
         data: { 
            action: 'geospatial-google-import',
            url: url,
            nonce: nonce
         },
         type: 'post',
         success: function(output) {
            parseWKT(output);
            $('#alert_panel').html("Successfully added layer.");
        }
    });
    return false;
}



function parseWKT(input) {
	var wkt = new OpenLayers.Format.WKT();
    var features = wkt.read(input);
    console.log(input);
    if(features) {
        geospatial.map.editor.editLayer.destroyFeatures();
        geospatial.map.editor.editLayer.addFeatures(features);
        //updateTextField();
        //geospatial.map.zoomFit();
    } else {
        alert('Bad WKT');
    }
}

document.scroll = new function() {
	this.locked = false;

	this.lock = function () {
		if (this.locked) return false;
		// lock scroll position, but retain settings for later
        this.scrollPosition = [
        	self.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft,
        	self.pageYOffset || document.documentElement.scrollTop  || document.body.scrollTop
        ];
        var html = jQuery('html'); // it would make more sense to apply this to body, but IE7 won't have that
        this.previousOverflow = html.css('overflow');
        html.css('overflow', 'hidden');
        window.scrollTo(this.scrollPosition[0], this.scrollPosition[1]);
        this.locked = true;
        return true;		
	};
	this.unlock = function() {
		if (!this.locked) return false;
        // un-lock scroll position
        var html = jQuery('html');
        html.css('overflow', this.previousOverflow);
        window.scrollTo(this.scrollPosition[0], this.scrollPosition[1]);
        return true;
    };
    this.toggle = function(){
    	if (!this.locked) this.lock();
    	if (this.locked) this.unlock();
    };

};

OpenLayers.Map.prototype.maximized = false;
OpenLayers.Map.prototype.popOut = function() {
	var $ = jQuery;
	if (this.maximized) return false;

	$(this.div).addClass("maximized");

	document.scroll.lock();

    h = $(window).height();
    w = $(window).width();
    $(this.viewPortDiv).height(h-80).width(w-80);

    this.updateSize();

    $(window).on('resize.map-maximized', function() {
    	console.log('resized');
        h = $(window).height();
    	w = $(window).width();
    	$(this.viewPortDiv).height(h-80).width(w-80);
    });
   	this.maximized = true;

};
OpenLayers.Map.prototype.popIn = function () {
	var $ = jQuery;
	if (!this.maximized) return false;
	$(this.div).removeClass("maximized");
    $(this.viewPortDiv).width("inherit").height("inherit");
    $(window).off('resize.map-maximized');
    this.updateSize();
    this.maximized = false;
};

OpenLayers.Editor.Control.EditorPanel.prototype._filterDraw = function() {
	div = jQuery(this.div);
	cancel = "<input class='olButton cancel' type='button' value='Cancel' />";
	done = "<input class='olButton done' type='button' value='Done' />";
	separator = "<div class='olControlSeparatorItemInactive olButton'></div>";
	div.prepend(cancel+done+separator);
	this.cancelButton = div.children(".olButton.cancel");
	this.doneButton = div.children(".olButton.done");

};

var geospatial = new function() {
	var $ = jQuery;
	OpenLayers.Lang.setCode('en');
    OpenLayers.Layer.Vector.prototype.renderers = ["SVG2", "VML", "Canvas"]; 
	this.map = null;
	this.basemapRenderer = new OpenLayers.Layer.OSM();

	//new OpenLayers.Layer.Google("Google Physical",{type: google.maps.MapTypeId.TERRAIN}));

	this.admin = new function() {

	}

    this.data = new function() {
		var wktParser =  new OpenLayers.Format.WKT({
			'externalProjection': new OpenLayers.Projection("EPSG:4326"),
			'internalProjection': new OpenLayers.Projection("EPSG:900913")
		});
		this.loaded = false;
		this.in = function(input) {
			console.log(input);
	        this.features = wktParser.read(input);
	        if(!this.features) return;
	        if(this.features.constructor != Array) {
	            this.features = [this.features];
	        }
	        this.loaded = true;
			this.computeBounds();
		};

		this.out = function() {
			features = this.features;
			if (features.length === 1) features = features[0];
			return wktParser.write(features);
		};
		this.computeBounds = function() {
			features = this.features;
			if (features == "") {
				this.bounds = "";
				return;
			}
			for(var i=0; i<features.length; ++i) {
				if (!this.bounds) {
				    this.bounds = features[i].geometry.getBounds();
				} else {
				    this.bounds.extend(features[i].geometry.getBounds());
				}
			}
		};
	};

	this.createMap = function(mapDiv, editorEnabled) {
		if(typeof(editorEnabled)==='undefined') editorEnabled = false;
		var parent = this;

		var _mapInit = function() {
			var $ = jQuery;
			
			var map = new OpenLayers.Map(mapDiv, {
	        	maxExtent: new OpenLayers.Bounds(-20037508.34, -20037508.34, 20037508.34, 20037508.34),
	    	});
	    	//map.dataLayer = new OpenLayers.Layer.Vector("DataLayer");
	    	//map.addLayer(map.dataLayer);
	    	//map.dataLayer.addFeatures(parent.data.features);


			map.zoomFit = function() {

				if (!parent.data.loaded) return;

				map.zoomToExtent(parent.data.bounds);
			};

	    	var _createZoomControl = function() {
				zoomControl = $(map.viewPortDiv).children('.olControlZoom');
				zoomControl.append("<a class='zoomToExtent olButton'><span></span></a>");
				zoomToBounds = zoomControl.children('a.zoomToExtent');
				zoomToBounds.click(function(){
					map.zoomFit();
				});
	    	};
	    	_createZoomControl();
	    	map.addLayer(parent.basemapRenderer);
	    	map.zoomFit();

	    	return map;			
		};

		var _editorInit = function(map) {
			var $ = jQuery;

	    	var editor = new OpenLayers.Editor(map, {
	        	activeControls: ['Navigation', 'SnappingSettings', 'Separator', 'DeleteFeature', 'SelectFeature', 'Separator', 'DrawHole', 'ModifyFeature'],
	        	featureTypes: ['polygon', 'path', 'point']
	    	});
	    	//editor.editLayer = editor.map.dataLayer;
	    	//editor.editLayer.refresh();

	    	var _createEditControl = function() {
	    		viewport = $(map.viewPortDiv);
				editControl = viewport.append("<a class='startEditing'><span></span></a>");
				editor.editControl = viewport.children('.startEditing');
				editor.editControl.click(function(){
					editor.start();
				});
	    	};

			editor.editorPanel.draw = function() {
	        	OpenLayers.Editor.Control.EditorPanel.prototype.draw.apply(this, arguments);
	        	editor.editorPanel._filterDraw();
	       		return this.div;
		    };

			editor.editorPanel.redraw = function() {
	        	OpenLayers.Editor.Control.EditorPanel.prototype.redraw.apply(this, arguments);
	        	editor.editorPanel._filterDraw();
	       		return this.div;
		    };

	    	editor.start = function() {
	    		editor.map.popOut();
		       	editor.startEditMode();

		        editor.editorPanel.doneButton.on('click.edit-enabled', function(){
		            editor.stop();
		        });
		        editor.editorPanel.cancelButton.on('click.edit-enabled', function(){
		            editor.stop();
		        });
		        $(document).keyup(function(e) {
  					if (e.keyCode == 27 & editor.editMode == true) editor.stop();   // esc					
  				});
	    	};

	    	editor.stop = function(){
			   	editor.stopEditMode();
			   	editor.editorPanel.doneButton.off('click.edit-enabled');
			   	editor.editorPanel.cancelButton.off('click.edit-enabled');
			   	editor.map.popIn();
	    	};

	    	_createEditControl();
	    	return editor;
    	};


    	this.map = _mapInit();
		if (editorEnabled) {
			this.map.editor = _editorInit(this.map);
			if (this.data.loaded) {
				this.map.editor.editLayer.destroyFeatures();
				this.map.editor.editLayer.addFeatures(this.data.features);
			}
		}


        return this.map;

	};

}
