jQuery( document ).ready( function( $ ) {
	$( "#plugin_configuration" ).submit(function( event ) {
  		event.preventDefault();

    	$.ajax( {
	    	url: ajaxurl,
	    	type: "POST",
	    	data: $(this).serialize(),
    		dataType: "json"
		}).done( function( response ) {
			if( response.success ) {
				alert( 'Konfiguracja zapisana' );
			} else {
				alert( response.data );
			}
		}).fail( function() {
			alert( 'Internal server error!' );
		});
		
	});
});