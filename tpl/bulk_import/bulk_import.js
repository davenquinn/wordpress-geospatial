jQuery(document).ready(function($){
	$('#importPanel div').hide();
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
	});

});