// append a success message to let the user know
// the account has been returned as clean!
jQuery( document ).ready( function() {
	// remove the display="none" attr on load
	jQuery( '.remodal' ).removeAttr( 'style' );
	
	// setup our success variable box	
	var compromised_box = '<tr class="user-email-wrap">'
		+'<th><label for="email">'+translation.have_i_been_pwned+'<span class="description"><small><a href="https://haveibeenpwned.com/FAQs" target="_blank" title="'+translation.have_i_been_pwned+'" class="ecc_pwned_about_link">'+translation.about+'</a></small></span></label></th>'
		+'<td><div class="ecc-compromised-notice error warning ecc-profile-notification"><span class="dashicons dashicons-dismiss"></span> <p>'+translation.account_compromised_string+'</p></div></td>'
	+'</tr>';
	
	// append it
	jQuery( 'tr.user-email-wrap' ).after( compromised_box );
	
	// click function
	jQuery( '.compromised-report-data > h4' ).click( function() {	   
		jQuery( this ).parents( '.compromised-report-data' ).toggleClass( 'collapsed' );
		jQuery( this ).parents( '.compromised-report-data' ).find( '.breach-info' ).slideToggle();
		return false;
	});
		// stop propagation so our links still work
		jQuery( '.compromised-report-data > h4 > a' ).click( function( e ) {
			e.stopPropagation();
		});
});
// done-zo