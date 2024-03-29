<?php
/**
 * Cart Abandonment
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

define( 'CARTFLOWS_EMAIL_TEMPLATE_DIR', CARTFLOWS_CA_DIR . 'modules/cart-abandonment/' );
define( 'CARTFLOWS_EMAIL_TEMPLATE_URL', CARTFLOWS_CA_URL . 'modules/cart-abandonment/' );

/**
 * Class for analytics tracking.
 */
class Cartflows_Ca_Email_Templates {



	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private $wpdb;

	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	public $email_history_table;

	/**
	 * Table name for email templates
	 *
	 * @var string
	 */
	public $cart_abandonment_template_table_name;

	/**
	 * Table name for email templates meta table
	 *
	 * @var string
	 */
	public $email_templates_meta_table;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Constructor function that initializes required actions and hooks
	 */
	public function __construct() {
		$this->define_template_constants();
		global $wpdb;
		$this->cart_abandonment_template_table_name = $wpdb->prefix . CARTFLOWS_CA_EMAIL_TEMPLATE_TABLE;
		$this->email_templates_meta_table           = $wpdb->prefix . CARTFLOWS_CA_EMAIL_TEMPLATE_META_TABLE;
		$this->email_history_table                  = $wpdb->prefix . CARTFLOWS_CA_EMAIL_HISTORY_TABLE;
		$this->wpdb                                 = $wpdb;

		add_action( 'admin_enqueue_scripts', __class__ . '::load_email_templates_script', 15 );
	}


	/**
	 * Add email template JS script.
	 */
	public static function load_email_templates_script() {

		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );

		if ( ! ( WCF_CA_PAGE_NAME === $page ) ) {
			return;
		}

		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'jquery-ui-style' );

		wp_enqueue_script(
			'cartflows-ca-email-tmpl-settings',
			CARTFLOWS_CA_URL . 'admin/assets/js/admin-email-templates.js',
			array( 'jquery' ),
			CARTFLOWS_CA_VER
		);

		$vars = array(
			'settings_url' => add_query_arg(
				array(
					'page'   => WCF_CA_PAGE_NAME,
					'action' => WCF_ACTION_SETTINGS,
				),
				admin_url( '/admin.php' )
			),
		);

		wp_localize_script( 'cartflows-ca-email-tmpl-settings', 'CAEmailTemplate', $vars );

		$current_user = wp_get_current_user();
		$vars         = array(
			'email'               => $current_user->user_email,
			'name'                => $current_user->user_firstname,
			'surname'             => $current_user->user_lastname,
			'phone'               => get_user_meta( $current_user->ID, 'billing_phone', true ),
			'billing_company'     => get_user_meta( $current_user->ID, 'billing_company', true ),
			'billing_address_1'   => get_user_meta( $current_user->ID, 'billing_address_1', true ),
			'billing_address_2'   => get_user_meta( $current_user->ID, 'billing_address_2', true ),
			'billing_state'       => get_user_meta( $current_user->ID, 'billing_state', true ),
			'billing_postcode'    => get_user_meta( $current_user->ID, 'billing_postcode', true ),
			'shipping_first_name' => $current_user->user_firstname,
			'shipping_last_name'  => $current_user->user_lastname,
			'shipping_company'    => get_user_meta( $current_user->ID, 'shipping_company', true ),
			'shipping_address_1'  => get_user_meta( $current_user->ID, 'shipping_address_1', true ),
			'shipping_address_2'  => get_user_meta( $current_user->ID, 'shipping_address_2', true ),
			'shipping_city'       => get_user_meta( $current_user->ID, 'shipping_city', true ),
			'shipping_state'      => get_user_meta( $current_user->ID, 'shipping_state', true ),
			'shipping_postcode'   => get_user_meta( $current_user->ID, 'shipping_postcode', true ),
			'woo_currency_symbol' => get_woocommerce_currency_symbol(),
		);
		wp_localize_script( 'cartflows-ca-email-tmpl-settings', 'CartFlowsCADetails', $vars );

	}

	/**
	 *  Initialise all the constants
	 */
	public function define_template_constants() {
		define( 'WCF_CA_PAGE_NAME', 'woo-cart-abandonment-recovery' );

		define( 'WCF_CA_GENERAL_SETTINGS_SECTION', 'cartflows_cart_abandonment_settings_section' );
		define( 'WCF_CA_EMAIL_SETTINGS_SECTION', 'cartflows_email_template_settings_section' );
		define( 'WCF_CA_COUPON_CODE_SECTION', 'cartflows_coupon_code_settings_section' );
		define( 'WCF_CA_ZAPIER_SETTINGS_SECTION', 'cartflows_zapier_settings_section' );
		define( 'WCF_CA_GDPR_SETTINGS_SECTION', 'cartflows_gdpr_settings_section' );

		define( 'WCF_CA_SETTINGS_OPTION_GROUP', 'cartflows-cart-abandonment-settings' );
		define( 'WCF_CA_EMAIL_SETTINGS_OPTION_GROUP', 'cartflows-cart-abandonment-email-settings' );

		define( 'WCF_ACTION_EMAIL_TEMPLATES', 'email_tmpl' );

		define( 'WCF_SUB_ACTION_ADD_EMAIL_TEMPLATES', 'add_email_tmpl' );
		define( 'WCF_SUB_ACTION_EDIT_EMAIL_TEMPLATES', 'edit_email_tmpl' );
		define( 'WCF_SUB_ACTION_DELETE_EMAIL_TEMPLATES', 'delete_email_tmpl' );
		define( 'WCF_SUB_ACTION_CLONE_EMAIL_TEMPLATES', 'clone_email_tmpl' );
		define( 'WCF_SUB_ACTION_DELETE_BULK_EMAIL_TEMPLATES', 'delete_bulk_email_tmpl' );
		define( 'WCF_SUB_ACTION_SAVE_EMAIL_TEMPLATES', 'save_email_template' );
		define( 'WCF_SUB_ACTION_RESTORE_EMAIL_TEMPLATES', 'restore_default_email_tmpl' );

		define( 'WCF_SUB_ACTION_CART_ABANDONMENT_SETTINGS', 'cart_abandonment_settings' );
		define( 'WCF_SUB_ACTION_EMAIL_SETTINGS', 'email_settings' );
		define( 'WCF_SUB_ACTION_COUPON_CODE_SETTINGS', 'coupon_code_settings' );
		define( 'WCF_SUB_ACTION_ZAPIER_SETTINGS', 'zapier_settings' );

		define( 'WCF_EMAIL_TEMPLATES_NONCE', 'email_template_nonce' );

	}

	/**
	 *  Show success messages for email templates.
	 */
	function show_messages() {

		$wcf_ca_template_created  = filter_input( INPUT_GET, 'wcf_ca_template_created', FILTER_SANITIZE_STRING );
		$wcf_ca_template_cloned   = filter_input( INPUT_GET, 'wcf_ca_template_cloned', FILTER_SANITIZE_STRING );
		$wcf_ca_template_deleted  = filter_input( INPUT_GET, 'wcf_ca_template_deleted', FILTER_SANITIZE_STRING );
		$wcf_ca_template_updated  = filter_input( INPUT_GET, 'wcf_ca_template_updated', FILTER_SANITIZE_STRING );
		$wcf_ca_template_restored = filter_input( INPUT_GET, 'wcf_ca_template_restored', FILTER_SANITIZE_STRING );

		?>
		<?php if ( 'YES' === $wcf_ca_template_created ) { ?>
		<div id="message" class="notice notice-success is-dismissible">
			<p>
				<strong>
					<?php _e( 'The Email Template has been successfully added.', 'cartflows-ca' ); ?>
				</strong>
			</p>
		</div>
	<?php } ?>

		<?php if ( 'YES' === $wcf_ca_template_cloned ) { ?>
		<div id="message" class="notice notice-success is-dismissible">
			<p>
				<strong>
					<?php _e( 'The Email Template has been cloned successfully.', 'cartflows-ca' ); ?>
				</strong>
			</p>
		</div>
	<?php } ?>

		<?php if ( 'YES' === $wcf_ca_template_deleted ) { ?>
		<div id="message" class="notice notice-success is-dismissible">
			<p>
				<strong>
					<?php _e( 'The Email Template has been successfully deleted.', 'cartflows-ca' ); ?>
				</strong>
			</p>
		</div>
	<?php } ?>
		<?php if ( 'YES' === $wcf_ca_template_updated ) { ?>
		<div id="message" class="notice notice-success is-dismissible">
			<p>
				<strong>
					<?php _e( 'The Email Template has been successfully updated.', 'cartflows-ca' ); ?>
				</strong>
			</p>
		</div>
	<?php } ?>

		<?php if ( 'YES' === $wcf_ca_template_restored ) { ?>
			<div id="message" class="notice notice-success is-dismissible">
				<p>
					<strong>
						<?php _e( 'Default Email Templates has been restored successfully.', 'cartflows-ca' ); ?>
					</strong>
				</p>
			</div>
		<?php } ?>
		<?php

	}

	/**
	 *  Delete bulk email templates.
	 */
	function delete_bulk_templates() {
		$wcf_template_list = new Cartflows_Ca_Email_Templates_Table();
		$wcf_template_list->process_bulk_action();
		$param        = array(
			'page'                    => WCF_CA_PAGE_NAME,
			'action'                  => WCF_ACTION_EMAIL_TEMPLATES,
			'wcf_ca_template_deleted' => 'YES',
		);
		$redirect_url = add_query_arg( $param, admin_url( '/admin.php' ) );
		wp_safe_redirect( $redirect_url );
	}


	/**
	 *  Delete email templates.
	 */
	function delete_single_template() {

		$id      = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
		$wpnonce = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING );

		if ( $id && $wpnonce && wp_verify_nonce( $wpnonce, WCF_EMAIL_TEMPLATES_NONCE ) ) {

			$this->wpdb->delete(
				$this->cart_abandonment_template_table_name,
				array( 'id' => $id ),
				'%d'
			);
			$param        = array(
				'page'                    => WCF_CA_PAGE_NAME,
				'action'                  => WCF_ACTION_EMAIL_TEMPLATES,
				'wcf_ca_template_deleted' => 'YES',
			);
			$redirect_url = add_query_arg( $param, admin_url( '/admin.php' ) );
			wp_safe_redirect( $redirect_url );

		}
	}

	/**
	 *  Delete email templates.
	 */
	function clone_email_template() {

		$id      = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
		$wpnonce = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING );

		if ( $id && $wpnonce && wp_verify_nonce( $wpnonce, WCF_EMAIL_TEMPLATES_NONCE ) ) {

			$email_template = $this->get_template_by_id( $id );

			$this->wpdb->insert(
				$this->cart_abandonment_template_table_name,
				array(
					'template_name'  => sanitize_text_field( $email_template->template_name ),
					'email_subject'  => sanitize_text_field( $email_template->email_subject ),
					'email_body'     => $email_template->email_body,
					'frequency'      => intval( sanitize_text_field( $email_template->frequency ) ),
					'frequency_unit' => sanitize_text_field( $email_template->frequency_unit ),

				),
				array( '%s', '%s', '%s', '%d', '%s' )
			);

			$email_template_id = $this->wpdb->insert_id;
			$meta_data         = array(
				'override_global_coupon' => false,
				'discount_type'          => 'percent',
				'coupon_amount'          => 10,
				'coupon_expiry_date'     => '',
				'coupon_expiry_unit'     => 'hours',
			);

			foreach ( $meta_data as $mera_key => $meta_value ) {
				$this->add_email_template_meta( $email_template_id, $mera_key, $meta_value );
			}

			$param        = array(
				'page'                   => WCF_CA_PAGE_NAME,
				'action'                 => WCF_ACTION_EMAIL_TEMPLATES,
				'wcf_ca_template_cloned' => 'YES',
			);
			$redirect_url = add_query_arg( $param, admin_url( '/admin.php' ) );
			wp_safe_redirect( $redirect_url );

		}
	}

	/**
	 *  Get email template by id.
	 *
	 * @param int $email_tmpl_id template id.
	 */
	function get_email_template_by_id( $email_tmpl_id ) {

		$query = 'SELECT  *  FROM ' . $this->cart_abandonment_template_table_name . ' WHERE id = %d ';
		return $this->wpdb->get_row($this->wpdb->prepare($query, $email_tmpl_id)); // phpcs:ignore

	}

	/**
	 *  Render email template add/edit form.
	 *
	 * @param string $sub_action sub_action.
	 */
	function render_email_template_form( $sub_action = WCF_SUB_ACTION_ADD_EMAIL_TEMPLATES ) {

		$id = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );

		if ( $id ) {
			$results = $this->get_email_template_by_id( $id );
		}

		?>

		<div id="content">

			<?php
			$param             = array(
				'page'       => WCF_CA_PAGE_NAME,
				'action'     => WCF_ACTION_EMAIL_TEMPLATES,
				'sub_action' => WCF_SUB_ACTION_SAVE_EMAIL_TEMPLATES,
			);
			$save_template_url = esc_url( add_query_arg( $param, admin_url( '/admin.php' ) ) );
			?>

			<form method="post" action="<?php echo $save_template_url; ?>" id="wcf_settings">
				<input type="hidden" name="sub_action" value="<?php echo $sub_action; ?>"/>
				<?php
				$id_by = '';
				if ( isset( $id ) ) {
					$id_by = $id;
				}
				?>
				<input type="hidden" name="id" value="<?php echo $id_by; ?>"/>
				<?php

				$button_sub_action = 'save';
				$display_message   = 'Add New Email Template:';

				if ( WCF_SUB_ACTION_EDIT_EMAIL_TEMPLATES === $sub_action ) {
					$button_sub_action = 'update';
					$display_message   = 'Edit Email Template:';
				}
				print'<input type="hidden" name="wcf_settings_frm" value="' . $button_sub_action . '">';
				?>
				<div id="poststuff">
					<div> <!-- <div class="postbox" > -->
                        <h3><?php _e($display_message, 'cartflows-ca'); // phpcs:ignore ?></h3>
						<hr/>
						<div>
							<table class="form-table" id="addedit_template">
								<tr>
									<th>
										<label for="wcf_email_subject"><b><?php _e( 'Activate Template now?', 'cartflows-ca' ); ?></b></label>
									</th>
									<td>
										<?php
										$is_activated  = '';
										$active_status = 0;
										if ( WCF_SUB_ACTION_EDIT_EMAIL_TEMPLATES === $sub_action && $results && isset( $results->is_activated ) ) {
											$active_status = stripslashes( $results->is_activated );
											$is_activated  = $active_status ? 'on' : 'off';

										}
										print'<button type="button" class="wcf-ca-switch wcf-toggle-template-status" wcf-template-id="1" wcf-ca-template-switch="' . $is_activated . '"> ' . $is_activated . ' </button>';
										print'<input type="hidden" name="wcf_activate_email_template" id="wcf_activate_email_template" value="' . $active_status . '" />';
										?>

									</td>
								</tr>

								<tr>
									<th>
										<label for="wcf_template_name"><b><?php _e( 'Template Name:', 'cartflows-ca' ); ?></b></label>
									</th>
									<td>
										<?php
										$template_name = '';
										if ( WCF_SUB_ACTION_EDIT_EMAIL_TEMPLATES === $sub_action && $results && isset( $results->template_name ) ) {
											$template_name = $results->template_name;
										}
										print'<input type="text" name="wcf_template_name" id="wcf_template_name" class="wcf-ca-trigger-input" value="' . $template_name . '">';
										?>
									</td>
								</tr>

								<tr>
									<th>
										<label for="wcf_email_subject"><b><?php _e( 'Email Subject:', 'cartflows-ca' ); ?></b></label>
									</th>
									<td>
										<?php
										$subject_edit = '';
										if ( WCF_SUB_ACTION_EDIT_EMAIL_TEMPLATES === $sub_action && $results && isset( $results->email_subject ) ) {
											$subject_edit = stripslashes( $results->email_subject );
										}
										print'<input type="text" name="wcf_email_subject" id="wcf_email_subject" class="wcf-ca-trigger-input" value="' . $subject_edit . '">';
										?>
									</td>
								</tr>

								<tr>
									<th>
										<label for="wcf_email_body"><b><?php _e( 'Email Body:', 'cartflows-ca' ); ?></b></label>
									</th>
									<td>
										<?php
										$initial_data = '';
										if ( WCF_SUB_ACTION_EDIT_EMAIL_TEMPLATES === $sub_action && $results && isset( $results->email_body ) ) {
											$initial_data = stripslashes( $results->email_body );
										}

										wp_editor(
											$initial_data,
											'wcf_email_body',
											array(
												'media_buttons' => true,
												'textarea_rows' => 15,
												'tabindex' => 4,
												'tinymce'  => array(
													'theme_advanced_buttons1' => 'bold,italic,underline,|,bullist,numlist,blockquote,|,link,unlink,|,spellchecker,fullscreen,|,formatselect,styleselect',
												),
											)
										);

										?>
										<?php echo stripslashes( get_option( 'wcf_email_body' ) ); ?>
									</td>
								</tr>

								<tr>
									<th>
										<label for="wcf_override_global_coupon"><b><?php _e( 'Create Coupon', 'cartflows-ca' ); ?></b></label>
									</th>
									<td>
										<?php

										$wcf_override_global_coupon = '';
										if ( WCF_SUB_ACTION_EDIT_EMAIL_TEMPLATES === $sub_action && $results ) {
											$wcf_override_global_coupon = $this->get_email_template_meta_by_key( $results->id, 'override_global_coupon' );
											if ( isset( $wcf_override_global_coupon->meta_value ) ) {
												$wcf_override_global_coupon = $wcf_override_global_coupon->meta_value ? 'checked' : '';
											}
										}

										print'<input ' . $wcf_override_global_coupon . ' id="wcf_override_global_coupon" name="wcf_override_global_coupon" type="checkbox" value="" /><label for="wcf_override_global_coupon"> Allows you to send new coupon only for this template. </label>';
										?>
									</td>
								</tr>

								<tr>
									<th>
										<label for="wcf_email_discount_type"><b><?php _e( 'Discount Type', 'cartflows-ca' ); ?></b></label>
									</th>
									<td>
										<?php

										$wcf_email_discount_type = 'percent';
										if ( WCF_SUB_ACTION_EDIT_EMAIL_TEMPLATES === $sub_action && $results ) {
											$wcf_email_discount_type = $this->get_email_template_meta_by_key( $results->id, 'discount_type' );
											if ( isset( $wcf_email_discount_type->meta_value ) ) {
												$wcf_email_discount_type = $wcf_email_discount_type->meta_value;
											}
										}

										$dropdown_options = array(
											'percent'    => 'Percentage discount',
											'fixed_cart' => 'Fixed cart discount',
										);

										echo '<select id="wcf_email_discount_type" name="wcf_email_discount_type">';
										foreach ( $dropdown_options as $key => $value ) {
											$is_selected = $key === $wcf_email_discount_type ? 'selected' : '';
											echo '<option ' . $is_selected . ' value=' . $key . '>' . $value . '</option>';

										}
										echo '</select>';

										?>
									</td>
								</tr>

								<tr>
									<th>
										<label for="wcf_email_discount_amount"><b><?php _e( 'Coupon Amount', 'cartflows-ca' ); ?></b></label>
									</th>
									<td>
										<?php
										$wcf_email_discount_amount = 10;
										if ( WCF_SUB_ACTION_EDIT_EMAIL_TEMPLATES === $sub_action && $results ) {
											$wcf_email_discount_amount = $this->get_email_template_meta_by_key( $results->id, 'coupon_amount' );
											if ( isset( $wcf_email_discount_amount->meta_value ) ) {
												$wcf_email_discount_amount = $wcf_email_discount_amount->meta_value;
											}
										}
										print'<input class="wcf-ca-trigger-input wcf-ca-email-inputs" type="number" id="wcf_email_discount_amount" name="wcf_email_discount_amount" value="' . $wcf_email_discount_amount . '">';
										?>
									</td>
								</tr>

								<tr>
									<th>
										<label for="wcf_email_coupon_expiry_date"><b><?php _e( 'Coupon expiry date', 'cartflows-ca' ); ?></b></label>
									</th>
									<td>
										<?php
											$wcf_email_coupon_expiry_date = 0;
										$coupon_expiry_unit               = 'hours';

										if ( WCF_SUB_ACTION_EDIT_EMAIL_TEMPLATES === $sub_action && $results ) {
											$wcf_email_coupon_expiry_date = $this->get_email_template_meta_by_key( $results->id, 'coupon_expiry_date' );
											$wcf_email_coupon_expiry_unit = $this->get_email_template_meta_by_key( $results->id, 'coupon_expiry_unit' );

											if ( isset( $wcf_email_coupon_expiry_date->meta_value ) ) {
												$wcf_email_coupon_expiry_date = $wcf_email_coupon_expiry_date->meta_value;
											}
											if ( isset( $wcf_email_coupon_expiry_unit->meta_value ) ) {
												$coupon_expiry_unit = $wcf_email_coupon_expiry_unit->meta_value;
											}
										}
										print'<input type="number" class="wcf-ca-trigger-input wcf-ca-coupon-inputs" id="wcf_email_coupon_expiry_date" name="wcf_email_coupon_expiry_date" value="' . intval( $wcf_email_coupon_expiry_date ) . '" autocomplete="off" />';
										$items = array(
											'hours' => 'Hour(s)',
											'days'  => 'Day(s)',
										);
										echo "<select id='wcf_coupon_expiry_unit' name='wcf_coupon_expiry_unit'>";
										foreach ( $items as $key => $item ) {
											$selected = ( $coupon_expiry_unit === $key ) ? 'selected="selected"' : '';
											echo "<option value='$key' $selected>$item</option>";
										}
										echo '</select>';

										echo " <span class='description'> Enter zero (0) to restrict coupon from expiring </span>"
										?>
									</td>
								</tr>

								<tr>
									<th>
										<label for="wcf_email_subject"><b><?php _e( 'Send This Email', 'cartflows-ca' ); ?></b></label>
									</th>
									<td>
										<?php
										$frequency_edit = '';
										if ( WCF_SUB_ACTION_EDIT_EMAIL_TEMPLATES === $sub_action && $results && isset( $results->frequency ) ) {
											$frequency_edit = $results->frequency;
										}
										print'<input style="width:15%" type="number" name="wcf_email_frequency" id="wcf_email_frequency" class="wcf-ca-trigger-input" value="' . $frequency_edit . '">';
										?>

										<select name="wcf_email_frequency_unit" id="wcf_email_frequency_unit">
											<?php
											$frequency_unit = '';
											if ( WCF_SUB_ACTION_EDIT_EMAIL_TEMPLATES === $sub_action && $results && isset( $results->frequency_unit ) ) {
												$frequency_unit = $results->frequency_unit;
											}
											$days_or_hours = array(
												'MINUTE' => 'Minute(s)',
												'HOUR'   => 'Hour(s)',
												'DAY'    => 'Day(s)',
											);
											foreach ( $days_or_hours as $k => $v ) {
												printf(
													"<option %s value='%s'>%s</option>\n",
													selected( $k, $frequency_unit, false ),
													esc_attr( $k ),
													$v
												);
											}
											?>
										</select>
										<span class="description">
		<?php _e( 'after cart is abandoned.', 'cartflows-ca' ); ?>
										</span>


									</td>
								</tr>

								<tr>
									<?php $current_user = wp_get_current_user(); ?>
									<th>
										<label for="wcf_email_preview"><b><?php _e( 'Send Test Email To:', 'cartflows-ca' ); ?></b></label>
									</th>
									<td>
										<input class="wcf-ca-trigger-input" type="text" id="wcf_send_test_email" name="send_test_email" value="<?php echo $current_user->user_email; ?>" class="wcf-ca-trigger-input">
										<input class="button" type="button" value="Send a test email" id="wcf_preview_email"/> <br/>

										<label id="mail_response_msg"> </label>
									</td>
								</tr>

							</table>
						</div>
					</div>
				</div>
				<?php wp_nonce_field( WCF_EMAIL_TEMPLATES_NONCE, '_wpnonce' ); ?>
				<p class="submit">
					<?php
					$button_value = 'Save Changes';
					if ( WCF_SUB_ACTION_EDIT_EMAIL_TEMPLATES === $sub_action ) {
						$button_value = 'Update Changes';
					}
					?>
					<input type="submit" name="Submit" class="button-primary" value="<?php echo $button_value; ?>"/>
				</p>
			</form>
		</div>
		<?php

	}


	/**
	 * Sanitize email post data.
	 *
	 * @return array
	 */
	function sanitize_email_post_data() {

		$input_post_values = array(
			'wcf_email_subject'            => array(
				'default'  => '',
				'sanitize' => FILTER_SANITIZE_STRING,
			),
			'wcf_email_body'               => array(
				'default'  => '',
				'sanitize' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			),
			'wcf_template_name'            => array(
				'default'  => '',
				'sanitize' => FILTER_SANITIZE_STRING,
			),
			'wcf_email_frequency'          => array(
				'default'  => 30,
				'sanitize' => FILTER_SANITIZE_NUMBER_INT,
			),
			'wcf_email_frequency_unit'     => array(
				'default'  => 'MINUTE',
				'sanitize' => FILTER_SANITIZE_STRING,
			),
			'wcf_activate_email_template'  => array(
				'default'  => 0,
				'sanitize' => FILTER_SANITIZE_NUMBER_INT,
			),

			'wcf_email_discount_type'      => array(
				'default'  => 'percent',
				'sanitize' => FILTER_SANITIZE_STRING,
			),
			'wcf_email_discount_amount'    => array(
				'default'  => 10,
				'sanitize' => FILTER_SANITIZE_NUMBER_INT,
			),
			'wcf_email_coupon_expiry_date' => array(
				'default'  => '',
				'sanitize' => FILTER_SANITIZE_STRING,
			),
			'wcf_coupon_expiry_unit'       => array(
				'default'  => 'hours',
				'sanitize' => FILTER_SANITIZE_STRING,
			),
			'id'                           => array(
				'default'  => null,
				'sanitize' => FILTER_SANITIZE_NUMBER_INT,
			),
		);

		$sanitized_post = array();
		foreach ( $input_post_values as $key => $input_post_value ) {

			if ( isset( $_POST[ $key ] ) ) {
				$sanitized_post[ $key ] = filter_input( INPUT_POST, $key, $input_post_value['sanitize'] );
			} else {
				$sanitized_post[ $key ] = $input_post_value['default'];
			}
		}

		$sanitized_post['wcf_override_global_coupon'] = isset( $_POST['wcf_override_global_coupon'] ) ? true : false;
		$sanitized_post['wcf_email_body']             = html_entity_decode( $sanitized_post['wcf_email_body'] );

		return $sanitized_post;

	}


	/**
	 *  Add email template callback ajax.
	 */
	function add_email_template() {

		$sanitized_post = $this->sanitize_email_post_data();
		$this->wpdb->insert(
			$this->cart_abandonment_template_table_name,
			array(
				'template_name'  => $sanitized_post['wcf_template_name'],
				'email_subject'  => $sanitized_post['wcf_email_subject'],
				'email_body'     => wpautop( $sanitized_post['wcf_email_body'] ),
				'frequency'      => $sanitized_post['wcf_email_frequency'],
				'frequency_unit' => $sanitized_post['wcf_email_frequency_unit'],
				'is_activated'   => $sanitized_post['wcf_activate_email_template'],
			),
			array( '%s', '%s', '%s', '%d', '%s', '%d' )
		);

		$email_template_id = $this->wpdb->insert_id;
		$meta_data         = array(
			'override_global_coupon' => $sanitized_post['wcf_override_global_coupon'],
			'discount_type'          => $sanitized_post['wcf_email_discount_type'],
			'coupon_amount'          => $sanitized_post['wcf_email_discount_amount'],
			'coupon_expiry_date'     => $sanitized_post['wcf_email_coupon_expiry_date'],
			'coupon_expiry_unit'     => $sanitized_post['wcf_coupon_expiry_unit'],
		);

		foreach ( $meta_data as $mera_key => $meta_value ) {
			$this->add_email_template_meta( $email_template_id, $mera_key, $meta_value );
		}

		$param        = array(
			'page'                    => WCF_CA_PAGE_NAME,
			'action'                  => WCF_ACTION_EMAIL_TEMPLATES,
			'sub_action'              => WCF_SUB_ACTION_EDIT_EMAIL_TEMPLATES,
			'id'                      => $email_template_id,
			'wcf_ca_template_created' => 'YES',
		);
		$redirect_url = add_query_arg( $param, admin_url( '/admin.php' ) );
		wp_safe_redirect( $redirect_url );

	}

	/**
	 *  Edit email template callback ajax.
	 */
	function edit_email_template() {
		$sanitized_post    = $this->sanitize_email_post_data();
		$email_template_id = $sanitized_post['id'];

		$this->wpdb->update(
			$this->cart_abandonment_template_table_name,
			array(
				'template_name'  => $sanitized_post['wcf_template_name'],
				'email_subject'  => $sanitized_post['wcf_email_subject'],
				'email_body'     => wpautop( $sanitized_post['wcf_email_body'] ),
				'frequency'      => $sanitized_post['wcf_email_frequency'],
				'frequency_unit' => $sanitized_post['wcf_email_frequency_unit'],
				'is_activated'   => $sanitized_post['wcf_activate_email_template'],
			),
			array( 'id' => $email_template_id ),
			array( '%s', '%s', '%s', '%d', '%s', '%d' ),
			array( '%d' )
		);

		$meta_data = array(
			'override_global_coupon' => $sanitized_post['wcf_override_global_coupon'],
			'discount_type'          => $sanitized_post['wcf_email_discount_type'],
			'coupon_amount'          => $sanitized_post['wcf_email_discount_amount'],
			'coupon_expiry_date'     => $sanitized_post['wcf_email_coupon_expiry_date'],
			'coupon_expiry_unit'     => $sanitized_post['wcf_coupon_expiry_unit'],
		);
		foreach ( $meta_data as $mera_key => $meta_value ) {
			$this->update_email_template_meta( $email_template_id, $mera_key, $meta_value );
		}

		$param        = array(
			'page'                    => WCF_CA_PAGE_NAME,
			'action'                  => WCF_ACTION_EMAIL_TEMPLATES,
			'sub_action'              => WCF_SUB_ACTION_EDIT_EMAIL_TEMPLATES,
			'id'                      => $email_template_id,
			'wcf_ca_template_updated' => 'YES',
		);
		$redirect_url = add_query_arg( $param, admin_url( '/admin.php' ) );

		wp_safe_redirect( $redirect_url );

	}

	/**
	 *  Restore default email templates.
	 */
	public function restore_email_templates() {

		$wpnonce = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING );

		if ( $wpnonce && wp_verify_nonce( $wpnonce, WCF_EMAIL_TEMPLATES_NONCE ) ) {

			include_once CARTFLOWS_CA_DIR . 'modules/cart-abandonment/class-cartflows-ca-cart-abandonment-db.php';
			$db = Cartflows_Ca_Cart_Abandonment_Db::get_instance();
			$db->template_table_seeder( true );

			$param        = array(
				'page'                     => WCF_CA_PAGE_NAME,
				'action'                   => WCF_ACTION_EMAIL_TEMPLATES,
				'wcf_ca_template_restored' => 'YES',
			);
			$redirect_url = add_query_arg( $param, admin_url( '/admin.php' ) );
			wp_safe_redirect( $redirect_url );
		}

	}

	/**
	 * Update the meta values.
	 *
	 * @param integer $email_template_id email template id.
	 * @param string  $meta_key meta key.
	 * @param string  $meta_value meta value.
	 */
	function update_email_template_meta( $email_template_id, $meta_key, $meta_value ) {

		$template_meta = $this->get_email_template_meta_by_key( $email_template_id, $meta_key );

		if ( $template_meta ) {
			$this->wpdb->update(
				$this->email_templates_meta_table,
				array(
					'meta_value' => sanitize_text_field( $meta_value ),
				),
				array(
					'email_template_id' => $email_template_id,
					'meta_key'          => sanitize_text_field( $meta_key ),
				)
			);
		} else {
			$this->add_email_template_meta( $email_template_id, $meta_key, $meta_value );
		}

	}


	/**
	 * Add the meta values.
	 *
	 * @param integer $email_template_id email template id.
	 * @param string  $meta_key meta key.
	 * @param string  $meta_value meta value.
	 */
	function add_email_template_meta( $email_template_id, $meta_key, $meta_value ) {
		$this->wpdb->insert(
			$this->email_templates_meta_table,
			array(
				'email_template_id' => $email_template_id,
				'meta_key'          => sanitize_text_field( $meta_key ),
				'meta_value'        => sanitize_text_field( $meta_value ),
			)
		);
	}

	/**
	 * Get the meta values.
	 *
	 * @param integer $email_template_id email template id.
	 * @param string  $meta_key meta key.
	 */
	function get_email_template_meta_by_key( $email_template_id, $meta_key ) {
		return $this->wpdb->get_row(
			$this->wpdb->prepare( "select * from $this->email_templates_meta_table where email_template_id = %d AND meta_key = %s", $email_template_id, $meta_key ) // phpcs:ignore
		);
	}

	/**
	 *  Render email template grid.
	 */
	function show_email_template_data_table() {
		$wcf_template_list = new Cartflows_Ca_Email_Templates_Table();
		$wcf_template_list->prepare_items();
		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
		?>
		<div class="wrap">
			<form id="wcf-cart-abandonment-template-table" method="GET">
				<input type="hidden" name="page" value="<?php echo esc_html( $page ); ?>"/>
				<input type="hidden" name="action" value="<?php echo esc_html( WCF_ACTION_EMAIL_TEMPLATES ); ?>"/>
				<input type="hidden" name="sub_action" value="<?php echo esc_html( WCF_SUB_ACTION_DELETE_BULK_EMAIL_TEMPLATES ); ?>"/>
				<?php $wcf_template_list->display(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 *  Render 'Add Email Template button'.
	 */
	public function show_add_new_template_button() {
		$param = array(
			'page'       => WCF_CA_PAGE_NAME,
			'action'     => WCF_ACTION_EMAIL_TEMPLATES,
			'sub_action' => WCF_SUB_ACTION_ADD_EMAIL_TEMPLATES,
		);

		$add_new_template_url = wp_nonce_url( add_query_arg( $param, admin_url( '/admin.php' ) ), WCF_EMAIL_TEMPLATES_NONCE );

		$param['sub_action']  = WCF_SUB_ACTION_RESTORE_EMAIL_TEMPLATES;
		$restore_template_url = wp_nonce_url( add_query_arg( $param, admin_url( '/admin.php' ) ), WCF_EMAIL_TEMPLATES_NONCE );

		?>
		<div class="wcf-ca-report-btn">
			<div  class="wcf-ca-left-report-field-group">
				<a style="cursor: pointer" href="<?php echo $add_new_template_url; ?>" class="button-secondary"><?php _e( 'Create New Template', 'cartflows-ca' ); ?></a>
			</div>
			<div  class="wcf-ca-right-report-field-group">
				<a onclick="return confirm('Are you sure to restore email templates?');" style="cursor: pointer" href="<?php echo $restore_template_url; ?>" class="button-secondary"><?php _e( ' Restore Default Templates', 'cartflows-ca' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Get all active templates.
	 *
	 * @return array|object|null
	 */
	public function fetch_all_active_templates() {
		$result = $this->wpdb->get_results(
			$this->wpdb->prepare('SELECT * FROM `' . $this->cart_abandonment_template_table_name . '` WHERE is_activated = %s', true) // phpcs:ignore
		);
		return $result;
	}

	/**
	 * Get specific template by id.
	 *
	 * @param integer $tmpl_id template id.
	 * @return array|object|void|null
	 */
	public function get_template_by_id( $tmpl_id ) {
		$result = $this->wpdb->get_row(
			$this->wpdb->prepare('SELECT * FROM `' . $this->cart_abandonment_template_table_name . '` WHERE id = %s', $tmpl_id) // phpcs:ignore
		);
		return $result;
	}

	/**
	 *  Get the email history.
	 *
	 * @param integer $email_history_id email history id.
	 * @return array|object|void|null
	 */
	public function get_email_history_by_id( $email_history_id ) {
		$result = $this->wpdb->get_row(
			$this->wpdb->prepare('SELECT * FROM `' . $this->email_history_table . '` WHERE id = %s', $email_history_id) // phpcs:ignore
		);
		return $result;
	}
}

Cartflows_Ca_Email_Templates::get_instance();
