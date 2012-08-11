var proj = new OpenLayers.Projection("EPSG:4326");
var $ = jQuery;

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

var Geospatial = new function() {
	var $ = jQuery;
	OpenLayers.Lang.setCode('en');
    OpenLayers.Layer.Vector.prototype.renderers = ["SVG2", "VML", "Canvas"];
	this.basemapRenderer = new OpenLayers.Layer.OSM();

	//new OpenLayers.Layer.Google("Google Physical",{type: google.maps.MapTypeId.TERRAIN}));

	this.Admin = function() {

	}

    this.Data = function(input, format) {
    	$ = jQuery;
    	parent = this;
    	this.map = false;
    	if (input !== "") this.in(input, format);
    	this.updateFromWKT = function(output) {
    		//var wkt = new OpenLayers.Format.WKT();
    		features = this.wktParser.read(output);
    		if(features) {
    			this.update(features);
    			//this.features = features;
    			//this.computeBounds();
    			if (this.map) {
    				this.map.updateData(features);
    				this.map.zoomFit();
    			}
			} else {
			   	alert("Bad WKT");
			}
    	};
    	this.import = new function() {
    		this.parent = parent;
    		parent = this;
    		this.google_maps = function(url){
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
			    		parent.parent.updateFromWKT(output);
			            $('#alert_panel').html("Successfully added layer.");
			        }
			    });
			    return false;
    		};
    	}
    	this.import.prototype = {
    		parseWKT: function() {
				var wkt = new OpenLayers.Format.WKT();
			    var features = wkt.read(input);
			    if(features) {
			        geospatial.map.editor.editLayer.destroyFeatures();
			        geospatial.map.editor.editLayer.addFeatures(features);
			        geospatial.map.zoomFit();
			        $('input[name=geospatial_wkt]').html(input);
			    } else {
			        alert('Bad WKT');
			    }
    		}
    	};
	};
	this.Data.prototype = {
		wktParser: new OpenLayers.Format.WKT({
			'externalProjection': new OpenLayers.Projection("EPSG:4326"),
			'internalProjection': new OpenLayers.Projection("EPSG:900913")
		}),
		JSONParser: new OpenLayers.Format.GeoJSON({
			'externalProjection': new OpenLayers.Projection("EPSG:4326"),
			'internalProjection': new OpenLayers.Projection("EPSG:900913")
		}),
		computeBounds: function() {
			features = this.features;
			console.log(this.features);
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
			console.log(this.bounds);
		},
		update: function(features) {
			if(features.constructor != Array) {
	            features = [features];
	        }
			this.features = features;
			/*if (this.map && this.map.features !== this.features) {
				this.map.updateData(features);
			}*/
			this.computeBounds();
			wkt = this.out();
			field = jQuery('input[name=geospatial_wkt]')
			field.val(wkt);
		},
		in: function(input, format) {
			if (typeof(format)==="undefined") format="wkt";
			this.loaded = true;
			switch(format) {
				case "wkt":
					this.features = this.wktParser.read(input);
					break;
				case "json":
					this.features = this.JSONParser.read(input);
					break;
			}
	        if(!this.features) return;
	        if(this.features.constructor != Array) {
	            this.features = [this.features];
	        }
			this.computeBounds();
		},
		out: function() {
			features = this.features;
			if (features.length === 1) features = features[0];
			out = this.wktParser.write(features);
			return out;
		}

	};


	this.Map = function (mapDiv, data, editorEnabled) {
		if(typeof(editorEnabled)==='undefined') editorEnabled = false;
		if (editorEnabled) {
			this.Editor = function (map) {
				$ = jQuery;
				parent = this;
				OpenLayers.Editor.call(this, map, {
			        activeControls: ['Navigation', 'SnappingSettings', 'Separator', 'DeleteFeature', 'SelectFeature', 'Separator', 'DrawHole', 'ModifyFeature'],
			        featureTypes: ['polygon', 'path', 'point']
			    });
				viewport = $(map.viewPortDiv);
				editControl = viewport.append("<a class='startEditing'><span></span></a>");
				this.editControl = viewport.children('.startEditing');
				this.editControl.click(function(){
					parent.start();
				});

				this.editorPanel.filterDraw = function() {
					div = jQuery(this.div);
					cancel = "<input class='olButton cancel' type='button' value='Cancel' />";
					done = "<input class='olButton done' type='button' value='Done' />";
					separator = "<div class='olControlSeparatorItemInactive olButton'></div>";
					div.prepend(cancel+done+separator);
					this.cancelButton = div.children(".olButton.cancel");
					this.doneButton = div.children(".olButton.done");
					jQuery(".olButton.done").on('click.editor-enabled', function(){
			            parent.stop();
			            parent.save();
			        });
			       	jQuery(".olButton.cancel").on('click.editor-enabled', function(){
			            parent.stop();
			        });
			        $(document).keyup(function(e) {
						if (e.keyCode == 27 & this.editMode == true) parent.stop();   // esc	
					});
				};

				this.editorPanel.draw = function() {
			    	OpenLayers.Editor.Control.EditorPanel.prototype.draw.apply(this, arguments);
			    	this.filterDraw();
			   		return this.div;
			    };
				this.editorPanel.redraw = function() {
			    	OpenLayers.Editor.Control.EditorPanel.prototype.redraw.apply(this, arguments);
			    	this.filterDraw();
			   		return this.div;
			    };

			};

			this.Editor.prototype = jQuery.extend(new OpenLayers.Editor(), {
				start: function() {
					this.map.popOut();
			       	this.startEditMode();
			        
				},
				stop: function(){
				   	this.stopEditMode();
				   	this.editorPanel.doneButton.off('click.editor-enabled');
				   	this.editorPanel.cancelButton.off('click.editor-enabled');
				   	this.map.popIn();
				},
				save:function() {
					features = this.editLayer.features;
					this.map.data.update(features);
				}
			});
		}

		$ = jQuery;
		bounds = new OpenLayers.Bounds(-20037508.34, -20037508.34, 20037508.34, 20037508.34);
		OpenLayers.Map.call(this, mapDiv, {
			maxExtent: bounds,
			controls: [
				new OpenLayers.Control.Zoom(),
				new OpenLayers.Control.Navigation({
		            dragPanOptions: {
		                enableKinetic: true
		            }
			    }),
			]
		});
		
		this.data = data;
		this.data.map = this;
		this.editorEnabled = editorEnabled;
		
		if (this.editorEnabled) {
			this.editor = new this.Editor(this);
			this.dataLayer = this.editor.editLayer;
			if (this.data.loaded) {
				this.dataLayer.features = this.data.features;
			}
		} else {
		    this.dataLayer = new OpenLayers.Layer.Vector("DataLayer");
		    this.addLayer(this.dataLayer);

		    this.dataLayer.addFeatures(this.data.features);
		}

    	var parent = this;
    	zoomControl = $(this.viewPortDiv).children('.olControlZoom');
		zoomControl.append("<a class='zoomToExtent olButton'><span></span></a>");
		zoomToBounds = zoomControl.children('a.zoomToExtent');
		zoomToBounds.click(function(){
			parent.zoomFit();
		});

    	this.addLayer(Geospatial.basemapRenderer);
    	
    	this.zoomFit();


	};
	this.Map.prototype = jQuery.extend(new OpenLayers.Map(), {
		data: {loaded: false}, // Temporary; should be a data object.
		editorEnabled: false,
		maximized: false,
		zoomFit: function() {
			if (this.data.bounds == "" || typeof(this.data.bounds) ==="undefined") {
    			this.setCenter(new OpenLayers.LonLat(0, 0),0);
			} else {
				this.zoomToExtent(this.data.bounds);
			}
		},
		updateData: function(features) {
			this.dataLayer.destroyFeatures();
			this.dataLayer.addFeatures(features);
			//this.dataLayer.features = features;
			//this.dataLayer.redraw({force: true});
		},
		popOut: function() {
			var $ = jQuery;
			if (this.maximized) return false;

			$(this.div).addClass("maximized");

			document.scroll.lock();

		    h = $(window).height();
		    w = $(window).width();
		    $(this.viewPortDiv).height(h-80).width(w-80);

		    this.updateSize();

		    $(window).on('resize.map-maximized', function() {
		        h = $(window).height();
		    	w = $(window).width();
		    	$(this.viewPortDiv).height(h-80).width(w-80);
		    });
		   	this.maximized = true;

		},
		popIn: function () {
			var $ = jQuery;
			if (!this.maximized) return false;
			$(this.div).removeClass("maximized");
		    $(this.viewPortDiv).width("inherit").height("inherit");
		    $(window).off('resize.map-maximized');
		    document.scroll.unlock();
		    this.updateSize();
		    this.maximized = false;
		}
	});
}


