<?php
/**
 * Welcome page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="wrap uncode-wrap" id="option-tree-settings-api">

	<?php echo uncode_admin_panel_page_title( 'welcome' ); ?>

	<div class="uncode-admin-panel">
		<?php //echo uncode_admin_panel_title(); ?>
		<?php echo uncode_admin_panel_menu( 'welcome' ); ?>

		<div class="uncode-admin-panel__content uncode-admin-panel__content--two-cols">

			<div class="uncode-admin-panel__left">
				<?php if ( defined('ENVATO_HOSTED_SITE') ) { ?>
					<h2 class="uncode-admin-panel__heading"><?php esc_html_e( 'Welcome', 'uncode' ); ?></h2>
				<?php } else { ?>
					<h2 class="uncode-admin-panel__heading"><?php esc_html_e( 'Registration', 'uncode' ); ?></h2>
				<?php } ?>

				<div class="uncode-info-box">
					<p class="uncode-admin-panel__description"><?php esc_html_e('Uncode is ready to be used with your WordPress site. Please register your product to get automatic updates.','uncode'); ?></p>

					<?php
					$uncode_envato_token                = get_option( 'uncode_registration_token', false );
					$uncode_envato_purchase_code        = get_option( 'uncode_registration_purchase_code', false );
					$uncode_valid_token                 = get_option( 'uncode_registration_valid_token', false );
					$uncode_valid_purchase_code         = get_option( 'uncode_registration_valid_purchase_code', false );
					$uncode_license_already_in_use      = $uncode_valid_token && $uncode_valid_purchase_code && ! get_option( 'uncode_registration_purchase_code_not_already_in_use' );
					$uncode_registration_error          = get_option( 'uncode_registration_error', false ) ? true : false;
					$uncode_registration_accepted_terms = get_option( 'uncode_registration_accepted_terms', false ) ? true : false;
					?>

					<?php
						if ( function_exists( 'password_hash' ) && function_exists( 'password_verify' ) ) :
					?>

						<form method="POST" id="uncode-registration-form" class="uncode-registration-form <?php echo $uncode_license_already_in_use ? 'uncode-registration-form--already-in-use' : ''; ?> <?php echo $uncode_registration_error ? 'uncode-registration-form--api-error' : ''; ?>">

							<div class="format-setting-wrap">

								<div class="format-setting-label">
									<h3 class="label"><?php esc_html_e('Envato Token', 'uncode'); ?></h3>
								</div>

								<div class="format-setting has-desc">
									<div class="description"><?php printf(esc_html__( 'Please insert your Envato token. %s.','uncode'), '<a tabindex="-1" id="uncode-envato-token-trigger" href="#">'.esc_html__('More info','uncode').'</a>'); ?></div>
									<div class="format-setting-inner">
										<input type="text" name="envato_token" id="envato-token" class="widefat option-tree-ui-input" value="<?php echo esc_attr( $uncode_envato_token ); ?>">
									</div>
								</div>

								<div class="uncode-envato-token-info" style="display: none;">

									<h4><?php esc_html_e( 'Instructions for generating an Envato Token', 'uncode' ); ?>:</h4>
									<ol>
										<li><?php printf(esc_html__( 'Login to %s with the account that was used to purchase Uncode','uncode'), '<a tabindex="-1" href="' . esc_url('//themeforest.net/') . '" target="_blank">'.esc_html__('ThemeForest','uncode').'</a>'); ?>.</li>
										<li><?php printf(esc_html__( 'Click on this link to %s','uncode'), '<a tabindex="-1" href="' . esc_url('//build.envato.com/create-token/?user:username=t&purchase:list=t&purchase:download=t&purchase:verify=t') . '" target="_blank">'.esc_html__('Create a Token','uncode').'</a>'); ?>.</li>
										<li><?php esc_html_e( 'Enter a name for your Token and make sure the following four checkboxes are checked: \'View your Envato Account username\', \'Download your purchased items\', \'List purchases you\'ve made\' and \'Verify purchases you\'ve made\'', 'uncode' ); ?>.</li>
										<li><?php esc_html_e( 'Click the \'Create Token\' button, copy the Token and paste it into the \'Envato Token\' field of the Registration page', 'uncode' ); ?>.</li>
									</ol>
									<a class="button button-primary external-button" href="<?php echo esc_url('//build.envato.com/create-token/?user:username=t&purchase:list=t&purchase:download=t&purchase:verify=t'); ?>" target="_blank"><span><?php esc_html_e('Create a Token','uncode'); ?></span></a>
								</div><!-- .uncode-envato-token-info -->
							</div>

							<div class="format-setting-wrap">
								<div class="format-setting-label">
									<h3 class="label"><?php esc_html_e( 'Envato Purchase Code', 'uncode' ); ?></h3>
								</div>

								<div class="format-setting has-desc">
									<div class="description"><?php printf(esc_html__( 'Please insert your Envato Purchase Code. %s.','uncode'), '<a tabindex="-1" id="uncode-purchase-code-trigger" href="#">'.esc_html__('More info','uncode').'</a>'); ?></div>
									<div class="format-setting-inner">
										<input type="text" name="envato_purchase_code" id="envato-purchase-code" class="widefat option-tree-ui-input" value="<?php echo esc_attr( $uncode_envato_purchase_code ); ?>">
									</div>
								</div>

								<div class="uncode-purchase-code-info" style="display: none;">

									<h4><?php printf(esc_html__( 'Where can I find my Purchase Code?','uncode'), '<a tabindex="-1" href="' . esc_url('//help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-') . '" target="_blank">'.esc_html__('video','uncode').'</a>'); ?>:</h4>
									<ol>
										<li><?php printf( esc_html__( 'Access your %s with the account that was used to purchase Uncode','uncode'), '<a tabindex="-1" href="' . esc_url('//themeforest.net/downloads') . '" target="_blank">'.esc_html__('ThemeForest downloads','uncode').'</a>'); ?>.</li>
										<li><?php esc_html_e( 'Look for Uncode in your list of purchases, click the Download button and select \'License Certificate & Purchase Code\'', 'uncode' ); ?>.</li>
										<li><?php esc_html_e( 'Copy the \'Item Purchase Code\' into the field \'Envato Purchase Code\' of the Registration page', 'uncode' ); ?>.</li>
									</ol>
									<a class="button button-primary external-button" href="<?php echo esc_url('//themeforest.net/downloads'); ?>" target="_blank"><span><?php esc_html_e('Your ThemeForest Downloads','uncode'); ?></span></a>
								</div><!-- .uncode-purchase-code-info -->
							</div>

							<?php if ( $uncode_registration_error = get_option( 'uncode_registration_error', false ) ) : ?>

								<p class="uncode-admin-registration-info uncode-ui-notice uncode-ui-notice--error"><?php echo esc_html( $uncode_registration_error ); ?></p>

							<?php elseif ( $uncode_license_already_in_use ) : ?>

								<p class="uncode-admin-registration-info uncode-ui-notice uncode-ui-notice--error">
									<?php esc_html_e( 'This product is in use on another domain.', 'uncode' ); ?><br>
									<?php printf(esc_html__('Are you using this theme for a new site? Please purchase a %s or move your license on this domain with the button below.', 'uncode' ), '<a tabindex="-1" href="' . esc_url('//themeforest.net/item/uncode-creative-multiuse-wordpress-theme/13373220?utm_source=undsgn_support&ref=undsgn&license=regular&open_purchase_for_item_id=13373220&purchasable=source') . '" target="_blank">'.esc_html__('new license','uncode').'</a>'); ?>
								</p>

							<?php endif; ?>

							<div class="format-setting-wrap">
								<div class="format-setting-inner envato-agreement">
									<label><input type="checkbox" class="alignleft" name="uncode_registration_accept_terms" id="uncode-registration-accept-terms" <?php checked( $uncode_registration_accepted_terms, true, true ); ?>>
										<small class="alignright"><?php printf( esc_html__( 'Confirm that, according to the Envato License Terms, each license entitles one person for a single project. Creating multiple unregistered installations is a copyright violation. %s.', 'uncode' ), '<a tabindex="-1" href="' . esc_url('//support.undsgn.com/hc/en-us/articles/360000718649') . '" target="_blank">'.esc_html__('More info','uncode').'</a>'); ?></small></label>
								</div>
							</div>

							<div>
								<input type="hidden" name="uncode_registration_form" value="1" />

								<?php wp_nonce_field( 'uncode-registration-form' ); ?>

								<?php if ( $uncode_valid_token && $uncode_valid_purchase_code && ! $uncode_license_already_in_use && ! $uncode_registration_error ) : ?>

									<input type="hidden" name="uncode_registration_deregister_product" value="1" />

								<?php elseif ( $uncode_license_already_in_use && ! $uncode_registration_error ) : ?>
									<input type="hidden" name="uncode_registration_force_to_this_domain" value="1" />
								<?php endif; ?>

								<?php
								$uncode_register_button_class = '';
								if ( $uncode_valid_token && $uncode_valid_purchase_code && ! $uncode_license_already_in_use && ! $uncode_registration_error ) {
									$uncode_register_button_class = 'button--deregister-product';
									$uncode_register_button_text = esc_html__( 'Deregister your product', 'uncode' );
								} else if ( $uncode_license_already_in_use && ! $uncode_registration_error ) {
									$uncode_register_button_text = esc_html__( 'Activate license on this domain', 'uncode' );
								} else {
									$uncode_register_button_text = esc_html__( 'Register your product', 'uncode' );
								}
								?>

								<button class="button button-primary <?php echo esc_attr( $uncode_register_button_class ); ?>" type="submit" id="envato_update_info" name="envato_update_info" disabled="disabled"><span><span class="uncode-ot-spinner"></span><?php echo $uncode_register_button_text; ?></span></button>
							</div>

						</form>

					<?php else : ?>

						<p class="uncode-admin-registration-info uncode-ui-notice uncode-ui-notice--error">
							<?php printf(esc_html__( 'To use Uncode and register your product, please make sure you are running at least PHP 5.6 or greater. WordPress officially recommends PHP 7.2 or greater. Please ask your host to update your PHP version. %s','uncode'), '<a href="' . esc_url('//wordpress.org/about/requirements/') . '" target="_blank">'.esc_html__('More info...','uncode').'</a>' ); ?>
						</p>

					<?php endif; ?>

				</div><!-- .uncode-info-box -->

			</div><!-- .uncode-admin-panel__left -->

			<div class="uncode-admin-panel__right">
				<h2 class="uncode-admin-panel__heading"><?php esc_html_e( 'System Status', 'uncode' ); ?></h2>

				<p class="uncode-admin-panel__description"><?php
					if ( defined('ENVATO_HOSTED_SITE') ) {
						 esc_html_e("Under System Status, you can find important information about your WordPress setup.", "uncode");
					} else {
						printf(esc_html__("Under System Status, you can find important information about your server setup. If you see red errors that indicate problems, it is likely that you're not in compliance with Uncode's %s.", "uncode"), '<a href="' . esc_url('//support.undsgn.com/hc/en-us/articles/213453949') . '" target="_blank">'.esc_html__('Server Requirements','uncode').'</a>');
					}
				?></p>

				<table class="widefat system-status-list" cellspacing="0" id="status">
					<tbody>
						<tr>
							<td data-export-label="License"><?php echo esc_html__("Theme version", "uncode"); ?>
							<?php echo '<span class="toggle-description"></span><small class="description">' . esc_html__( 'The version of Uncode installed on your site.', 'uncode' ) . '</small>'; ?></td>
							<td>
							<?php
								$theme_data = wp_get_theme();
								echo esc_attr(UNCODE_PARENT_VERSION);
							?>
							</td>
			            </tr>
		            <?php if ( is_child_theme() ) : ?>
						<tr>
							<td data-export-label="Child Theme"><?php echo esc_html__("Child Theme", "uncode"); ?>
							<?php echo '<span class="toggle-description"></span><small class="description">' . esc_html__( 'Name and version of child theme installed on your site.', 'uncode' ) . '</small>'; ?></td>
							<td>
							<?php printf( wp_kses_post( _x( '%s - %s', 'Child theme name and version', 'uncode' ) ), $theme_data->get( 'Name' ), $theme_data->get( 'Version' ) ) . '</small>'; ?>
							</td>
			            </tr>
			        <?php endif; ?>
			        <?php if ( ! defined('ENVATO_HOSTED_SITE') ) : ?>

						<tr>
							<td data-export-label="Product Registration"><?php echo esc_html__("Product Registration", "uncode"); ?>
							<?php echo '<span class="toggle-description"></span><small class="description">' . esc_html__( 'Please validate your product license as outlined in Envato\'s license terms.', 'uncode' ) . '</small>'; ?></td>
							<td>
							<?php
							if ( $uncode_valid_token && $uncode_valid_purchase_code && ! $uncode_license_already_in_use && ! $uncode_registration_error ) {
								echo '<mark class="yes">' . esc_html__( 'Theme registered.', 'uncode' ) . '</mark>';
							} else {
								echo '<mark class="error">' . esc_html__( 'Not registered.', 'uncode' ) . '</mark>';
							}
							?>
							</td>
						</tr>
				        <?php endif; ?>
						<tr>
							<td data-export-label="WP Version"><?php esc_html_e( 'WP Version', 'uncode' ); ?>
							<?php echo '<span class="toggle-description"></span><small class="description">' . esc_html__( 'The version of WordPress installed on your site.', 'uncode' ) . '</small>'; ?></td>
							<td><?php bloginfo('version'); ?></td>
						</tr>
						<tr>
							<td data-export-label="Language"><?php esc_html_e( 'Language', 'uncode' ); ?>
							<?php echo '<span class="toggle-description"></span><small class="description">' . esc_html__( 'The current language used by WordPress. Default = English.', 'uncode' ) . '</small>'; ?></td>
							<td><?php echo get_locale() ?></td>
						</tr>
						<tr>
							<td data-export-label="WP Multisite"><?php esc_html_e( 'WP Multisite', 'uncode' ); ?>
							<?php echo '<span class="toggle-description"></span><small class="description">' . esc_html__( 'Whether or not you have WordPress Multisite enabled.', 'uncode' ) . '</small>'; ?></td>
							<td><?php if ( is_multisite() ) echo '&#10004;'; else echo '&ndash;'; ?></td>
						</tr>
						<?php if ( ! class_exists( 'UncodeCore_Plugin' ) ) : ?>
							<tr>
								<td data-export-label="Uncode Core"><?php esc_html_e( 'Uncode Core', 'uncode' ); ?>
								<?php echo '<span class="toggle-description"></span><small class="description">' . esc_html__( 'Whether or not you have Uncode Core active.', 'uncode' ) . '</small>'; ?></td>
								<td><mark class="error"><?php printf( __( 'Not active, please <a href="%s">activate </a> it.', 'uncode' ), admin_url( 'admin.php?page=uncode-plugins' ) ); ?></mark></td>
							</tr>
						<?php endif; ?>

						<?php if ( current_user_can( 'switch_themes' ) ) : ?>
							<tr>
								<td data-export-label="Frontend Stylesheet"><?php esc_html_e( 'Frontend Stylesheet', 'uncode' ); ?>
								<?php echo '<span class="toggle-description"></span><small class="description">' . esc_html__( 'Uncode is generating a stylesheet when the options are saved. The file must be writtable.', 'uncode' ) . '</small>'; ?></td>
								<td><?php
									global $wp_filesystem;
									if (empty($wp_filesystem)) {
										require_once (ABSPATH . '/wp-admin/includes/file.php');
									}
									$mod_file = (defined('FS_CHMOD_FILE')) ? FS_CHMOD_FILE : false;
									$front_css = get_template_directory() . '/library/css/';
									$front_css_file = $front_css . 'style-custom.css';
									$creds = request_filesystem_credentials($front_css, '', false, false, array());
									$can_write_front = true;
									if (!!$creds) {
										/* initialize the API */
										if ( ! WP_Filesystem($creds) ) {
											/* any problems and we exit */
											$can_write_front = false;
										}
									}
									$filename = trailingslashit($front_css).'test.txt';
									if ( ! $wp_filesystem->put_contents( $filename, 'Test file contents', $mod_file) ) {
										$can_write_front = false;
									} else {
										$wp_filesystem->delete( $filename );
									}

									$file_is_writable = wp_is_writable($front_css_file);

									$front_css = '..' . substr($front_css, strpos($front_css,"/wp-content"));
									$front_css_file = $front_css . 'style-custom.css';

									if ($can_write_front) {
										if ( ! $file_is_writable )
											printf( '<div class="uncode-note">' . wp_kses(__( 'WordPress doesn\'t have direct access to this file <code>%s</code>. This is most likely due to a conflict with server file permissions. It is also possible that WordPress\' file access is not configured correctly. The custom CSS will be output inline.', 'uncode' ), array( 'code' => '' )) . '</div>', $front_css_file  );
										else
											echo '<mark class="yes">' . '<code class="yes">' . $front_css .'</code></mark> ';
									} else {
										printf( '<div class="uncode-note">' . wp_kses(__( 'WordPress doesn\'t have direct access to this folder <code>%s</code>. This is most likely due to a conflict with server file permissions. It is also possible that WordPress\' file access is not configured correctly. The custom CSS will be output inline.', 'uncode' ), array( 'code' => '' )) . '</div>', $front_css  );
									}
								?></td>
							</tr>
							<tr>
								<td data-export-label="Backend Stylesheet"><?php esc_html_e( 'Backend Stylesheet', 'uncode' ); ?>
								<?php echo '<span class="toggle-description"></span><small class="description">' . esc_attr__( 'Uncode is generating a stylesheet when the options are saved. The file must be writtable.', 'uncode' ) . '</small>'; ?></td>
								<td><?php
									$mod_file = (defined('FS_CHMOD_FILE')) ? FS_CHMOD_FILE : false;
									$back_css = get_template_directory() . '/core/assets/css/';
									$back_css_file = $back_css . 'admin-custom.css';
									$creds = request_filesystem_credentials($back_css, '', false, false, array());
									$can_write_back = true;
									if (!!$creds) {
										/* initialize the API */
										if ( ! WP_Filesystem($creds) ) {
											/* any problems and we exit */
											$can_write_back = false;
										}
									}
									$filename = trailingslashit($back_css).'test.txt';
									if ( ! $wp_filesystem->put_contents( $filename, 'Test file contents', $mod_file) ) {
										$can_write_back = false;
									} else {
										$wp_filesystem->delete( $filename );
									}

									$back_is_writable = wp_is_writable($back_css_file);

									$back_css = '..' . substr($back_css, strpos($back_css,"/wp-content"));
									$back_css_file = $back_css . 'admin-custom.css';

									if ($can_write_back) {
										if ( ! $back_is_writable )
											printf( '<div class="uncode-note">' . wp_kses(__( 'WordPress doesn\'t have direct access to this file <code>%s</code>. This is most likely due to a conflict with server file permissions. It is also possible that WordPress\' file access is not configured correctly. The custom CSS will be output inline.', 'uncode' ), array( 'code' => '' )) . '</div>', $back_css_file  );
										else
											echo '<mark class="yes">' . '<code class="yes">' . $back_css .'</code></mark> ';
									} else {
										printf( '<div class="uncode-note">' . wp_kses(__( 'WordPress doesn\'t have direct access to this folder <code>%s</code>. This is most likely due to a conflict with server file permissions. It is also possible that WordPress\' file access is not configured correctly. The custom CSS will be output inline.', 'uncode' ), array( 'code' => '' )) . '</div>', $back_css  );
									}
								?></td>
							</tr>
							<?php if ( ! defined('ENVATO_HOSTED_SITE') ) : ?>
							<tr>
								<td data-export-label="WP Memory Limit"><?php esc_html_e( 'WP Memory Limit', 'uncode' ); ?>
								<?php echo '<span class="toggle-description"></span><small class="description">' . esc_attr__( 'Memory Limits not satisfied may produce possible errors on the frontend of the site as blank pages.', 'uncode' ) . '</small>'; ?></td>
								<td><?php
									$memory = uncode_let_to_num( WP_MEMORY_LIMIT );

									if ( $memory < 100663296 ) {
										echo '<mark class="error">' . sprintf(esc_html__('%s - We recommend setting memory to at least 96 MB. %s.','uncode'), size_format( $memory ), '<a href="' . esc_url('//support.undsgn.com/hc/en-us/articles/213459889') . '" target="_blank">'.esc_html__('More info','uncode').'</a>') . '</mark>';
									} else {
										echo '<mark class="yes">' . size_format( $memory ) . '</mark>';
									}
								?></td>
							</tr>
							<?php do_action( 'uncode_server_memory_limit' ); ?>
							<tr>
								<td data-export-label="PHP Max Input Vars"><?php esc_html_e( 'PHP Max Input Vars', 'uncode' ); ?>
								<?php echo '<span class="toggle-description"></span><small class="description">' . esc_attr__( 'Max Input Vars not satisfied may result in loss of Theme Options.', 'uncode' ) . '</small>'; ?></td>
								<td><?php
									$max_input = ini_get('max_input_vars');
									if ( $max_input < 3000 ) {
										echo '<mark class="error">' . sprintf( wp_kses(__( '%s - We recommend setting PHP max_input_vars to at least 3000. See: <a href="%s" target="_blank">Increasing the PHP max vars limit</a>', 'uncode' ), array( 'a' => array( 'href' => array(),'target' => array() ) ) ), $max_input, '//support.undsgn.com/hc/en-us/articles/213459869' ) . '</mark>';
									} else {
										echo '<mark class="yes">' . $max_input . '</mark>';
									}
								?></td>
							</tr>
							<tr>
								<td data-export-label="PHP Max Input Vars Allowed"><?php esc_html_e( 'PHP Max Input Vars (allowed)', 'uncode' ); ?>
								<?php echo '<span class="toggle-description"></span><small class="description">' . esc_attr__( 'The effective maximum number of variables your server can use for a single function to avoid overloads. If this value is lower than max_input_vars your server is applying restrictions on the actual number of vars that can be used.', 'uncode' ) . '<br>' . esc_attr__( 'If you modified the server settings refresh the option to test.', 'uncode' ) . '</small>'; ?></td>
								<td class="get-max-input-vars">
									<?php $uncode_test_max_input_vars = intval(get_option('uncode_test_max_input_vars'));
										if ( $uncode_test_max_input_vars != '' ) : ?>
										<span class="calculating" style="display: none"><?php esc_html_e( 'Calculating…', 'uncode' ); ?></span>
										<mark class="yes" <?php if ( $uncode_test_max_input_vars < 3000 ) echo 'style="display: none;"' ?>><?php echo $uncode_test_max_input_vars; ?></mark>
										<mark class="error get_data" <?php if ( $uncode_test_max_input_vars >= 3000 ) echo 'style="display: none;"'; ?>><?php echo $uncode_test_max_input_vars; printf(esc_html__(' - We recommend setting PHP max_input_vars to at least 3000. %s.','uncode'), '<a href="' . esc_url('//support.undsgn.com/hc/en-us/articles/213459869') . '" target="_blank">'.esc_html__('More info','uncode').'</a>'); ?></mark>
										<a href="#" id="max_vars_checker"><i class="fa fa-refresh"></i></a>
									<?php else : ?>
										<span class="calculating"><?php esc_html_e( 'Calculating…', 'uncode' ); ?></span>
										<mark class="yes" style="display: none;"></mark>
										<mark class="error get_data" style="display: none;">%d%<?php printf(esc_html__(' - We recommend setting PHP max_input_vars to at least 3000. %s.','uncode'), '<a href="' . esc_url('//support.undsgn.com/hc/en-us/articles/213459869') . '" target="_blank">'.esc_html__('More info','uncode').'</a>'); ?></mark>
										<mark class="error no_data" style="display: none;"><?php esc_html_e('No available data','uncode'); ?></mark>
										<a href="#" id="max_vars_checker"><i class="fa fa-refresh"></i></a>
									<?php endif; ?>
								</td>
							</tr>
							<?php endif; ?>
							<tr>
								<td data-export-label="WP Debug Mode"><?php esc_html_e( 'WP Debug Mode', 'uncode' ); ?>
								<?php echo '<span class="toggle-description"></span><small class="description">' . esc_attr__( 'Displays whether or not WordPress is in Debug Mode.', 'uncode' ) . '</small>'; ?></td>
								<td><?php if ( defined('WP_DEBUG') && WP_DEBUG ) echo '<mark class="yes">' . '&#10004;' . '</mark>'; else echo '&ndash;'; ?></td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
				<?php do_action('uncode_welcome'); ?>
			</div><!-- .uncode-admin-panel__right -->
		</div><!-- .uncode-admin-panel__content -->
	</div><!-- .uncode-admin-panel -->
</div><!-- .uncode-wrap -->

<script type="text/javascript">

	jQuery( document ).ready( function ( $ ) {

		if ($('#uncode-registration-accept-terms').prop('checked')) {
			$('#envato_update_info').prop('disabled', false);
		}

		$('#uncode-registration-accept-terms').on('click', function() {
			if ($('#uncode-registration-accept-terms').prop('checked')) {
				$('#envato_update_info').prop('disabled', false);
			} else {
				$('#envato_update_info').prop('disabled', true);
			}
		});

		$( '.help_tip' ).tipTip({
			attribute: 'data-tip'
		});

		$( 'a.help_tip' ).click( function() {
			return false;
		});

		var max_vars_checker = function(){
			var $wrap = $('.get-max-input-vars'),
				$calculating = $('.calculating', $wrap),
				$errors = $('.error', $wrap),
				$yes = $('.yes', $wrap),
				$checker = $('#max_vars_checker');

			$checker.on('click', function(e){
				e.preventDefault();

				$yes.add($errors).add($checker).fadeOut(200);
				setTimeout(function(){
					$calculating.fadeIn(200);
					uncode_test_max_input_vars(10000);
				}, 200);

			});

		};
		max_vars_checker();

		<?php if ( current_user_can( 'switch_themes' ) ) { ?>

		var uncode_test_max_input_vars = function($vars){
			var param = [],
				var_string,
				intData;
			for (i = 0; i < $vars; i++) {
				param[i] = 'var_'+i;
			}

			$.ajax({
				url: ajaxurl,
				data: {
					action: 'uncode_test_vars',
					content: param,
				},
				type: 'post',
				error: function(){
					$('.get-max-input-vars .calculating').hide();
					$('.get-max-input-vars .error.no_data').fadeIn();
				},
				success: function(data){
					intData = parseInt(data);
					if ( intData < ($vars-1) ) {
						if ( intData < 2990 ) {
							var_string = $('.get-max-input-vars .error.get_data');
							var_string.html(var_string.html().replace("%d%", intData));
						} else {
							var_string = $('.get-max-input-vars .yes');
							var_string.html(intData);
						}
						$('.get-max-input-vars .calculating').hide();
						var_string.add('#max_vars_checker').fadeIn();

					} else {
						uncode_test_max_input_vars($vars+10000);
					}
					$.ajax({
						url: ajaxurl,
						data: {
							action: 'uncode_update_max_input_vars',
							content: intData,
						},
						type: 'post'
					});
				}
			});

		};

		<?php } ?>

		jQuery( function ( $ ) {
			'use strict';
			$('#uncode-envato-token-trigger').on('click', function(event) {
				var envato_token_info = $('.uncode-envato-token-info').html();
				$("<div />").html(envato_token_info).dialog({
					autoOpen: true,
					modal: true,
					dialogClass: 'uncode-modal',
					title: "<?php echo esc_html__('Instructions for generating an Envato Token', 'uncode'); ?>",
					maxHeight: 800,
					//minHeight: 500,
					//minWidth: 500,
					width: 600,
					position: { my: "center", at: "center", of: window },
					open: function( event, ui ) {
						$('body').addClass('overflow_hidden');
					},
					close: function( event, ui ) {
						$('body').removeClass('overflow_hidden');
					}
				});
			});

			$('#uncode-purchase-code-trigger').on('click', function(event) {
				var envato_token_info = $('.uncode-purchase-code-info').html();
				$("<div />").html(envato_token_info).dialog({
					autoOpen: true,
					modal: true,
					dialogClass: 'uncode-modal',
					title: "<?php echo esc_html__('Where can I find my Purchase Code?', 'uncode'); ?>",
					maxHeight: 800,
					//minHeight: 500,
					//minWidth: 500,
					width: 600,
					position: { my: "center", at: "center", of: window },
					open: function( event, ui ) {
						$('body').addClass('overflow_hidden');
					},
					close: function( event, ui ) {
						$('body').removeClass('overflow_hidden');
					}
				});
			});
		});
		<?php if ( current_user_can( 'switch_themes' ) && $uncode_test_max_input_vars == '' ) { ?>
		uncode_test_max_input_vars(10000);
		<?php } ?>

	});

</script>
