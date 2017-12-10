<?php

/* Simple Smugmug admin settings */

class simple_smugmug_admin {

   public function __construct() {
    if( is_admin() ) {
			add_action( 'admin_menu', array($this, 'smug_add_admin_menu') );
			add_action( 'admin_init', array($this, 'smug_settings_init') );
		
		}
	}
	protected function defaults() {
		return array(
          'cache_expiry' =>  60 * 60 * 6,
          'album_count' => 3,
  				'image_count' => 8,
  				'force_https' => 0,
  				'display_in_lightgallery' => 1,
  				'show_gallery_buy_link' => 1,
  				'show_album_title' => 1,
  				'api_key' => 'your api key',
  				'smugmug_username' => 'your smugmug username',
  				'album_container_class' => 'simple-smugmug-widget',
  				'first_image_container_class' => 'simple-smugmug-col-3',
  				'image_container_class' => 'simple-smugmug-col-3',
  				'image_class' => 'simple-smugmug-img',
  				'title_class' => 'simple-smugmug-heading',
  				'link_class' => 'simple-smugmug-link',
  				'smug_link_icon' => 'lg-cart lg-icon',

        );
  }
	

  public function get_default($key) {
		$defaults = $this->defaults();
		return $defaults[$key];
	}

	public function get_option($key) {
		$options = get_option( 'smug_settings' );

		if ( isset( $options[$key] ) && $options[$key] != '') {
			return $options[$key];
		} else {
			return $this->defaults()[$key];
		}
	}

	public function smug_reset_defaults( ) {

		$options = get_option( 'smug_settings' );
		  foreach( $options as $key => $value ) {
		  	//don't reset api key and username
			  if ($key !== 'api_key' && $key !== 'smugmug_username') {
			  	$options[$key] = $this->get_default($key);
			  }
      }
      update_option( 'smug_settings', $options );
	}

	public function init_options( ) {
		$options = $this->defaults();
    add_option( 'smug_settings', $options );
	}


	public function smug_add_admin_menu(  ) { 

		add_options_page( 'Simple Smugmug', 'Simple Smugmug', 'manage_options', 'simple_smugmug', array($this,'smug_options_page') );

	}

 public function smug_settings_validate( $option ) {
    // // Create our array for storing the validated options
     $output = array();
     
    // // Loop through each of the incoming options
    foreach( $option as $key => $value ) {
         
         // Check to see if the current option has a value. If so, process it.
         if( isset( $option[$key] ) ) {
         
             // Strip all HTML and PHP tags and properly handle quoted strings
             $output[$key] = strip_tags( stripslashes( $option[ $key ] ) );

         } // end if
         
     } // end foreach
     
    // // Return the array processing any additional functions filtered by this action
    // return apply_filters( 'smug_settings_validate', $output, $input );
 // $option = sanitize_text_field( $option );
  return $output;
}


	public function smug_settings_init(  ) { 

		$this->init_options();
		add_action( 'wp_ajax_smug_reset_defaults', array( $this, 'smug_reset_defaults' ) );

		register_setting( 
			'pluginPage', 
			'smug_settings',
			array($this, 'smug_settings_validate')
		//	array('sanitize_callback' => 'smug_settings_validate')
		);

		add_settings_section(
			'smug_pluginPage_section', 
			__( 'Settings', 'wordpress' ), 
			array($this, 'smug_settings_section_callback'), 
			'pluginPage'
		);

		add_settings_section(
			'smug_styles_section', 
			__( 'Styles', 'wordpress' ), 
			array($this, 'smug_styles_section_callback'), 
			'pluginPage'
		);

		add_settings_field( 
			'api_key', 
			__( 'Smugmug API key', 'wordpress' ), 
			array($this, 'smug_text_field_api_key_render'), 
			'pluginPage', 
			'smug_pluginPage_section' 
		);

		add_settings_field( 
			'smugmug_username', 
			__( 'Smugmug username', 'wordpress' ), 
			array($this, 'smug_text_field_smugmug_username_render'), 
			'pluginPage', 
			'smug_pluginPage_section' 
		);
		add_settings_field( 
			'show_album_title', 
			__( 'Show album title/links', 'wordpress' ), 
			array($this, 'smug_text_field_show_album_title_render'), 
			'pluginPage', 
			'smug_pluginPage_section' 
		);

		add_settings_field( 
			'force_https', 
			__( 'Force https in album links', 'wordpress' ), 
			array($this, 'smug_text_field_force_https_render'), 
			'pluginPage', 
			'smug_pluginPage_section' 
		);

		add_settings_field( 
			'display_in_lightgallery', 
			__( 'Open thumbs in lightGallery instead of linking directly to smugmug album', 'wordpress' ), 
			array($this, 'smug_text_field_display_in_lightgallery_render'), 
			'pluginPage', 
			'smug_pluginPage_section' 
		);

		add_settings_field( 
			'show_gallery_buy_link', 
			__( 'If using lightgallery, show a buy link for each image', 'wordpress' ), 
			array($this, 'smug_text_field_show_gallery_buy_link_render'), 
			'pluginPage', 
			'smug_pluginPage_section' 
		);

		add_settings_field( 
			'cache_expiry', 
			__( 'Cache expiry time in seconds', 'wordpress' ), 
			array($this, 'smug_text_field_cache_expiry_render'), 
			'pluginPage', 
			'smug_pluginPage_section' 
		);

		add_settings_field( 
			'album_count', 
			__( 'number of albums to show', 'wordpress' ), 
			array($this, 'smug_text_album_count_render'), 
			'pluginPage', 
			'smug_pluginPage_section' 
		);

		add_settings_field( 
			'image_count', 
			__( 'Number of images per album to show (max 100)', 'wordpress' ), 
			array($this, 'smug_text_image_count_render'), 
			'pluginPage', 
			'smug_pluginPage_section' 
		);

		add_settings_field( 
			'album_container_class', 
			__( 'Album container class', 'wordpress' ), 
			array($this, 'smug_text_album_container_class_render'), 
			'pluginPage', 
			'smug_styles_section' 
		);

		add_settings_field( 
			'first_image_container_class', 
			__( 'First image container class', 'wordpress' ), 
			array($this, 'smug_text_first_image_container_class_render'), 
			'pluginPage', 
			'smug_styles_section' 
		);

		add_settings_field( 
			'image_container_class', 
			__( 'Image container class', 'wordpress' ), 
			array($this, 'smug_text_image_container_class_render'), 
			'pluginPage', 
			'smug_styles_section' 
		);

		add_settings_field( 
			'image_class', 
			__( 'Image class', 'wordpress' ), 
			array($this, 'smug_text_image_class_render'), 
			'pluginPage', 
			'smug_styles_section' 
		);

		add_settings_field( 
			'title_class', 
			__( 'Album title class', 'wordpress' ), 
			array($this, 'smug_text_title_class_render'), 
			'pluginPage', 
			'smug_styles_section' 
		);

		add_settings_field( 
			'link_class', 
			__( 'Album title link class', 'wordpress' ), 
			array($this, 'smug_text_link_class_render'), 
			'pluginPage', 
			'smug_styles_section' 
		);

		add_settings_field( 
			'smug_link_icon', 
			__( 'Gallery buy link icon class', 'wordpress' ), 
			array($this, 'smug_text_smug_link_icon_render'), 
			'pluginPage', 
			'smug_styles_section' 
		);

		add_settings_field( 
			'reset_defaults_button', 
			__( 'Reset values to their defaults', 'wordpress' ), 
			array($this, 'reset_defaults_button'), 
			'pluginPage', 
			'smug_styles_section' 
		);

	}

	public function reset_defaults_button(  ) {
		 ?>
		 <script>
	    function resetDefaults(event) {
	      event.preventDefault();

	      var data = {
	        'action': 'smug_reset_defaults',
	      };

	      jQuery('#reset_defaults_button').attr('disabled', 'true');

	      jQuery.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', data, function(response) {
	      	console.log(response);
	      jQuery('#reset_defaults_button').text('Done!').attr('disabled', 'true');
	     	window.location.reload();
	      }, 'json');

	    }
    </script>
    <button onClick="resetDefaults(event)" id="reset_defaults_button">Reset</button>
    <?php
	}

	public function smug_text_field_api_key_render(  ) { 
		$default = $this->get_default( 'api_key' );
		$option = $this->get_option( 'api_key' );

		?>
		<input class="widefat" type='text' name='smug_settings[api_key]' value='<?php echo sanitize_text_field($option)  ?>'>
		<?php

	}


	public function smug_text_field_smugmug_username_render(  ) { 
		$default = $this->get_default( 'smugmug_username' );
		$option = $this->get_option( 'smugmug_username' );

		?>
		<input class="widefat" type='text' name='smug_settings[smugmug_username]' value='<?php echo sanitize_text_field($option)  ?>'>
		<?php

	}
	public function smug_text_field_show_album_title_render(  ) {
		$default = $this->get_default( 'show_album_title' );
		$option = get_option( 'smug_settings' );

		?>
		<input type='checkbox' id="smug_settings[show_album_title]" name='smug_settings[show_album_title]' value="1" <?php checked( '1', $option["show_album_title"] ); ?> />
		<?php

	}
	public function smug_text_field_force_https_render(  ) { 
		$default = $this->get_default( 'force_https' );
		$option = get_option( 'smug_settings' );
		?>
		<input type='checkbox' id="smug_settings[force_https]" name='smug_settings[force_https]' value="1" <?php checked( '1', $option["force_https"] ); ?> />
		<?php

	}
	public function smug_text_field_display_in_lightgallery_render(  ) { 
		$default = $this->get_default( 'display_in_lightgallery' );
		$option = get_option( 'smug_settings' );
		?>
		<input type='checkbox' id="smug_settings[display_in_lightgallery]" name='smug_settings[display_in_lightgallery]' value="1" <?php checked( '1', $option["display_in_lightgallery"] ); ?> />
		<?php

	}
	public function smug_text_field_show_gallery_buy_link_render(  ) { 
		$default = $this->get_default( 'show_gallery_buy_link' );
		$option = get_option( 'smug_settings' );
		?>
		<input type='checkbox' id="smug_settings[show_gallery_buy_link]" name='smug_settings[show_gallery_buy_link]' value="1" <?php checked( '1', $option["show_gallery_buy_link"] ); ?> />
		<?php

	}
	public function smug_text_field_cache_expiry_render(  ) { 
		$default = $this->get_default( 'cache_expiry' );
		$option = $this->get_option( 'cache_expiry' );
		?>
		<input type='number' name='smug_settings[cache_expiry]' value='<?php echo sanitize_text_field($option) ?>'>
		<?php

	}


	public function smug_text_album_count_render(  ) { 
		$default = $this->get_default( 'album_count' );
		$option = $this->get_option( 'album_count' );
		?>
		<input type='number' name='smug_settings[album_count]' value='<?php echo sanitize_text_field($option) ?>'>
		<?php

	}


	public function smug_text_image_count_render(  ) { 
		$default = $this->get_default( 'image_count' );
		$option = $this->get_option( 'image_count' );
		?>
		<input type='number' name='smug_settings[image_count]' value='<?php echo sanitize_text_field($option) ?>'>
		<?php

	}


	public function smug_text_album_container_class_render(  ) { 

		$default = $this->get_default( 'album_container_class' );
		$option = $this->get_option( 'album_container_class' );
		?>
		<input class="widefat" type='text' name='smug_settings[album_container_class]' value='<?php echo sanitize_text_field($option) ?>'>
		<?php

	}


	public function smug_text_first_image_container_class_render(  ) { 

		$default = $this->get_default( 'first_image_container_class' );
		$option = $this->get_option( 'first_image_container_class' );
		?>
		<input class="widefat" type='text' name='smug_settings[first_image_container_class]' value='<?php echo sanitize_text_field($option) ?>'>
		<?php

	}

	public function smug_text_image_container_class_render(  ) { 

		$default = $this->get_default( 'image_container_class' );
		$option = $this->get_option( 'image_container_class' );
		?>
		<input class="widefat" type='text' name='smug_settings[image_container_class]' value='<?php echo sanitize_text_field($option) ?>'>
		<?php

	}

	public function smug_text_image_class_render(  ) { 
		$default = $this->get_default( 'image_class' );
		$option = $this->get_option( 'image_class' );
		?>
		<input class="widefat" type='text' name='smug_settings[image_class]' value='<?php echo sanitize_text_field($option) ?>'>
		<?php

	}

	public function smug_text_title_class_render(  ) { 

		$default = $this->get_default( 'title_class' );
		$option = $this->get_option( 'title_class' );
		?>
		<input class="widefat" type='text' name='smug_settings[title_class]' value='<?php echo sanitize_text_field($option) ?>'>
		<?php

	}

	public function smug_text_link_class_render(  ) { 

		$default = $this->get_default( 'link_class' );
		$option = $this->get_option( 'link_class' );
		?>
		<input class="widefat" type='text' name='smug_settings[link_class]' width="300" value='<?php echo sanitize_text_field($option) ?>'>
		<?php

	}

	public function smug_text_smug_link_icon_render(  ) { 

		$default = $this->get_default( 'smug_link_icon' );
		$option = $this->get_option( 'smug_link_icon' );
		?>
		<input class="widefat" type='text' name='smug_settings[smug_link_icon]' width="300" value='<?php echo sanitize_text_field($option) ?>'>
		<?php

	}


	public function smug_settings_section_callback(  ) {

			?>


		<?php

	}


	public function smug_styles_section_callback(  ) { 

		echo __( 'Use the default included stylesheet or add your own classes here', 'wordpress' );

	}

	public function smug_options_page(  ) { 
	if( isset( $_GET[ 'tab' ] ) )
	{
		$active_tab = $_GET[ 'tab' ];
	}else{
	//set settings_tab tab as a default tab.
		$active_tab = 'settings_tab' ;
	}

		?>
		<form action='options.php' method='post' style="max-width: 800px;">

			<h2>Simple Smugmug</h2>
	    <h2 class="nav-tab-wrapper">
    		<a href="<?php get_admin_url(); ?>options-general.php?page=simple_smugmug&tab=settings_tab" class="nav-tab <?php echo $active_tab == 'settings_tab' ? 'nav-tab-active' : ''; ?>">Settings</a>
    		<a href="<?php get_admin_url(); ?>options-general.php?page=simple_smugmug&tab=usage_tab" class="nav-tab <?php echo $active_tab == 'usage_tab' ? 'nav-tab-active' : ''; ?>">Usage</a>
			</h2>
			<?php

			if( $active_tab == 'settings_tab' ) {
      	settings_fields( 'pluginPage' );
				do_settings_sections( 'pluginPage' );
				submit_button();
      } else {
	     ?>

		<h3>Usage:</h3> 
		<h4>There are 2 ways to use this plugin:</h4>
		<p><strong>1)</strong> Use shortcode <code>[simple_smugmug]</code> to display the <code>[album_count]</code> most recent albums with the below settings. This is intended to be used as a widget in e.g a sidebar.</p>
		<p><strong>2)</strong> Display a single gallery in a post by passing its album key into the shortcode:</p>
		<p><code>[simple_smugmug gallery_id="Pz5sF8"]</code></p>
		<p>The album key can be found by inspecting the element of the gallery on the page that lists the galleries, and looking for the attribute <code>data-clientid</code>. It will be in this format: <code>/api/v2/album/xxxxxx</code>. The album key is the xxxxxx part. (An easier way is if you're logged in, press the replace image button then look for AlbumKey in the url).</p>
		<p>This is done automatically if you use the Add Smugmug Gallery button on the WP visual editor.</p>
	<ul>
		<li>Lightgallery only works on single posts that display a single gallery (i.e using <code>gallery_id</code>)</li>
		<li>The 'feed' version (i.e shortcode used without a gallery_id) will be cached in localstorage. The idea is that if you have it in a sidebar, as someone navigates your site, they won't have to do the api requests on every page. You can set the cache time to 0 if you don't want this behaviour.</li>
		<li>You can add multiple shortcodes to a post, but you can't combine the feed version and specific galleries.</li>
		<li>The loading spinner can be overridden with html passed to the <code>'simple_smugmug_loader'</code> hook.
			<br/>
			<pre><code style="display:block;">
// your theme's functions.php
function my_loader() {
	return "loading";
}
add_filter( "simple_smugmug_loader", "my_loader" );
			</code></pre>
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
	new simple_smugmug_admin();

?>