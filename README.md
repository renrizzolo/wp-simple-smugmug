# Simple Smugmug

This is a wordpress plugin to display [Smugmug](https://smugmug.com) galleries using a shortcode.  
It uses the Smugmug REST API.  

**Disclaimer:**  
Galleries are loaded client side via ajax.  
Nothing is saved to your server. 
If you remove a gallery from smugmug in the future, it will no longer show on your wordpress site.  
Your API key will be exposed to the browser. Use at your own risk.

**credits**
Uses the awesome jQuery gallery plugin [lightGallery](https://github.com/sachinchoolur/lightGallery)

## screenshots

![editor add gallery](https://github.com/renrizzolo/wp-simgple-smugmug/blob/master/screenshots/simple-smugmug-editor.png
![settings](https://github.com/renrizzolo/wp-simgple-smugmug/blob/master/screenshots/simple-smugmug-settings.png)

## Usage:

**First:** go to wp dashboard > settings > Simple Smugmug.  
Set your Smugmug API key and smugmug username.  (see: [here](https://api.smugmug.com/api/developer/apply) if you don't have an API key).

There are 2 ways to use this plugin:
1) **Display the ``[album_count]`` most recent albums** with the defined settings. Simply (heh) use the shortcode ``[simple_smugmug]``. This is intended to be used as a widget in e.g a sidebar.  

2) **Display up to 10 single galleries** in a post by passing its album key into the shortcode: ``[simple_smugmug gallery_id="Pz5sF8"]``  
	* **Automatic:** create a post and press the Add Smugmug Gallery button (It should be next to Add Media). This will Fetch the last 50 galleries. Select the gallery from the dropdown.

	* **Manual:** The album key can be found by inspecting the element of the gallery on the page that lists the galleries, and looking for the attribute ``data-clientid``. It will be in this format: ``/api/v2/album/xxxxxx``. The album key is the xxxxxx part. Another way is if you're logged in to smugmug, press the replace image button on a gallery photo then look for AlbumKey in the url.


The 'feed' version (i.e shortcode used without a gallery_id) will be cached in localstorage (album uris, captions, titles, image urls etc). The idea is that if you have it in a sidebar, as someone navigates your site, they won't have to do the api requests on every page. You can set the cache time to 0 if you don't want this behaviour.  

You can add multiple shortcodes to a post.  

No more than 100 images will be displayed per gallery. This is the pagination limit of the Smugmug API.  

#### Loading spinner output

The loading spinner can be overridden with html passed to the ``'simple_smugmug_loader'`` hook.  

```
// your theme's functions.php
function my_loader() {
  return "loading";
}
add_filter( "simple_smugmug_loader", "my_loader" );
```
  
#### Media button output
The displaying of the media button in the wp post editor can also be filtered with ``'simple_smugmug_media_button'``:
				
```
// your theme's functions.php
function simple_smugmug_button($request){
 //only show the button when editing post in the galleries category
  if (in_category('galleries') ) {
    return $request;
  }
}
add_filter("simple_smugmug_media_button", "simple_smugmug_button");
```
  
#### Shortcode attributes:  

| attribute | value |
|------------|--------|
image_count | number|
display_in_lightgallery | 0 (false) or 1 (true)|
show_gallery_buy_link | 0 (false) or 1 (true)|
show_album_title | 0 (false) or 1 (true)|
album_container_class | string|
first_image_container_class | string|
image_container_class | string|
image_class | string|
title_class | string|
link_class | string|
smug_link_icon | string|

	
## Issues

Please feel free to submit an issue for ideas / problems / bugs.
