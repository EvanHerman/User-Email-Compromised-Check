<?php
/*
Plugin Name: User Email Comrpomised Check
Plugin URI: https://www.evan-herman.com
Description: Check if the user email has been compromised at any point.
Version:1.0
Author: Evan Herman
Author URI: https://www.evan-herman.com
*/

// don't load directly
if ( ! defined( 'ABSPATH' ) ) die( '-1' );
/**
 * Current Plugin Verison
 */
if ( ! defined( 'ECC_VERSION' ) ) define( 'ECC_VERSION', '4.3.4' );
/**
 * Email_Comrpomised_Check starts here.
 *
 * @package Email_Comrpomised_Check
 * @since   0.1
 */
class Email_Comrpomised_Check {

	public function __construct() {
		// email done been pwned?
		add_action( 'admin_head' , array( $this , 'ecc_has_email_been_pwned' ) );
		// check bulk users at a tiz-ime
		 add_filter( 'admin_footer-users.php' , array( $this , 'ecc_bulk_users_been_pwned' ) );
		 // check for our bulk action on load
		 add_action( 'load-users.php' , array( $this , 'ecc_bulk_user_pwn_check' ) );
		 // add custom columns to user table
		 add_filter('manage_users_columns', array( $this , 'ecc_add_user_id_column' ) );
		 // display the status based on user meta
		 add_action('manage_users_custom_column', array( $this , 'ecc_show_user_compromised_status_column_content' ), 10, 3);
		 // enqueue styles in the proper hook
		 add_action( 'admin_enqueue_scripts' , array( $this , 'ecc_enqueue_styles' ) );
	}
	
	public function ecc_enqueue_styles($hook) {
		// load on users, profile and user edit pages
		if( 'users.php' == $hook || 'profile.php' == $hook || 'user-edit.php' == $hook ) {
			wp_enqueue_style( 'ecc-profile-styles' , plugin_dir_url( __FILE__ ) . 'css/min/ecc-profile-styles.min.css' );
		}
	}
	
	/*
	*	Check if were on user profile page
	*	else we abort!
	*/
	public function ecc_is_user_profile_page() {
		global $pagenow;
		if ($pagenow == 'profile.php' || $pagenow == 'user-edit.php') {
			return true;
		}
		return false;
	}
	
	/*
	*	Get the users/admins email
	*/
	public function ecc_get_user_email() {
		global $pagenow;
		if ($pagenow == 'profile.php') { // viewing your current profile
			global $current_user;
			get_currentuserinfo();
			return $current_user->user_email;
		} else 
		if ($pagenow == 'user-edit.php') { // editing another user
			$user_id = isset( $_GET['user_id'] ) ? (int) $_GET['user_id'] : '';
			$user_data = get_userdata( $user_id );
			return $user_data->user_email;
		}
	}
	
	/*
	*	Check email API Check
	*	send request over to https://haveibeenpwned.com/API/v2/breachedaccount/email
	*/
	public function ecc_has_email_been_pwned( $email='' ) {
		// double check we're on the user profile page
		if( $this->ecc_is_user_profile_page() ) {
			// send our API response
			$response = wp_remote_get( 'https://haveibeenpwned.com/api/v2/breachedaccount/'.urlencode( $this->ecc_get_user_email() ) );			
			// Check for error
			if ( is_wp_error( $response ) ) {
				return;
			}
			$response_code = wp_remote_retrieve_response_code( $response );
			
			// if the account returns a 404, this means this account has not been
			// found in the database, and therefore has not been pwned, let's load up some success message
			if( $response_code == '404' ) { // account is clean
				wp_register_script( 'account-clean' , plugin_dir_url( __FILE__ ) . 'js/min/account-clean.min.js' , array( 'jquery' ) );
				wp_enqueue_script( 'account-clean' );
				return;
			} else { // account was found to be compromised
				// enqueue style
				wp_enqueue_style( 'remodal-css' , plugin_dir_url( __FILE__ ) . 'css/min/remodal.min.css' );
				wp_enqueue_style( 'remodal-theme' , plugin_dir_url( __FILE__ ) . 'css/min/remodal-default-theme.min.css' , array( 'remodal-css' ) );
				// enqueue scripts
				wp_register_script( 'remodal-js' , plugin_dir_url( __FILE__ ) . 'js/min/remodal.min.js' , array( 'jquery' ) );
				wp_register_script( 'account-compromised' , plugin_dir_url( __FILE__ ) . 'js/min/account-compromised.min.js' , array( 'jquery' ) );
				wp_enqueue_script( 'remodal-js' );
				wp_enqueue_script( 'account-compromised' );
			}
			
			$body = wp_remote_retrieve_body( $response );
			
			 // Check for error
			if ( is_wp_error( $body ) ) {
				return;
			}
			
			$body_array = json_decode( $body , true );
			?>
				<div class="remodal" data-remodal-id="compromised-email-data" role="dialog" aria-labelledby="modal1Title" aria-describedby="modal1Desc" style="display:none;">
				  <button data-remodal-action="close" class="remodal-close" aria-label="Close"></button>
				  <h2 id="modal1Title"><?php printf( _n( '%s Breached Site', '%s Breached Sites', count( $body_array ), 'ecc-domain' ), count( $body_array ) ); ?></h2>
				  <div>
					<p id="modal1Desc">
					  <?php
						// print_r( $body_array );
						$i = 1;
						foreach( $body_array as $compromised_report ) {
							?>	
							<li class="compromised-report-data collapsed">
								<h4>
									<a href="<?php echo esc_url( $compromised_report['Domain'] ); ?>" target="_blank" title="<?php echo $compromised_report['Title']; ?>"><?php echo trim( $compromised_report['Title'] ); ?></a> 
									(<?php esc_attr_e( 'Breached' , 'ecc-domain' ); echo ': ' . date( 'F jS, Y' , strtotime( $compromised_report['BreachDate'] ) ); ?>)
								</h4>
								<span class="breach-info">
									<strong class="title"><?php esc_attr_e( 'Description' , 'ecc-domain' ); ?>:</strong>
									<?php echo apply_filters( 'the_content' , $compromised_report['Description'] ); ?>
									<strong class="title"><?php esc_attr_e( 'Potential Data Stolen' , 'ecc-domain' ); ?>:</strong>
									<p><?php echo implode( ', ' , $compromised_report['DataClasses'] ); ?></p>
									<strong class="title"><?php esc_attr_e( 'Affected' , 'ecc-domain' ); ?>:</strong>
									<p><?php echo number_format( $compromised_report['PwnCount'] ) . ' ' . __( 'email addresses stolen in this attack.' , 'ecc-domain' ); ?></p>
								</span>
							</li>
							<?php if( $i != count( $body_array ) ) { ?>
								<hr />
							<?php
							}
							$i++;
						}
					  ?>
					</p>
				  </div>
				  <br>
				  <button data-remodal-action="confirm" class="remodal-confirm"><?php esc_attr_e( 'OK' , 'ecc-domain' ); ?></button>
				</div>
			<?php	
		}
	}
	
	/*
	*	Add a custom action tot he bulk actions dropdown
	*/
	public function ecc_bulk_users_been_pwned() {
		?>
			<script type="text/javascript">
			  jQuery(document).ready(function() {
				jQuery('<option>').val('pwned').text('<?php _e('Is It Pwned?','ecc-domain')?>').appendTo("select[name='action']");
			  });
			</script>
		<?php	
	}
	 
	public function ecc_bulk_user_pwn_check() {
	 	if( isset( $_GET['action'] ) && $_GET['action'] == 'pwned' ) {		
			$user_array = isset( $_GET['users'] ) ? $_GET['users'] : array();
			// check if it's an array
			if( ! is_array( $user_array ) ) {
				$user_array = array( $_GET['users'] );
			}
			if( empty( $user_array ) ) {
				return;
			}
			// loop over passed in users
			foreach( $user_array as $user_id ) {
				// user_data here
				$user_data = get_userdata( $user_id );
				// get / store user email
				$user_email = $user_data->user_email; 
				// get it
				$response = wp_remote_get( 'https://haveibeenpwned.com/api/v2/breachedaccount/'.urlencode( $user_email ) );			
				// Check for error
				if ( is_wp_error( $response ) ) {
					return;
				}
				$response_code = wp_remote_retrieve_response_code( $response );
				if( $response_code == '404' ) {
					update_user_meta( $user_id , 'pwned' , 'account-clean' );
					return;
				} else {
					update_user_meta( $user_id , 'pwned' , 'account-compromised' );
					return;
				}
			}
			// redirect for the message!
			wp_redirect( esc_url_raw( add_query_arg( array( 'users' => urlencode( implode( ',' , $user_array ) ) ) ) , admin_url('users.php') ) , '200' );
			exit();
		}
	}
	
	
	/**
	*	Add additional user row to display if compromised or not!
	*	@since 0.1
	**/
	function ecc_add_user_id_column($columns) {
		$columns['pwned_status'] = __( 'Pwned Status' , 'ecc-domain' );
		return $columns;
	}
	 
	/*
	*	Display Visual representation of comrpomised status
	* @since 0.1
	*/
	function ecc_show_user_compromised_status_column_content($value, $column_name, $user_id) {
		$user = get_userdata( $user_id );
		if ( 'pwned_status' == $column_name ) {
			if( ! get_user_meta( $user_id , 'pwned' , true ) ) {
				$pwn_status_url = esc_url_raw( add_query_arg( array( 'action' => 'pwned' , 'users' => (int)$user_id ) ) );
				$value = '<a href="' . $pwn_status_url . '" class="button-secondary">' . __( 'Check Pwn Status' , 'ecc-domain' ) . '</a>';
			} else {
				if( get_user_meta( $user_id , 'pwned' , true ) == 'account-clean' ) {
					$value = '<span class="account-clean-column-notice"><span class="dashicons dashicons-yes"></span> ' . __( 'Email Safe' , 'ecc-domain' ) . '</span>';
				} else if ( get_user_meta( $user_id , 'pwned' , true ) == 'account-compromised' ) {
					$value = '<a href="' . get_edit_user_link( $user_id ) . '#email"<span class="account-compromised-column-notice"><span class="dashicons dashicons-no"></span> ' . __( 'Email Compromised' , 'ecc-domain' ) . '</span></a>';
				}
			}
			return $value;
		}
	}
}
/**
 * Main Email Compromised Check
 * @var Email_Comrpomised_Check
 */
global $Email_Comrpomised_Check;
$Email_Comrpomised_Check = new Email_Comrpomised_Check();
