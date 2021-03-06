<?php

/**
 *
 * Simple Smugmug admin setting.
 *
 * @link       https://renrizzolo.github.com/wp-simple-smugmug
 * @since      1.0.0
 *
 * @package    Simple_Smugmug
 * @subpackage Simple_Smugmug/admin
 */
class Simple_Smugmug_Admin {
	/**
	 * Hook into admin_menu and admin_init
	 **/
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'smug_add_admin_menu' ) );
			add_action( 'admin_init', array( $this, 'smug_settings_init' ) );
		}
	}

	/**
	 * Returns the array of default options.
	 **/
	protected function defaults() {
		return array(
			'api_key'                     => 'your api key',
			'smugmug_username'            => 'your smugmug username',
			'cache_expiry'                => 60 * 60 * 6,
			'album_count'                 => 3,
			'image_count'                 => 8,
			'force_https'                 => 0,
			'display_in_lightgallery'     => 1,
			'show_gallery_buy_link'       => 1,
			'show_album_title'            => 1,
			'album_container_class'       => 'simple-smugmug-widget',
			'first_image_container_class' => 'simple-smugmug-col-3',
			'image_container_class'       => 'simple-smugmug-col-3',
			'image_class'                 => 'simple-smugmug-img',
			'title_class'                 => 'simple-smugmug-heading',
			'link_class'                  => 'simple-smugmug-link',
			'smug_link_icon'              => 'lg-cart lg-icon',

		);
	}

	/**
	 * Returns a default option by its key.
	 *
	 * @param string $key The key of the default option.
	 **/
	public function get_default( $key ) {
		$defaults = $this->defaults();
		return $defaults[ $key ];
	}

	/**
	 * Returns an option by its key.
	 * If empty or not set, returns the default.
	 *
	 * @param string $key The key of the option.
	 **/
	public function get_option( $key ) {
		$options = get_option( 'smug_settings' );

		if ( isset( $options[ $key ] ) && '' !== $options[ $key ] ) {
			return $options[ $key ];
		} else {
			return $this->defaults()[ $key ];
		}
	}

	/**
	 * Resets options to their defaults ( called via ajax )
	 **/
	public function smug_reset_defaults() {

		$options = get_option( 'smug_settings' );
		foreach ( $options as $key => $value ) {

			// Don't reset api key and username!
			if ( 'api_key' !== $key && 'smugmug_username' !== $key ) {
				$options[ $key ] = $this->get_default( $key );
			}
		}
		update_option( 'smug_settings', $options );
	}

	/**
	 * Adds the options.
	 **/
	public function init_options() {
		$options = $this->defaults();
		add_option( 'smug_settings', $options );
	}

	/**
	 * Add Simple Smugmug to Settings menu.
	 **/
	public function smug_add_admin_menu() {

		add_options_page( 'Simple Smugmug', 'Simple Smugmug', 'manage_options', 'simple_smugmug', array( $this, 'smug_options_page' ) );

	}

	/**
	 * Validates text/number input options being saved
	 *
	 * @param array $options The options to be validated.
	 **/
	public function smug_settings_validate( $options ) {

		// Create our array for storing the validated options.
		$output = array();

		// hard setting the checkboxes to 0 ( they will be overwritten to 1 if they are set (i.e checked) in the foreach loop ).
		$output['show_album_title']        = 0;
		$output['force_https']             = 0;
		$output['display_in_lightgallery'] = 0;
		$output['show_gallery_buy_link']   = 0;
		// Loop through each of the incoming options.
		foreach ( $options as $key => $value ) {

			// Check to see if the current option has a value. If so, process it.
			if ( isset( $options[ $key ] ) ) {

				// Strip all HTML and PHP tags and properly handle quoted strings.
				$output[ $key ] = sanitize_text_field( $options[ $key ] );
			}

		}

		// Return the sanitized array.
		return $output;
	}

	/**
	 * Validates checkbox options being saved
	 *
	 * @param array $options The options to be validated.
	 **/
	public function smug_settings_validate_checkboxes( $options ) {

		// return 1 or 0 for checkboxes instead of unsetting when unchcecked.
		$output = array();
		foreach ( $options as $key => $value ) {
			$output[$key] = ( 1 === $option[$key] ) ? 1 : 0;
		}
		return $output;
	}

	/**
	 * Initialize the settings page.
	 *
	 * Registers settings and adds settings sections using the plugin settings API.
	 **/
	public function smug_settings_init() {

		// Add the options if they haven't been added already.
		$this->init_options();

		add_action( 'wp_ajax_smug_reset_defaults', array( $this, 'smug_reset_defaults' ) );

		register_setting(
			'pluginPage',
			'smug_settings',
			array( $this, 'smug_settings_validate' )
		);

		add_settings_section(
			'smug_pluginPage_section',
			__( 'Settings', 'simple_smugmug' ),
			array( $this, 'smug_settings_section_callback' ),
			'pluginPage'
		);

		add_settings_section(
			'smug_styles_section',
			__( 'Styles', 'simple_smugmug' ),
			array( $this, 'smug_styles_section_callback' ),
			'pluginPage'
		);

		add_settings_field(
			'api_key',
			__( 'Smugmug API key', 'simple_smugmug' ),
			array( $this, 'smug_text_field_api_key_render' ),
			'pluginPage',
			'smug_pluginPage_section'
		);

		add_settings_field(
			'smugmug_username',
			__( 'Smugmug username', 'simple_smugmug' ),
			array( $this, 'smug_text_field_smugmug_username_render' ),
			'pluginPage',
			'smug_pluginPage_section'
		);
		add_settings_field(
			'show_album_title',
			__( 'Show album title/links', 'simple_smugmug' ),
			array( $this, 'smug_text_field_show_album_title_render' ),
			'pluginPage',
			'smug_pluginPage_section'
		);

		add_settings_field(
			'force_https',
			__( 'Force https in album links', 'simple_smugmug' ),
			array( $this, 'smug_text_field_force_https_render' ),
			'pluginPage',
			'smug_pluginPage_section'
		);

		add_settings_field(
			'display_in_lightgallery',
			__( 'Open thumbs in lightGallery instead of linking directly to smugmug album', 'simple_smugmug' ),
			array( $this, 'smug_text_field_display_in_lightgallery_render' ),
			'pluginPage',
			'smug_pluginPage_section'
		);

		add_settings_field(
			'show_gallery_buy_link',
			__( 'If using lightgallery, show a buy link for each image', 'simple_smugmug' ),
			array( $this, 'smug_text_field_show_gallery_buy_link_render' ),
			'pluginPage',
			'smug_pluginPage_section'
		);

		add_settings_field(
			'cache_expiry',
			__( 'Cache expiry time in seconds', 'simple_smugmug' ),
			array( $this, 'smug_text_field_cache_expiry_render' ),
			'pluginPage',
			'smug_pluginPage_section'
		);

		add_settings_field(
			'album_count',
			__( 'number of albums to show', 'simple_smugmug' ),
			array( $this, 'smug_text_album_count_render' ),
			'pluginPage',
			'smug_pluginPage_section'
		);

		add_settings_field(
			'image_count',
			__( 'Number of images per album to show (max 100)', 'simple_smugmug' ),
			array( $this, 'smug_text_image_count_render' ),
			'pluginPage',
			'smug_pluginPage_section'
		);

		add_settings_field(
			'album_container_class',
			__( 'Album container class', 'simple_smugmug' ),
			array( $this, 'smug_text_album_container_class_render' ),
			'pluginPage',
			'smug_styles_section'
		);

		add_settings_field(
			'first_image_container_class',
			__( 'First image container class', 'simple_smugmug' ),
			array( $this, 'smug_text_first_image_container_class_render' ),
			'pluginPage',
			'smug_styles_section'
		);

		add_settings_field(
			'image_container_class',
			__( 'Image container class', 'simple_smugmug' ),
			array( $this, 'smug_text_image_container_class_render' ),
			'pluginPage',
			'smug_styles_section'
		);

		add_settings_field(
			'image_class',
			__( 'Image class', 'simple_smugmug' ),
			array( $this, 'smug_text_image_class_render' ),
			'pluginPage',
			'smug_styles_section'
		);

		add_settings_field(
			'title_class',
			__( 'Album title class', 'simple_smugmug' ),
			array( $this, 'smug_text_title_class_render' ),
			'pluginPage',
			'smug_styles_section'
		);

		add_settings_field(
			'link_class',
			__( 'Album title link class', 'simple_smugmug' ),
			array( $this, 'smug_text_link_class_render' ),
			'pluginPage',
			'smug_styles_section'
		);

		add_settings_field(
			'smug_link_icon',
			__( 'Gallery buy link icon class', 'simple_smugmug' ),
			array( $this, 'smug_text_smug_link_icon_render' ),
			'pluginPage',
			'smug_styles_section'
		);

		add_settings_field(
			'reset_defaults_button',
			__( 'Reset values to their defaults', 'simple_smugmug' ),
			array( $this, 'reset_defaults_button' ),
			'pluginPage',
			'smug_styles_section'
		);

	}

	/**
	 * JS and html for reset defaults button
	 **/
	public function reset_defaults_button() {
	?>
		<script>
			function resetDefaults(event) {
				event.preventDefault();

				var data = {
					'action': 'smug_reset_defaults',
				};

				jQuery('#reset_defaults_button').attr('disabled', 'true');

				jQuery.post('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', data, function(response) {
					console.log(response);
					jQuery('#reset_defaults_button').text('Done!').attr('disabled', 'true');
					window.location.reload();
				}, 'json');

		}
		</script>
	<button onClick="resetDefaults(event)" id="reset_defaults_button">Reset</button>
	<?php
	}

	public function smug_text_field_api_key_render() {
		$option = $this->get_option( 'api_key' );

		?>
		<input class="widefat" type='text' name='smug_settings[api_key]' value='<?php echo esc_html( $option ); ?>'>
		<?php

	}


	public function smug_text_field_smugmug_username_render() {
		$option = $this->get_option( 'smugmug_username' );

		?>
		<input class="widefat" type='text' name='smug_settings[smugmug_username]' value='<?php echo esc_html( $option ); ?>'>
		<?php

	}
	public function smug_text_field_show_album_title_render() {
		$option = get_option( 'smug_settings' );

		?>
		<input type='checkbox' id="smug_settings[show_album_title]" name='smug_settings[show_album_title]' value="1" <?php checked( '1', $option['show_album_title'] ); ?> />
		<?php

	}
	public function smug_text_field_force_https_render() {
		$option = get_option( 'smug_settings' );
		?>
		<input type='checkbox' id="smug_settings[force_https]" name='smug_settings[force_https]' value="1" <?php checked( '1', $option['force_https'] ); ?> />
		<?php

	}
	public function smug_text_field_display_in_lightgallery_render() {
		$option = get_option( 'smug_settings' );
		?>
		<input type='checkbox' id="smug_settings[display_in_lightgallery]" name='smug_settings[display_in_lightgallery]' value="1" <?php checked( '1', $option['display_in_lightgallery'] ); ?> />
		<?php

	}
	public function smug_text_field_show_gallery_buy_link_render() {
		$option = get_option( 'smug_settings' );
		?>
		<input type='checkbox' id="smug_settings[show_gallery_buy_link]" name='smug_settings[show_gallery_buy_link]' value="1" <?php checked( '1', $option['show_gallery_buy_link'] ); ?> />
		<?php

	}
	public function smug_text_field_cache_expiry_render() {
		$option = $this->get_option( 'cache_expiry' );
		?>
		<input type='number' name='smug_settings[cache_expiry]' value='<?php echo esc_html( $option ); ?>'>
		<?php

	}


	public function smug_text_album_count_render() {
		$option = $this->get_option( 'album_count' );
		?>
		<input type='number' name='smug_settings[album_count]' value='<?php echo esc_html( $option ); ?>'>
		<?php

	}


	public function smug_text_image_count_render() {
		$option = $this->get_option( 'image_count' );
		?>
		<input type='number' name='smug_settings[image_count]' value='<?php echo esc_html( $option ); ?>'>
		<?php

	}


	public function smug_text_album_container_class_render() {

		$option = $this->get_option( 'album_container_class' );
		?>
		<input class="widefat" type='text' name='smug_settings[album_container_class]' value='<?php echo esc_html( $option ); ?>'>
		<?php

	}


	public function smug_text_first_image_container_class_render() {

		$option = $this->get_option( 'first_image_container_class' );
		?>
		<input class="widefat" type='text' name='smug_settings[first_image_container_class]' value='<?php echo esc_html( $option ); ?>'>
		<?php

	}

	public function smug_text_image_container_class_render() {

		$option = $this->get_option( 'image_container_class' );
		?>
		<input class="widefat" type='text' name='smug_settings[image_container_class]' value='<?php echo esc_html( $option ); ?>'>
		<?php

	}

	public function smug_text_image_class_render() {
		$option = $this->get_option( 'image_class' );
		?>
		<input class="widefat" type='text' name='smug_settings[image_class]' value='<?php echo esc_html( $option ); ?>'>
		<?php

	}

	public function smug_text_title_class_render() {

		$option = $this->get_option( 'title_class' );
		?>
		<input class="widefat" type='text' name='smug_settings[title_class]' value='<?php echo esc_html( $option ); ?>'>
		<?php

	}

	public function smug_text_link_class_render() {

		$option = $this->get_option( 'link_class' );
		?>
		<input class="widefat" type='text' name='smug_settings[link_class]' width="300" value='<?php echo esc_html( $option ); ?>'>
		<?php

	}

	public function smug_text_smug_link_icon_render() {

		$option = $this->get_option( 'smug_link_icon' );
		?>
		<input class="widefat" type='text' name='smug_settings[smug_link_icon]' width="300" value='<?php echo esc_html( $option ); ?>'>
		<?php

	}


	public function smug_settings_section_callback() {
		echo __( 'Settings for the default output', 'simple_smugmug' );

	}

	public function smug_styles_section_callback() {

		echo __( 'Use the default included stylesheet or add your own classes here', 'simple_smugmug' );

	}

	public function smug_options_page() {
		if ( isset( $_GET['tab'] ) ) {
			$active_tab = sanitize_text_field( wp_unslash( $_GET['tab'] ) );
		} else {
			// Set settings_tab tab as a default tab.
			$active_tab = 'settings_tab';
		}

		?>
		<form action='options.php' method='post' style="max-width: 800px;">

			<h2>Simple Smugmug</h2>
			<h2 class="nav-tab-wrapper">
				<a href="<?php get_admin_url(); ?>options-general.php?page=simple_smugmug&tab=settings_tab" class="nav-tab <?php echo 'settings_tab' === $active_tab ? 'nav-tab-active' : ''; ?>">Settings	</a>
				<a href="<?php get_admin_url(); ?>options-general.php?page=simple_smugmug&tab=usage_tab" class="nav-tab <?php echo 'usage_tab' === $active_tab ? 'nav-tab-active' : ''; ?>">Usage</a>
			</h2>
			<?php

			if ( 'settings_tab' === $active_tab ) {
				settings_fields( 'pluginPage' );
				do_settings_sections( 'pluginPage' );
				submit_button();
			} else {
			?>

			<h3>Usage:</h3> 
			<h4>There are 2 ways to use this plugin:</h4>
			<p><strong>1)</strong> Use shortcode <code>[simple_smugmug]</code> to display the <code>[album_count]</code> most recent albums with the below settings. This is intended to be used as a widget in e.g a sidebar.</p>
			<p><strong>2)</strong> Display (up to 10) single galleries in a post by passing its album key into the shortcode:</p>
			<p>Automatically: create a post and press the Add Smugmug Gallery button (It should be next to Add Media). This will Fetch the last 50 galleries. Select the gallery from the dropdown.</p>
			<p>Manually: The album key can be found by inspecting the element of the gallery on the page that lists the galleries, and looking for the attribute <code>data-clientid</code>. It will be in this format: <code>/api/v2/album/xxxxxx</code>. The album key is the xxxxxx part. (An easier way is if you're logged in, press the replace image button then look for AlbumKey in the url).</p>
			<ul>
				<li>The 'feed' version (i.e shortcode used without a gallery_id) will be cached in localstorage (album uris, captions, titles, image urls etc). The idea is that if you have it in a sidebar, as someone navigates your site, they won't have to do the api requests on every page. You can set the cache time to 0 if you don't want this behaviour.  </li>
				<li>You can add multiple shortcodes to a post.</li>
				<li>No more than 100 images will be displayed per gallery. This is the pagination limit of the Smugmug API. </li>
				<li>The loading spinner can be overridden with html passed to the <code>'simple_smugmug_loader'</code> hook.
					<br/>
					<pre>
						<code style="display:block;">
	// your theme's functions.php
	function my_loader() {
		return "loading";
	}
	add_filter( "simple_smugmug_loader", "my_loader" );
						</code>
					</pre>
				</li>
				<li>The displaying of the media button in the wp post editor can also be filtered with <code>'simple_smugmug_media_button'</code>:
					<br/>
					<pre>
						<code style="display:block;">
	// your theme's functions.php
	function simple_smugmug_button($request){
		//only show the button when editing post in the galleries category
		if (in_category('galleries') ) {
			return $request;
		}
	}
	add_filter("simple_smugmug_media_button", "simple_smugmug_button");
						</code>
					</pre>
				</li>
				<li>
				Shortcode attributes:
					<pre>

	'image_count' | number
	'display_in_lightgallery' | 0 (false) or 1 (true)
	'show_gallery_buy_link' | 0 (false) or 1 (true)
	'show_album_title' | 0 (false) or 1 (true)
	'album_container_class' | string
	'first_image_container_class' | string
	'image_container_class' | string
	'image_class' | string
	'title_class' | string
	'link_class' | string
	'smug_link_icon' | string

					</pre>
				</li>
			</ul>
			<?php
			}
			?>

	</form>
		<?php

	}
}
	new Simple_Smugmug_Admin();
