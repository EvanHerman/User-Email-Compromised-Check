// append a success message to let the user know
// the account has been returned as clean!
jQuery( document ).ready( function() {
	// remove the display="none" attr on load
	jQuery( '.remodal' ).removeAttr( 'style' );
	
	// setup our success variable box	
	var compromised_box = '<tr class="user-email-wrap">'
		+'<th><label for="email">Have I Been Compromised?<span class="description"><small><a href="https://haveibeenpwned.com/FAQs" target="_blank" title="Have I Been Pwned?">about</a></small></span></label></th>'
		+'<td><div class="ecc-compromised-notice error warning ecc-profile-notification"><span class="dashicons dashicons-dismiss"></span> <p>This email address appears to have been compromised. To view more info, please <a href="#compromised-email-data" class="ecc-account-compromised-modal">click here</a>.</p></div></td>'
	+'</tr>';
	
	// append it
	jQuery( 'tr.user-email-wrap' ).after( compromised_box );
	
	jQuery( '.compromised-report-data > h4' ).click( function() {	   
		jQuery( this ).parents( '.compromised-report-data' ).toggleClass( 'collapsed' );
		jQuery( this ).parents( '.compromised-report-data' ).find( '.breach-info' ).slideToggle();
		return false;
	});
		jQuery( '.compromised-report-data > h4 > a' ).click( function( e ) {
			e.stopPropagation();
		});
});
// done-zo