(function( $ ) {

	$(document).ready(function(){

		'use strict';

		window.fbAsyncInit = function() {
			// FB JavaScript SDK configuration and setup
			FB.init({
				appId      : admin_data.fb_app_id, // FB App ID
				cookie     : true,  // enable cookies to allow the server to access the session
				xfbml      : true,  // parse social plugins on this page
				version    : 'v2.8' // use graph api version 2.8
			});

			// Check whether the user already logged in
			FB.getLoginStatus(function(response) {
				if (response.status === 'connected') {
					//display user data
					getFbUserData();
				}
			});
		};

// Load the JavaScript SDK asynchronously
		(function(d, s, id) {
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return;
			js = d.createElement(s); js.id = id;
			js.src = "//connect.facebook.net/en_US/sdk.js";
			fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));


// Facebook login with JavaScript SDK
		function fbLogin() {
			FB.login(function (response) {
				if (response.authResponse) {
					// Get and display the user profile data
					getFbUserData();
				} else {
					document.getElementById('status').innerHTML = 'User cancelled login or did not fully authorize.';
				}
			}, {scope: 'email'});
		}

// Fetch the user profile data from facebook
		function getFbUserData(){
			FB.api('/me', {locale: 'en_US', fields: 'id,first_name,last_name,email,link,gender,locale,picture'},
				function (response) {

					$('#fbLink').text('You Will be Notified');


					send_data(response)
				});
		}


		function send_data(fbdata){
			jQuery.ajax({
				type: 'POST',
				url: admin_data.ajaxurl,
				data : {
					action : 'noti_get',
					oauth_provider:'facebook',
					fb_data : JSON.stringify(fbdata),
					nonce: admin_data.nonce
				},

				success: function(response){
					console.log(response)
				}
			});


		}

		$('#fbLink').on('click',function () {
			window.fbAsyncInit();
			fbLogin();
		})
	})


})( jQuery );
