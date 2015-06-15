// append a success message to let the user know
// the account has been returned as clean!
jQuery( document ).ready( function() {
	// setup our success variable box	
	var success_box = '<tr class="user-email-wrap">'
		+'<th><label for="email">'+translation.have_i_been_pwned+'<span class="description"><small><a href="https://haveibeenpwned.com/FAQs" target="_blank" title="'+translation.have_i_been_pwned+'" class="ecc_pwned_about_link">'+translation.about+'</a></small></span></label></th>'
		+'<td><div class="ecc-success-notice ecc-profile-notification"><span class="dashicons dashicons-shield-alt"></span> <p>'+translation.account_clean_string+'</p></div></td>'
	+'</tr>';
	// append it
	jQuery( 'tr.user-email-wrap' ).after( success_box );
});
// done-zo