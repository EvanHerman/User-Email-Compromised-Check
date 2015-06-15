// append a success message to let the user know
// the account has been returned as clean!
jQuery( document ).ready( function() {
	// setup our success variable box	
	var success_box = '<tr class="user-email-wrap">'
		+'<th><label for="email">Have I Been Compromised?<span class="description"><small><a href="https://haveibeenpwned.com/FAQs" target="_blank" title="Have I Been Pwned?">about</a></small></span></label></th>'
		+'<td><div class="ecc-success-notice ecc-profile-notification"><span class="dashicons dashicons-lock"></span> <p>This email address does not appear to have been compromised.</p></div></td>'
	+'</tr>';
	// append it
	jQuery( 'tr.user-email-wrap' ).after( success_box );
});
// done-zo