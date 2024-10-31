(function( $ ) {
	'use strict';


	$(document).ready(function () {
		jQuery.ajaxSetup({
			type: 'POST',
			url: admin_data.ajaxurl,
			beforeSend: function() {
				$('.bubblingG').show();
				setTimeout(function(){ console.log("Hello"); }, 3000);
			},
			complete: function(){

				$('.bubblingG').hide();
			},

		});

		var inputId = $('input[name="id"]'),
		 notificationTextInput =	$('textarea[name="txp_notification_text"]');

			//send notification
		$('body').on('click','.txp-send-notification-btn',function () {
			var checkedValue = [];
			if($(this).parent('td').length>0) {
				checkedValue.push( getRowColumnValue($(this), '.selected_id', true))
				notificationTextInput.val('Hello '+ getRowColumnValue($(this), '.first_name'));
				console.log(checkedValue);
			}else{
				$('.selected_id:checked').each(function (index,item) {
					checkedValue.push($(item).val())
					notificationTextInput.val('Hello Everybody');
				});
			}
			inputId.val(JSON.stringify(checkedValue));

			notificationTextInput.focus();
		
		})

		//delete function
		$('body').on('click','.txp-send-notification-delete-btn',function () {
			var checkedValue = [];
			$('body').append('<div class="loader"></div>');
			$('.selected_id:checked').each(function (index,item) {
				checkedValue.push($(item).val())
			});
			var id_value	 = JSON.stringify(checkedValue);
			var nonce		 = $('input[name="txp_admin_ajax"]').val();
			jQuery.ajax({
				data : {
					action : 'delete_data',
					nonce:nonce,
					id: id_value,
				},

				success: function(response){

					$('.txp-table-div').html('').html(response);
					console.log(response);

				}
			});
		})

		$('#txp_notification_text').on('keydown blur',function () {

			var taLength = $(this).val().length;
			$('.txp-txt-length').html(taLength);
			if(parseInt(taLength)>180){
				$('.txp-txt-length').html(taLength+" More than 180 Character is not supported by Facebook. <a target='_blank' href='https://developers.facebook.com/docs/games/services/appnotifications#bestpractices'>Visit here</a> to know more ");
			}
		})

		function getRowColumnValue(context,selector_class,input){
			var ownRowContext = context.closest('tr');
			if(!input){
				return ownRowContext.find(selector_class).clone().children().remove().end().text();
			}else{
				return ownRowContext.find(selector_class).val();

			}
		}

		$('#txp-notification-form').on('submit',function (e) {

			e.preventDefault();
			var id 		=  inputId.val();
			console.log(id);
			var notification_text 	=   notificationTextInput.val();
			var notification_link 	=  $('input[name="txp_notification_link"]').val();
			var nonce				= $('input[name="txp_admin_ajax"]').val();


			jQuery.ajax({
				data : {
					action : 'notification_data',
					nonce:nonce,
					id: id,
					notification_text: notification_text,
					notification_link:notification_link
				},

				success: function(response){
					var error_obj = JSON.parse(response),
						gen_html = [];


					$.each(error_obj,function (a,b) {
						if(b.hasOwnProperty('name')){
							gen_html.push("<p class='error-message'><b>"+b.name+"</b>"+b.message+"</p>")
						}else if(b.hasOwnProperty('success')){
							gen_html.push("<p class='sucess-message'>"+b.success+"</p>")
						} else{
							gen_html.push("<p class='error-message'>"+b.message+"</p>")
						}
						
					})
					$('.txp-notification-output').html(gen_html.toString())

				}
			});

		})

	})
	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

})( jQuery );
