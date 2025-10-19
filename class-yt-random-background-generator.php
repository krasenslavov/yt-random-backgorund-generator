<?php
/**
 * Plugin Name: YT Random Background Generator
 * Plugin URI: https://github.com/krasenslavov/yt-random-background-generator
 * Description: Dynamically assigns random background colors or images on each page load. Configure colors/images globally or per category/tag. Perfect for creative sites, portfolios, and dynamic visual experiences.
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Krasen Slavov
 * Author URI: https://krasenslavov.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: yt-random-background-generator
 * Domain Path: /languages
 *
 * @package YT_Random_Background_Generator
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'YT_RBG_VERSION', '1.0.0' );
define( 'YT_RBG_BASENAME', plugin_basename( __FILE__ ) );
define( 'YT_RBG_PATH', plugin_dir_path( __FILE__ ) );
define( 'YT_RBG_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main Plugin Class
 *
 * @since 1.0.0
 */
class YT_Random_Background_Generator {

	/**
	 * Single instance of the class.
	 *
	 * @var YT_Random_Background_Generator
	 */
	private static $instance = null;

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * Current background data.
	 *
	 * @var array
	 */
	private $current_background = array();

	/**
	 * Get single instance.
	 *
	 * @return YT_Random_Background_Generator
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->load_options();
		$this->init_hooks();
	}

	/**
	 * Load plugin options.
	 */
	private function load_options() {
		$this->options = get_option(
			'yt_rbg_options',
			array(
				'enabled'               => true,
				'background_type'       => 'color',
				'colors'                => array( '#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6' ),
				'images'                => array(),
				'image_size'            => 'cover',
				'image_position'        => 'center center',
				'image_repeat'          => 'no-repeat',
				'image_attachment'      => 'fixed',
				'change_frequency'      => 'every_load',
				'persist_session'       => false,
				'target_element'        => 'body',
				'custom_css'            => '',
				'enable_categories'     => false,
				'category_backgrounds'  => array(),
				'enable_tags'           => false,
				'tag_backgrounds'       => array(),
				'enable_post_types'     => false,
				'post_type_backgrounds' => array(),
				'fallback_color'        => '#ffffff',
				'transition_enabled'    => true,
				'transition_duration'   => '0.5s',
			)
		);
	}

	/**
	 * Initialize WordPress hooks.
	 */
	private function init_hooks() {
		// Plugin lifecycle.
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// Core hooks.
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Background injection.
		add_action( 'wp_head', array( $this, 'inject_background_css' ), 999 );

		// AJAX handlers.
		add_action( 'wp_ajax_yt_rbg_preview_background', array( $this, 'ajax_preview_background' ) );
		add_action( 'wp_ajax_yt_rbg_upload_image', array( $this, 'ajax_upload_image' ) );

		// Plugin links.
		add_filter( 'plugin_action_links_' . YT_RBG_BASENAME, array( $this, 'add_action_links' ) );

		// Session handling.
		if ( $this->options['persist_session'] ) {
			add_action( 'init', array( $this, 'start_session' ) );
		}
	}

	/**
	 * Plugin activation.
	 */
	public function activate() {
		if ( ! get_option( 'yt_rbg_options' ) ) {
			add_option(
				'yt_rbg_options',
				array(
					'enabled'               => true,
					'background_type'       => 'color',
					'colors'                => array( '#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6' ),
					'images'                => array(),
					'image_size'            => 'cover',
					'image_position'        => 'center center',
					'image_repeat'          => 'no-repeat',
					'image_attachment'      => 'fixed',
					'change_frequency'      => 'every_load',
					'persist_session'       => false,
					'target_element'        => 'body',
					'custom_css'            => '',
					'enable_categories'     => false,
					'category_backgrounds'  => array(),
					'enable_tags'           => false,
					'tag_backgrounds'       => array(),
					'enable_post_types'     => false,
					'post_type_backgrounds' => array(),
					'fallback_color'        => '#ffffff',
					'transition_enabled'    => true,
					'transition_duration'   => '0.5s',
				)
			);
		}

		// Set activation flag.
		set_transient( 'yt_rbg_activated', true, 30 );
	}

	/**
	 * Plugin deactivation.
	 */
	public function deactivate() {
		delete_transient( 'yt_rbg_activated' );
	}

	/**
	 * Load text domain for translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'yt-random-background-generator',
			false,
			dirname( YT_RBG_BASENAME ) . '/languages'
		);
	}

	/**
	 * Start PHP session if needed.
	 */
	public function start_session() {
		if ( ! session_id() && ! headers_sent() ) {
			session_start();
		}
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( 'settings_page_yt-random-background-generator' !== $hook ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_style(
			'yt-rbg-admin',
			YT_RBG_URL . 'assets/css/yt-random-background-generator.css',
			array(),
			YT_RBG_VERSION
		);

		wp_enqueue_script(
			'yt-rbg-admin',
			YT_RBG_URL . 'assets/js/yt-random-background-generator.js',
			array( 'jquery', 'wp-color-picker' ),
			YT_RBG_VERSION,
			true
		);

		wp_localize_script(
			'yt-rbg-admin',
			'ytRbgData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'yt_rbg_nonce' ),
				'strings' => array(
					'confirmDelete' => __( 'Are you sure you want to delete this item?', 'yt-random-background-generator' ),
					'selectImage'   => __( 'Select Background Image', 'yt-random-background-generator' ),
					'useImage'      => __( 'Use This Image', 'yt-random-background-generator' ),
					'uploadError'   => __( 'Error uploading image.', 'yt-random-background-generator' ),
					'previewError'  => __( 'Error generating preview.', 'yt-random-background-generator' ),
					'addColor'      => __( 'Add Color', 'yt-random-background-generator' ),
					'addImage'      => __( 'Add Image', 'yt-random-background-generator' ),
				),
			)
		);
	}

	/**
	 * Enqueue frontend assets.
	 */
	public function enqueue_frontend_assets() {
		if ( ! $this->options['enabled'] ) {
			return;
		}

		// Only enqueue CSS if transitions are enabled.
		if ( $this->options['transition_enabled'] ) {
			wp_enqueue_style(
				'yt-rbg-frontend',
				YT_RBG_URL . 'yt-random-background-generator.css',
				array(),
				YT_RBG_VERSION
			);
		}
	}

	/**
	 * Add settings page to admin menu.
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'Random Background Generator', 'yt-random-background-generator' ),
			__( 'Random Backgrounds', 'yt-random-background-generator' ),
			'manage_options',
			'yt-random-background-generator',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings.
	 */
	public function register_settings() {
		register_setting(
			'yt_rbg_settings',
			'yt_rbg_options',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_options' ),
			)
		);

		// General settings section.
		add_settings_section(
			'yt_rbg_general',
			__( 'General Settings', 'yt-random-background-generator' ),
			array( $this, 'render_general_section' ),
			'yt-random-background-generator'
		);

		// Background type section.
		add_settings_section(
			'yt_rbg_backgrounds',
			__( 'Background Configuration', 'yt-random-background-generator' ),
			array( $this, 'render_backgrounds_section' ),
			'yt-random-background-generator'
		);

		// Taxonomy settings section.
		add_settings_section(
			'yt_rbg_taxonomy',
			__( 'Category & Tag Settings', 'yt-random-background-generator' ),
			array( $this, 'render_taxonomy_section' ),
			'yt-random-background-generator'
		);

		// Advanced settings section.
		add_settings_section(
			'yt_rbg_advanced',
			__( 'Advanced Settings', 'yt-random-background-generator' ),
			array( $this, 'render_advanced_section' ),
			'yt-random-background-generator'
		);
	}

	/**
	 * Sanitize plugin options.
	 *
	 * @param array $input Raw input values.
	 * @return array Sanitized values.
	 */
	public function sanitize_options( $input ) {
		$sanitized = array();

		// General settings.
		$sanitized['enabled']          = ! empty( $input['enabled'] );
		$sanitized['background_type']  = sanitize_text_field( $input['background_type'] ?? 'color' );
		$sanitized['change_frequency'] = sanitize_text_field( $input['change_frequency'] ?? 'every_load' );
		$sanitized['persist_session']  = ! empty( $input['persist_session'] );
		$sanitized['target_element']   = sanitize_text_field( $input['target_element'] ?? 'body' );
		$sanitized['fallback_color']   = sanitize_hex_color( $input['fallback_color'] ?? '#ffffff' );

		// Colors.
		$sanitized['colors'] = array();
		if ( ! empty( $input['colors'] ) && is_array( $input['colors'] ) ) {
			foreach ( $input['colors'] as $color ) {
				$clean_color = sanitize_hex_color( $color );
				if ( $clean_color ) {
					$sanitized['colors'][] = $clean_color;
				}
			}
		}

		// Images.
		$sanitized['images'] = array();
		if ( ! empty( $input['images'] ) && is_array( $input['images'] ) ) {
			foreach ( $input['images'] as $image ) {
				$sanitized['images'][] = esc_url_raw( $image );
			}
		}

		// Image settings.
		$sanitized['image_size']       = sanitize_text_field( $input['image_size'] ?? 'cover' );
		$sanitized['image_position']   = sanitize_text_field( $input['image_position'] ?? 'center center' );
		$sanitized['image_repeat']     = sanitize_text_field( $input['image_repeat'] ?? 'no-repeat' );
		$sanitized['image_attachment'] = sanitize_text_field( $input['image_attachment'] ?? 'fixed' );

		// Taxonomy settings.
		$sanitized['enable_categories']     = ! empty( $input['enable_categories'] );
		$sanitized['enable_tags']           = ! empty( $input['enable_tags'] );
		$sanitized['enable_post_types']     = ! empty( $input['enable_post_types'] );
		$sanitized['category_backgrounds']  = $this->sanitize_taxonomy_backgrounds( $input['category_backgrounds'] ?? array() );
		$sanitized['tag_backgrounds']       = $this->sanitize_taxonomy_backgrounds( $input['tag_backgrounds'] ?? array() );
		$sanitized['post_type_backgrounds'] = $this->sanitize_taxonomy_backgrounds( $input['post_type_backgrounds'] ?? array() );

		// Advanced settings.
		$sanitized['transition_enabled']  = ! empty( $input['transition_enabled'] );
		$sanitized['transition_duration'] = sanitize_text_field( $input['transition_duration'] ?? '0.5s' );
		$sanitized['custom_css']          = wp_strip_all_tags( $input['custom_css'] ?? '' );

		return $sanitized;
	}

	/**
	 * Sanitize taxonomy backgrounds.
	 *
	 * @param array $backgrounds Raw backgrounds array.
	 * @return array Sanitized backgrounds.
	 */
	private function sanitize_taxonomy_backgrounds( $backgrounds ) {
		$sanitized = array();

		if ( ! is_array( $backgrounds ) ) {
			return $sanitized;
		}

		foreach ( $backgrounds as $term_id => $data ) {
			$term_id = absint( $term_id );
			if ( ! $term_id ) {
				continue;
			}

			$sanitized[ $term_id ] = array(
				'type'  => sanitize_text_field( $data['type'] ?? 'color' ),
				'value' => ( 'color' === ( $data['type'] ?? 'color' ) )
					? sanitize_hex_color( $data['value'] ?? '#ffffff' )
					: esc_url_raw( $data['value'] ?? '' ),
			);
		}

		return $sanitized;
	}

	/**
	 * Render general settings section.
	 */
	public function render_general_section() {
		echo '<p>' . esc_html__( 'Configure how random backgrounds are generated and displayed.', 'yt-random-background-generator' ) . '</p>';
	}

	/**
	 * Render backgrounds settings section.
	 */
	public function render_backgrounds_section() {
		echo '<p>' . esc_html__( 'Add colors or images to be randomly selected.', 'yt-random-background-generator' ) . '</p>';
	}

	/**
	 * Render taxonomy settings section.
	 */
	public function render_taxonomy_section() {
		echo '<p>' . esc_html__( 'Assign specific backgrounds to categories, tags, or post types.', 'yt-random-background-generator' ) . '</p>';
	}

	/**
	 * Render advanced settings section.
	 */
	public function render_advanced_section() {
		echo '<p>' . esc_html__( 'Fine-tune background behavior and styling.', 'yt-random-background-generator' ) . '</p>';
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Show success message.
		if ( isset( $_GET['settings-updated'] ) ) {
			add_settings_error(
				'yt_rbg_messages',
				'yt_rbg_message',
				__( 'Settings saved successfully.', 'yt-random-background-generator' ),
				'success'
			);
		}

		settings_errors( 'yt_rbg_messages' );
		?>
		<div class="wrap yt-rbg-settings-page">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<form method="post" action="options.php">
				<?php settings_fields( 'yt_rbg_settings' ); ?>

				<!-- General Settings -->
				<div class="yt-rbg-section">
					<h2><?php esc_html_e( 'General Settings', 'yt-random-background-generator' ); ?></h2>

					<table class="form-table">
						<tr>
							<th><?php esc_html_e( 'Enable Random Backgrounds', 'yt-random-background-generator' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="yt_rbg_options[enabled]" value="1" <?php checked( $this->options['enabled'], true ); ?>>
									<?php esc_html_e( 'Enable random background generation', 'yt-random-background-generator' ); ?>
								</label>
							</td>
						</tr>

						<tr>
							<th><?php esc_html_e( 'Background Type', 'yt-random-background-generator' ); ?></th>
							<td>
								<select name="yt_rbg_options[background_type]" id="yt-rbg-background-type">
									<option value="color" <?php selected( $this->options['background_type'], 'color' ); ?>>
										<?php esc_html_e( 'Solid Colors', 'yt-random-background-generator' ); ?>
									</option>
									<option value="image" <?php selected( $this->options['background_type'], 'image' ); ?>>
										<?php esc_html_e( 'Background Images', 'yt-random-background-generator' ); ?>
									</option>
									<option value="mixed" <?php selected( $this->options['background_type'], 'mixed' ); ?>>
										<?php esc_html_e( 'Mixed (Colors + Images)', 'yt-random-background-generator' ); ?>
									</option>
								</select>
							</td>
						</tr>

						<tr>
							<th><?php esc_html_e( 'Change Frequency', 'yt-random-background-generator' ); ?></th>
							<td>
								<select name="yt_rbg_options[change_frequency]">
									<option value="every_load" <?php selected( $this->options['change_frequency'], 'every_load' ); ?>>
										<?php esc_html_e( 'Every page load', 'yt-random-background-generator' ); ?>
									</option>
									<option value="daily" <?php selected( $this->options['change_frequency'], 'daily' ); ?>>
										<?php esc_html_e( 'Once per day', 'yt-random-background-generator' ); ?>
									</option>
									<option value="session" <?php selected( $this->options['change_frequency'], 'session' ); ?>>
										<?php esc_html_e( 'Once per session', 'yt-random-background-generator' ); ?>
									</option>
								</select>
								<p class="description">
									<?php esc_html_e( 'How often the background should change', 'yt-random-background-generator' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th><?php esc_html_e( 'Target Element', 'yt-random-background-generator' ); ?></th>
							<td>
								<input type="text" name="yt_rbg_options[target_element]" value="<?php echo esc_attr( $this->options['target_element'] ); ?>" class="regular-text">
								<p class="description">
									<?php esc_html_e( 'CSS selector for the element to apply background (e.g., body, .site-content)', 'yt-random-background-generator' ); ?>
								</p>
							</td>
						</tr>
					</table>
				</div>

				<!-- Background Colors -->
				<div class="yt-rbg-section yt-rbg-colors-section">
					<h2><?php esc_html_e( 'Background Colors', 'yt-random-background-generator' ); ?></h2>

					<div id="yt-rbg-colors-list" class="yt-rbg-items-list">
						<?php
						if ( ! empty( $this->options['colors'] ) ) {
							foreach ( $this->options['colors'] as $index => $color ) {
								$this->render_color_item( $color, $index );
							}
						}
						?>
					</div>

					<button type="button" id="yt-rbg-add-color" class="button">
						<?php esc_html_e( 'Add Color', 'yt-random-background-generator' ); ?>
					</button>
				</div>

				<!-- Background Images -->
				<div class="yt-rbg-section yt-rbg-images-section">
					<h2><?php esc_html_e( 'Background Images', 'yt-random-background-generator' ); ?></h2>

					<div id="yt-rbg-images-list" class="yt-rbg-items-list">
						<?php
						if ( ! empty( $this->options['images'] ) ) {
							foreach ( $this->options['images'] as $index => $image_url ) {
								$this->render_image_item( $image_url, $index );
							}
						}
						?>
					</div>

					<button type="button" id="yt-rbg-add-image" class="button">
						<?php esc_html_e( 'Add Image', 'yt-random-background-generator' ); ?>
					</button>

					<h3><?php esc_html_e( 'Image Display Settings', 'yt-random-background-generator' ); ?></h3>
					<table class="form-table">
						<tr>
							<th><?php esc_html_e( 'Background Size', 'yt-random-background-generator' ); ?></th>
							<td>
								<select name="yt_rbg_options[image_size]">
									<option value="cover" <?php selected( $this->options['image_size'], 'cover' ); ?>><?php esc_html_e( 'Cover', 'yt-random-background-generator' ); ?></option>
									<option value="contain" <?php selected( $this->options['image_size'], 'contain' ); ?>><?php esc_html_e( 'Contain', 'yt-random-background-generator' ); ?></option>
									<option value="auto" <?php selected( $this->options['image_size'], 'auto' ); ?>><?php esc_html_e( 'Auto', 'yt-random-background-generator' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Background Position', 'yt-random-background-generator' ); ?></th>
							<td>
								<select name="yt_rbg_options[image_position]">
									<option value="center center" <?php selected( $this->options['image_position'], 'center center' ); ?>><?php esc_html_e( 'Center', 'yt-random-background-generator' ); ?></option>
									<option value="top left" <?php selected( $this->options['image_position'], 'top left' ); ?>><?php esc_html_e( 'Top Left', 'yt-random-background-generator' ); ?></option>
									<option value="top center" <?php selected( $this->options['image_position'], 'top center' ); ?>><?php esc_html_e( 'Top Center', 'yt-random-background-generator' ); ?></option>
									<option value="top right" <?php selected( $this->options['image_position'], 'top right' ); ?>><?php esc_html_e( 'Top Right', 'yt-random-background-generator' ); ?></option>
									<option value="bottom center" <?php selected( $this->options['image_position'], 'bottom center' ); ?>><?php esc_html_e( 'Bottom Center', 'yt-random-background-generator' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Background Repeat', 'yt-random-background-generator' ); ?></th>
							<td>
								<select name="yt_rbg_options[image_repeat]">
									<option value="no-repeat" <?php selected( $this->options['image_repeat'], 'no-repeat' ); ?>><?php esc_html_e( 'No Repeat', 'yt-random-background-generator' ); ?></option>
									<option value="repeat" <?php selected( $this->options['image_repeat'], 'repeat' ); ?>><?php esc_html_e( 'Repeat', 'yt-random-background-generator' ); ?></option>
									<option value="repeat-x" <?php selected( $this->options['image_repeat'], 'repeat-x' ); ?>><?php esc_html_e( 'Repeat X', 'yt-random-background-generator' ); ?></option>
									<option value="repeat-y" <?php selected( $this->options['image_repeat'], 'repeat-y' ); ?>><?php esc_html_e( 'Repeat Y', 'yt-random-background-generator' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Background Attachment', 'yt-random-background-generator' ); ?></th>
							<td>
								<select name="yt_rbg_options[image_attachment]">
									<option value="fixed" <?php selected( $this->options['image_attachment'], 'fixed' ); ?>><?php esc_html_e( 'Fixed (Parallax)', 'yt-random-background-generator' ); ?></option>
									<option value="scroll" <?php selected( $this->options['image_attachment'], 'scroll' ); ?>><?php esc_html_e( 'Scroll', 'yt-random-background-generator' ); ?></option>
								</select>
							</td>
						</tr>
					</table>
				</div>

				<!-- Category Settings -->
				<div class="yt-rbg-section">
					<h2><?php esc_html_e( 'Category & Tag Backgrounds', 'yt-random-background-generator' ); ?></h2>

					<table class="form-table">
						<tr>
							<th><?php esc_html_e( 'Enable Category Backgrounds', 'yt-random-background-generator' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="yt_rbg_options[enable_categories]" value="1" <?php checked( $this->options['enable_categories'], true ); ?>>
									<?php esc_html_e( 'Assign specific backgrounds to categories', 'yt-random-background-generator' ); ?>
								</label>
							</td>
						</tr>
					</table>

					<?php if ( $this->options['enable_categories'] ) : ?>
					<div class="yt-rbg-taxonomy-list">
						<h3><?php esc_html_e( 'Category Backgrounds', 'yt-random-background-generator' ); ?></h3>
						<?php $this->render_taxonomy_backgrounds( 'category' ); ?>
					</div>
					<?php endif; ?>
				</div>

				<!-- Advanced Settings -->
				<div class="yt-rbg-section">
					<h2><?php esc_html_e( 'Advanced Settings', 'yt-random-background-generator' ); ?></h2>

					<table class="form-table">
						<tr>
							<th><?php esc_html_e( 'Enable Transitions', 'yt-random-background-generator' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="yt_rbg_options[transition_enabled]" value="1" <?php checked( $this->options['transition_enabled'], true ); ?>>
									<?php esc_html_e( 'Enable smooth background transitions', 'yt-random-background-generator' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Transition Duration', 'yt-random-background-generator' ); ?></th>
							<td>
								<input type="text" name="yt_rbg_options[transition_duration]" value="<?php echo esc_attr( $this->options['transition_duration'] ); ?>" class="small-text">
								<p class="description"><?php esc_html_e( 'Duration in seconds (e.g., 0.5s, 1s)', 'yt-random-background-generator' ); ?></p>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Fallback Color', 'yt-random-background-generator' ); ?></th>
							<td>
								<input type="text" name="yt_rbg_options[fallback_color]" value="<?php echo esc_attr( $this->options['fallback_color'] ); ?>" class="yt-rbg-color-picker">
								<p class="description"><?php esc_html_e( 'Backup color if no backgrounds are configured', 'yt-random-background-generator' ); ?></p>
							</td>
						</tr>
					</table>
				</div>

				<?php submit_button( __( 'Save Settings', 'yt-random-background-generator' ) ); ?>
			</form>

			<!-- Preview Box -->
			<div class="yt-rbg-preview-box">
				<h3><?php esc_html_e( 'Background Preview', 'yt-random-background-generator' ); ?></h3>
				<div id="yt-rbg-preview" class="yt-rbg-preview-area">
					<?php esc_html_e( 'Preview area', 'yt-random-background-generator' ); ?>
				</div>
				<button type="button" id="yt-rbg-generate-preview" class="button">
					<?php esc_html_e( 'Generate Random Preview', 'yt-random-background-generator' ); ?>
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Render color item.
	 *
	 * @param string $color Color value.
	 * @param int    $index Item index.
	 */
	private function render_color_item( $color, $index ) {
		?>
		<div class="yt-rbg-item yt-rbg-color-item">
			<input type="text" name="yt_rbg_options[colors][]" value="<?php echo esc_attr( $color ); ?>" class="yt-rbg-color-picker">
			<button type="button" class="button yt-rbg-remove-item"><?php esc_html_e( 'Remove', 'yt-random-background-generator' ); ?></button>
		</div>
		<?php
	}

	/**
	 * Render image item.
	 *
	 * @param string $image_url Image URL.
	 * @param int    $index     Item index.
	 */
	private function render_image_item( $image_url, $index ) {
		?>
		<div class="yt-rbg-item yt-rbg-image-item">
			<div class="yt-rbg-image-preview" style="background-image: url('<?php echo esc_url( $image_url ); ?>')"></div>
			<input type="hidden" name="yt_rbg_options[images][]" value="<?php echo esc_url( $image_url ); ?>">
			<input type="text" value="<?php echo esc_url( $image_url ); ?>" readonly class="regular-text">
			<button type="button" class="button yt-rbg-remove-item"><?php esc_html_e( 'Remove', 'yt-random-background-generator' ); ?></button>
		</div>
		<?php
	}

	/**
	 * Render taxonomy backgrounds.
	 *
	 * @param string $taxonomy Taxonomy name.
	 */
	private function render_taxonomy_backgrounds( $taxonomy ) {
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			)
		);

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			echo '<p>' . esc_html__( 'No terms found.', 'yt-random-background-generator' ) . '</p>';
			return;
		}

		$key = $taxonomy . '_backgrounds';
		?>
		<table class="widefat">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Term', 'yt-random-background-generator' ); ?></th>
					<th><?php esc_html_e( 'Type', 'yt-random-background-generator' ); ?></th>
					<th><?php esc_html_e( 'Value', 'yt-random-background-generator' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $terms as $term ) : ?>
					<?php
					$saved_bg = $this->options[ $key ][ $term->term_id ] ?? array(
						'type'  => 'color',
						'value' => '#ffffff',
					);
					?>
				<tr>
					<td><strong><?php echo esc_html( $term->name ); ?></strong></td>
					<td>
						<select name="yt_rbg_options[<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $term->term_id ); ?>][type]">
							<option value="color" <?php selected( $saved_bg['type'], 'color' ); ?>><?php esc_html_e( 'Color', 'yt-random-background-generator' ); ?></option>
							<option value="image" <?php selected( $saved_bg['type'], 'image' ); ?>><?php esc_html_e( 'Image', 'yt-random-background-generator' ); ?></option>
						</select>
					</td>
					<td>
						<input type="text" name="yt_rbg_options[<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $term->term_id ); ?>][value]" value="<?php echo esc_attr( $saved_bg['value'] ); ?>" class="regular-text">
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Inject background CSS into frontend.
	 */
	public function inject_background_css() {
		if ( ! $this->options['enabled'] ) {
			return;
		}

		$background = $this->get_current_background();

		if ( empty( $background ) ) {
			return;
		}

		$css = $this->generate_background_css( $background );

		echo "\n<!-- Random Background Generator -->\n";
		echo '<style id="yt-rbg-inline-css">' . $css . '</style>' . "\n";
	}

	/**
	 * Get current background for the page.
	 *
	 * @return array Background data.
	 */
	private function get_current_background() {
		// Check if cached.
		if ( ! empty( $this->current_background ) ) {
			return $this->current_background;
		}

		// Check for category/tag specific backgrounds.
		$specific_bg = $this->get_specific_background();
		if ( $specific_bg ) {
			$this->current_background = $specific_bg;
			return $specific_bg;
		}

		// Get random background based on frequency.
		$background = $this->get_random_background();

		$this->current_background = $background;
		return $background;
	}

	/**
	 * Get specific background for category/tag.
	 *
	 * @return array|null Background data or null.
	 */
	private function get_specific_background() {
		// Check categories.
		if ( $this->options['enable_categories'] && is_category() ) {
			$term_id = get_queried_object_id();
			if ( isset( $this->options['category_backgrounds'][ $term_id ] ) ) {
				return $this->options['category_backgrounds'][ $term_id ];
			}
		}

		// Check single post categories.
		if ( $this->options['enable_categories'] && is_single() ) {
			$categories = get_the_category();
			if ( ! empty( $categories ) ) {
				foreach ( $categories as $category ) {
					if ( isset( $this->options['category_backgrounds'][ $category->term_id ] ) ) {
						return $this->options['category_backgrounds'][ $category->term_id ];
					}
				}
			}
		}

		return null;
	}

	/**
	 * Get random background.
	 *
	 * @return array Background data.
	 */
	private function get_random_background() {
		$frequency = $this->options['change_frequency'];

		// Generate seed based on frequency.
		$seed = $this->get_seed( $frequency );

		// Use seed for consistent randomness.
		if ( $seed ) {
			mt_srand( $seed );
		}

		$type = $this->options['background_type'];

		if ( 'color' === $type ) {
			$background = $this->get_random_color();
		} elseif ( 'image' === $type ) {
			$background = $this->get_random_image();
		} else {
			// Mixed mode.
			$background = ( mt_rand( 0, 1 ) === 0 ) ? $this->get_random_color() : $this->get_random_image();
		}

		return $background;
	}

	/**
	 * Get seed based on frequency.
	 *
	 * @param string $frequency Change frequency.
	 * @return int|null Seed value.
	 */
	private function get_seed( $frequency ) {
		if ( 'every_load' === $frequency ) {
			return null; // Truly random.
		}

		if ( 'daily' === $frequency ) {
			return (int) current_time( 'Ymd' );
		}

		if ( 'session' === $frequency && isset( $_SESSION['yt_rbg_seed'] ) ) {
			return $_SESSION['yt_rbg_seed'];
		}

		if ( 'session' === $frequency ) {
			$seed                    = time();
			$_SESSION['yt_rbg_seed'] = $seed;
			return $seed;
		}

		return null;
	}

	/**
	 * Get random color.
	 *
	 * @return array Color data.
	 */
	private function get_random_color() {
		$colors = $this->options['colors'];

		if ( empty( $colors ) ) {
			return array(
				'type'  => 'color',
				'value' => $this->options['fallback_color'],
			);
		}

		$random_key = array_rand( $colors );

		return array(
			'type'  => 'color',
			'value' => $colors[ $random_key ],
		);
	}

	/**
	 * Get random image.
	 *
	 * @return array Image data.
	 */
	private function get_random_image() {
		$images = $this->options['images'];

		if ( empty( $images ) ) {
			return array(
				'type'  => 'color',
				'value' => $this->options['fallback_color'],
			);
		}

		$random_key = array_rand( $images );

		return array(
			'type'  => 'image',
			'value' => $images[ $random_key ],
		);
	}

	/**
	 * Generate CSS for background.
	 *
	 * @param array $background Background data.
	 * @return string CSS code.
	 */
	private function generate_background_css( $background ) {
		$target = $this->options['target_element'];
		$css    = '';

		if ( 'color' === $background['type'] ) {
			$css .= sprintf(
				'%s { background-color: %s !important; }',
				esc_attr( $target ),
				esc_attr( $background['value'] )
			);
		} else {
			$css .= sprintf(
				'%s { background-image: url(%s) !important; background-size: %s !important; background-position: %s !important; background-repeat: %s !important; background-attachment: %s !important; }',
				esc_attr( $target ),
				esc_url( $background['value'] ),
				esc_attr( $this->options['image_size'] ),
				esc_attr( $this->options['image_position'] ),
				esc_attr( $this->options['image_repeat'] ),
				esc_attr( $this->options['image_attachment'] )
			);
		}

		// Add transition.
		if ( $this->options['transition_enabled'] ) {
			$css .= sprintf(
				' %s { transition: background-color %s ease, background-image %s ease; }',
				esc_attr( $target ),
				esc_attr( $this->options['transition_duration'] ),
				esc_attr( $this->options['transition_duration'] )
			);
		}

		// Add custom CSS.
		if ( ! empty( $this->options['custom_css'] ) ) {
			$css .= ' ' . $this->options['custom_css'];
		}

		return $css;
	}

	/**
	 * AJAX handler for preview.
	 */
	public function ajax_preview_background() {
		check_ajax_referer( 'yt_rbg_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'yt-random-background-generator' ) ) );
		}

		$background = $this->get_random_background();

		wp_send_json_success(
			array(
				'background' => $background,
				'css'        => $this->generate_background_css( $background ),
			)
		);
	}

	/**
	 * Add plugin action links.
	 *
	 * @param array $links Existing links.
	 * @return array Modified links.
	 */
	public function add_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'options-general.php?page=yt-random-background-generator' ),
			__( 'Settings', 'yt-random-background-generator' )
		);

		array_unshift( $links, $settings_link );

		return $links;
	}
}

/**
 * Initialize the plugin.
 */
function yt_rbg_init() {
	return YT_Random_Background_Generator::get_instance();
}

// Bootstrap the plugin.
yt_rbg_init();
