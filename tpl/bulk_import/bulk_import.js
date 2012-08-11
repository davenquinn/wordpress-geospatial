jQuery(document).ready(function($){
	/* $('#importPanel div').hide();
	$('#importPanel div:first').show();
	$('#importPanel ul li:first').addClass('active');
	var currentTab = $('#importPanel ul li:first').attr('href');
	var hoverTab;

	$('#importPanel ul li a').click(function(){
		$('#importPanel ul li').removeClass('active');
		$(this).parent().addClass('active');
		var currentTab = $(this).attr('href');
		$('#importPanel div').hide();
		$(currentTab).show();
		return false;
	}); */
	$('input#bulk_import_submit').click(function(){
		nonce = $('input#geospatial_noncename').val();
	    $.ajax({
	        url: ajaxurl,
	         data: { 
	            action: 'geospatial-start-import',
	            nonce: nonce
	         },
	         type: 'post',
	         success: function(output) {
	         	output = JSON.parse(output);
	         	$('#bulk_import').addClass("validate").html("<div id='alert_panel'></div><div id='features_preview'></div>");
	         	$('#features_preview').html(output.data);
	            $('#alert_panel').html(output.status.message);
	            $('#alert_panel').removeClass()
	            if(output.status.error) {
	            	$('#alert_panel').addClass('error');
	            } else {
	            	$('#alert_panel').addClass('ok');
	            }

	            on_validate();
	        }
	    });
	    return false;
    });

function on_validate() {
    // Validation functions
    $('input[name=validate_submit]').click(function() {
		nonce = $('input#geospatial_noncename').val();
	    $.ajax({
	        url: ajaxurl,
	         data: { 
	            action: 'geospatial-finish-import',
	            nonce: nonce
	         },
	         type: 'post',
	         success: function(output) {
	         	output = JSON.parse(output);
	        }
	    });
	    return false;   	
    });
    $('input[name=validate_cancel]').click(function() {
	    location.reload();
	    return true;  	
    });
}

});