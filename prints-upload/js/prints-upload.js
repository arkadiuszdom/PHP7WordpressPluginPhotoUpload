function get_original_file( filename ) {
	jQuery.ajax( {
    	url: ( typeof ajaxurl !== "undefined" ? ajaxurl : front_ajax_object.ajax_url ),
    	type: "GET",
    	data: {
    		action: 'get_file',
    		filename: filename
    	}
	}).done( function( response ) {
		console.log( response );
	}).fail( function() {
		console.log( "fail" );
	});
}

function save_file_recursively( file_counter ) {
	if( current_upload_files_number == file_counter) {	
		currently_uploading = false;
		jQuery('#prints_upload_input').val("");
		return;
	}  

	let formData = new FormData();
	formData.append( 'action', 'save_file' );
	formData.append( 'file', current_upload_files[ file_counter ] );
	formData.append( 'product_id', current_upload_product_id );

	//suppose that image will be uploaded

	jQuery.ajax( {
    	url:  ajaxurl,
    	type: "POST",
    	data: formData,
    	contentType: false,
    	processData: false
	}).done( function( response ) {
		let response_object = JSON.parse( response );
		if( !response_object ){
			alert('Błędna odpowiedź serwera');
		} else if (response_object.url){//success
			//prepend('<div class="uploaded_image_container"><img class="uploaded_image_thumbnail" src="'+response_object.url+'"/><div class="delete_uploaded_image"/>X</div>');
			current_upload_files_container.prepend('<img src="'+response_object.url+'"/>');
			current_upload_uploaded_files_number++;
		} else if (response_object.error){
			alert(response_object.error);
		} else {
			alert('Błąd zapisu');
		}
		
		//const url_splitted = url.split("/");
		//const filename = url_splitted[url_splitted.length-1];
		
		//$(event.target).siblings('.uploaded_files').prepend('<button onclick=get_original_file("'+filename+'") type="button"><img src="'+url+'"/></button>');
		
	}).fail( function() {
		alert('Błąd serwera');
	}).always( function() {
		save_file_recursively( file_counter + 1 );
	});
}

var ajaxurl = ( typeof ajaxurl !== "undefined" ? ajaxurl : front_ajax_object.ajax_url );

var currently_uploading = false;

var current_upload_files;

var current_upload_product_id;
var current_upload_files_container;

var	current_upload_uploaded_files_number;
var	current_upload_files_number;

var last_deleted_product_id;

jQuery( document ).ready( function( $ ) {
	$( ".files_upload_form" ).submit(function( event ) {
  		event.preventDefault();


  		if( currently_uploading ) {
  			alert( "Poczekaj na zakończenie wgrywania zdjęć!");
  		} else {
			//globals
			current_upload_product_id = $(event.target).attr('product_id');
			current_upload_files = event.target[0].files;
			current_upload_files_container = $(event.target).siblings('.uploaded_files');
	  		current_upload_uploaded_files_number = current_upload_files_container.children().length;
			current_upload_files_number = current_upload_files.length;

	  		let file_counter = 0;
			let current_upload_max_quantity = $(event.target).attr('max_quantity');


			if ( !current_upload_files_number ){
				jQuery('#prints_upload_input').val("");
				alert('Wybierz zdjęcia!');	
			} else if ( current_upload_max_quantity < current_upload_uploaded_files_number + current_upload_files_number) {	
				jQuery('#prints_upload_input').val("");
				alert('Zakupiłeś jednynie ' + current_upload_max_quantity + '!' );	
			} else {
				currently_uploading = true;			
				save_file_recursively(file_counter);//doing recursively in order to get thumbnails being continously added
			}
  		}
	});


	//ajax cart manipulation handling 

	//hide on upload container on cart item remove
	$( ".product-remove a" ).click(function( event ) {
		let product_id = $(event.target).attr("data-product_id");
		console.log('sdas');

		let maybe_file_upload_container = $('.file_upload_container[data-product_id="'+product_id+'"]');
		if( maybe_file_upload_container ) {
			last_deleted_product_id = product_id;
			maybe_file_upload_container.toggle();
		}
	});

	//show on upload container on cart item restore
	var observer = new MutationObserver( function() {
		$( ".restore-item" ).click(function( event ) {
			if( last_deleted_product_id ) {
				let maybe_file_upload_container = $('.file_upload_container[data-product_id="'+last_deleted_product_id+'"]');
				if( maybe_file_upload_container ) {
					maybe_file_upload_container.toggle();
				}
			}
		});
	});
	observer.observe( $( ".woocommerce-notices-wrapper" ).get( 0 ), {childList: true});

	/*window.onbeforeunload = function() {//try to prevent leaving page without uploading all of the prints
		let not_enough_photos_uploaded = false;
		$('.files_upload_form').each( function() {
			if ( $(this).attr('max_quantity') > $(this).siblings('.uploaded_files').children().length ) {//we shouldnt have more prints uploaded then selected
				not_enough_photos_uploaded = true;
				return;//break callback execution
			}
		});
		if( not_enough_photos_uploaded ){
			return "Wgraj zdjęcia dla każdej zakupionej odbitki!";//custom message doesnt work in most of the browsers 
		}
	}*/
});
/*
jQuery( document ).ready( function( $ ) {
	//while(true) {
		get_original_file('asf');
		console.log('contin22e');
	//}
	$( ".files_upload_form" ).submit(function( event ) {
  		event.preventDefault();
  		let uploaded_files_number = $(event.target).siblings('.uploaded_files').children().length;

    	jQuery.each( event.target[0].files, function() {
    		if ( >uploaded_files_number) {
    			
	  			let formData = new FormData();
	    		formData.append( 'action', 'save_file' );
	        	formData.append( 'file', this );
	        	formData.append( 'product_id', $(event.target).attr('product_id') );

				uploaded_files_number++;//suppose that image will be uploaded

	        	jQuery.ajax( {
			    	url:  ajaxurl,
			    	type: "POST",
			    	data: formData,
			    	contentType: false,
			    	processData: false
				}).done( function( response ) {
					let response_object = JSON.parse( response );
					if( !response_object ){
						uploaded_files_number--;//image wasn't uploaded
						alert('Błędna odpowiedź serwera');
					} else if (response_object.url){
						console.log('prep');
						$(event.target).siblings('.uploaded_files').prepend('<img src="'+response_object.url+'"/>');
					} else if (response_object.error){
						uploaded_files_number--;//image wasn't uploaded
						alert(response_object.error);

					} else {
						uploaded_files_number--;//image wasn't uploaded
						alert('Błąd serwera');
					}
					
					//const url_splitted = url.split("/");
					//const filename = url_splitted[url_splitted.length-1];
					
					//$(event.target).siblings('.uploaded_files').prepend('<button onclick=get_original_file("'+filename+'") type="button"><img src="'+url+'"/></button>');
					
				}).fail( function() {
					alert('Błąd');

					uploaded_files_number--;//image wasn't uploaded
				});
			} else {
				alert( 'Wgrałeś plik dla każdej zamówionej odbitki tej kategorii');
			}
   		});
		
	});
});*/