jQuery(document).ready(function ($) {

	// admin page
	$('#bitly_settings-domain').change(function(){
		if ( $(this).attr('value') == 'Custom' ){
			$('#bitly_settings-custom-domain').show();
		}else{
			$('#bitly_settings-custom-domain').hide();
		}
	});

	$(".bitly-admin-num-results").change(function(){
		if ( parseInt($(this).attr('value')) > 15 ){
			$(this).attr('value', 15);
		}
	});


	$(document).on('change', '.bitly_widget_type_select', function(ev){
		el = $(ev.originalEvent.srcElement);
		if(el.val() === 'search'){
			el.parent().siblings('.sort_by_search').show();
		}else{
			el.parent().siblings('.sort_by_search').hide();
		}
	});

});

