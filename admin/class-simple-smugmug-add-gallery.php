<?php

/**
 *
 * Add a button to wp visual editor media buttons.
 *
 * @link       https://renrizzolo.github.com/wp-simple-smugmug
 * @since      1.0.0
 *
 * @package    Simple_Smugmug
 * @subpackage Simple_Smugmug/admin
 */
class Simple_Smugmug_Add_Gallery {
	/**
	 * Class constructor fires the init function.
	 */
	public function __construct() {
		// Add a media button to our post and page editor.
		$this->init();
	}

	/**
	 * Loads the required js and adds the media button
	 */
	public function init() {
		add_action( 'media_buttons', array( $this, 'insert_media_button' ), 15 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueues the script if creating/editing a post
	 *
	 * @param string $hook_suffix The current admin page.
	 **/
	public function enqueue_scripts( $hook_suffix ) {
		// Only load the script on the post page.
		if ( 'post-new.php' === $hook_suffix || 'post.php' === $hook_suffix ) {
			wp_enqueue_script( 'simple_smugmug_media_button', plugin_dir_url( __FILE__ ) . '/js/simple-smugmug-media-button.js', array( 'jquery' ), '', true );
		}
	}

	/**
	 * Echoes out the html of the media button.
	 */
	public function insert_media_button() {
		ob_start();
			echo '<style>
							.button-secondary.simple-smugmug-insert-gallery {
								margin: 0 10px;
							}
							.simple-smugmug-insert-gallery > span {
									color:#888;
									-webkit-font-smoothing: antialiased;
									-moz-osx-font-smoothing: grayscale;
									display: inline-block;
									width: 18px;
									height: 18px;
									vertical-align: text-bottom;
									margin: 0 2px 0 0;
							}
							.smugmug-id-container {
									display: none;
									padding: 5px;
									background: #f3f3f3;
									margin-bottom: 20px;
							}
							.smugmug-albums{
								margin-bottom: 20px;
							}
							.smugmug-albums select {
								font-size: 13px;
							}
							.smugmug-options-container {
								display: none;
							}
							#smugmug-enter-manually {
								margin-bottom: 20px;
							}
							.smugmug-modal {
								display: none;
								padding: 15px;
								position: absolute;
								transform: translateX(-50%);
								top: 0%;
								left: 50%;
								width: 320px;
								background-color: #ffffff;
								border: 1px solid #333;
								box-shadow: 0pc 0pc 150px 20px rgba(0,0,0,0.6); 
								}
							.smugmug-show {
								display:block;
								z-index:99;
							}
							.smugmug-modal label {
								display: inline-block;
								margin-right: 15px;
								font-size: 12px;
								font-weight: bold;
							}
							.smugmug-options-container > div {
								margin-bottom: 10px;
							}
					</style>';

		echo '<a href="#" id="insert-smugmug-gallery" class="button-secondary simple-smugmug-insert-gallery">
						<span class="dashicons dashicons-images-alt"></span> 
						' . esc_attr( __( 'Add Smugmug Gallery', 'simple_smugmug' ) ) . '
					</a>
			<div id="insert-smugmug-modal" class="smugmug-modal">
				<div id="smugmug-modal-form">';
					$options = get_option( 'smug_settings' );
		echo '<input type="hidden" name="simple_smugmug[api_key]" id="api_key" value="' . esc_attr( $options['api_key'] ) . '"/>
					<input type="hidden" name="simple_smugmug[smugmug_username]" id="smugmug_username" value="' . esc_attr( $options['smugmug_username'] ) . '"/>

					<div class="smugmug-albums">
						<label for="gallery_id">Smugmug Album</label>
						<span class="error"></span>
						<select name="simple_smugmug[gallery_id_select]" id="gallery_id_select"><option id="loading">Loading...</option></select>
					</div>
					<a class="button" href="#" id="smugmug-enter-manually">Enter ID manually</a>
					<div class="smugmug-id-container">
						<label for="gallery_id">Smugmug Album key*</label>
						<input type="text" name="simple_smugmug[gallery_id]" id="gallery_id"/>
					</div>
					<a class="button" href="#" id="smugmug-show-options">Show options</a>

					<div class="smugmug-options-container">
						<p>leave blank to use current plugin settings</p>
					<div>
						<label for="show_album_title">Show title</label>
						<input type="checkbox" name="simple_smugmug[show_album_title]" id="show_album_title" 
							data-current="' . esc_attr( $options['show_album_title'] ) . '" 
							value="' . esc_attr( $options['show_album_title'] ) . '"
							';
							checked( '1', $options['show_album_title'] );
				echo '/>
					</div>

					<div>
						<label for="display_in_lightgallery">Display in lightgallery</label>
						<input type="checkbox" name="simple_smugmug[display_in_lightgallery]" id="display_in_lightgallery" 
							data-current="' . esc_attr( $options['display_in_lightgallery'] ) . '" 
							value="' . esc_attr( $options['display_in_lightgallery'] ) . '" 
							';
							checked( '1', $options['display_in_lightgallery'] );
				echo '/>
					</div>

					<div>
						<label for="show_gallery_buy_link">Show buy link in gallery</label>
						<input type="checkbox" name="simple_smugmug[show_gallery_buy_link]" id="show_gallery_buy_link" 
							data-current="' . esc_attr( $options['show_gallery_buy_link'] ) . '" 
							value="' . esc_attr( $options['show_gallery_buy_link'] ) . '" 
							';
							checked( '1', $options['show_gallery_buy_link'] );
				echo '/>
					</div>

					<div>
						<label for="image_count">Image Count</label>
						<input type="number" name="simple_smugmug[image_count]" id="image_count" 
							data-current="' . esc_attr( $options['image_count'] ) . '" 
							value="' . esc_attr( $options['image_count'] ) . '" 
						/>
					</div>

					<div>
						<label for="album_container_class">Album container class</label>
						<input type="text" name="simple_smugmug[album_container_class]" id="album_container_class" 
							data-current="' . esc_attr( $options['album_container_class'] ) . '" 
							value="' . esc_attr( $options['album_container_class'] ) . '" 
						/>
					</div>

					<div>
						<label for="first_image_container_class">1st image container class</label>
						<input type="text" name="simple_smugmug[first_image_container_class]" id="first_image_container_class" 
							data-current="' . esc_attr( $options['first_image_container_class'] ) . '" 
							value="' . esc_attr( $options['first_image_container_class'] ) . '" 
						/>
					</div>

					<div>
						<label for="image_container_class">Image container class</label>
						<input type="text" name="simple_smugmug[image_container_class]" id="image_container_class" 
							data-current="' . esc_attr( $options['image_container_class'] ) . '" 
							value="' . esc_attr( $options['image_container_class'] ) . '" 
						/>
					</div>

					<div>
						<label for="image_class">Image class</label>
						<input type="text" name="simple_smugmug[image_class]" id="image_class" data-current="' . esc_attr( $options['image_class'] ) . '" value="' . esc_attr( $options['image_class'] ) . '"/>
					</div>

					<div>
						<label for="title_class">Title class</label>
						<input type="text" name="simple_smugmug[title_class]" id="title_class" data-current="' . esc_attr( $options['title_class'] ) . '" value="' . esc_attr( $options['title_class'] ) . '"/>
					</div>

					<div>
						<label for="link_class">Link class</label>
						<input type="text" name="simple_smugmug[link_class]" id="link_class" data-current="' . esc_attr( $options['link_class'] ) . '" value="' . esc_attr( $options['link_class'] ) . '"/>
					</div>

					<div>
						<label for="smug_link_icon">Gallery buy link icon class</label>
						<input type="text" name="simple_smugmug[smug_link_icon]" id="smug_link_icon" data-current="' . esc_attr( $options['smug_link_icon'] ) . '" value="' . esc_attr( $options['smug_link_icon'] ) . '"/>
					</div>
				</div>
				<div>
					<input type="button" class="button-primary" id="smugmug-modal-submit" value="Insert" />
					<a class="button-primary" id="smugmug-modal-cancel">Cancel</a>
				</div>
				</div>
			</div>';

		$request = ob_get_clean();
		$request = apply_filters( 'simple_smugmug_media_button', $request );

		echo $request;

	}

}

new Simple_Smugmug_Add_Gallery();
