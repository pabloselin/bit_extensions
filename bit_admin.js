jQuery(document).ready(function($) {
	console.log('admin script');
	$('#medialist li').hide();

	$('.importar_contenidos').on('click', function(e) {
		e.preventDefault();
		var playid = $(this).attr('data-play-id');
		$('#medialist li').each(function(idx, el) {
			var dataid = $(this).attr('data-id');
			var mediatype = $(this).attr('data-mediatype');
			console.log(dataid, mediatype);
			$.ajax({
				type: 'post',
				url: bit.ajaxurl,
				data: {
					action: "bit_ajax_assign_resource_to_wp",
					mediaid: dataid,
					type: mediatype
				},
				error: function( response ) {
					console.log('error:', response);
				},
				success: function( response ) {
					console.log(response);
					$('.play-messages[data-play-id="' + playid + '"]').empty().append('<strong>' + response + '</strong>');
				}
			});
		});
	})
});