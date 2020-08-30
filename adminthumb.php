<?php
/*
  Plugin Name: Easy Featured Images
  Plugin URI: https://kosvrouvas.com
  Description: A lightweight plugin to add featured images in the "All Posts" page.
  Author: <a href="http://kosvrouvas.com/" target="_blank">Kostas Vrouvas</a>
  Version: 1.2
 */

// Prevent direct access to this file.
if( !defined( 'ABSPATH' ) ) {
        exit( 'You are not allowed to access this file directly.' );
}

// Styles
add_action( 'admin_enqueue_scripts', 'safely_add_stylesheet_to_admin' );

function safely_add_stylesheet_to_admin() {
 wp_enqueue_style( 'prefix-style', plugins_url('stylesheet.css', __FILE__) );
}

// "The Core"
function custom_columns($columns) 
{
  $columns['featured_image'] = 'Featured Image';
  return $columns;
}
add_filter('manage_posts_columns' , 'custom_columns');


function custom_columns_data($column, $post_id)
{
  switch ( $column ) 
  {
    case 'featured_image':
        if ( has_post_thumbnail() ) {
  ?>
  <a href="#" class="afi_upload_featured_image">
    <?php the_post_thumbnail(); ?>
  </a>
  <?php
  } else {
    echo 'No Featured Image, <a href="#" class="afi_upload_featured_image">Set</a>';
  }
    break;
  }
}

add_action( 'admin_enqueue_scripts', 'afi_include_myuploadscript' );
function afi_include_myuploadscript() {
    if ( ! did_action( 'wp_enqueue_media' ) ) {
        wp_enqueue_media();
    }
}

add_action('manage_posts_custom_column', 'custom_columns_data', 10, 2);

add_action('admin_footer', 'afi_featured_js_update');

function afi_featured_js_update() {

  global $current_screen;

  // add this JS function only if we are on all posts page
  if (($current_screen->id != 'edit-post') || ($current_screen->post_type != 'post'))
      return;

      ?>
      
      <script>
      jQuery(function($){

          $('body').on('click', '.afi_upload_featured_image', function(e){
              e.preventDefault();
              var button = $(this),
                custom_uploader = wp.media({
                  title: 'Set featured image',
                  library : { type : 'image' },
                  button: { text: 'Set featured image' },
              }).on('select', function() {
                  var attachment = custom_uploader.state().get('selection').first().toJSON();
                  $(button).html('<img src="' + attachment.url + '" />').next().val(attachment.id).parent().next().show();
              }).open();
          });

          $('body').on('click', '.afi_remove_featured_image', function(){
              $(this).hide().prev().val('-1').prev().html('Set featured Image');
              return false;
          });

          var $wp_inline_edit = inlineEditPost.edit;
          inlineEditPost.edit = function( id ) {
              $wp_inline_edit.apply( this, arguments );
              var $post_id = 0;
              if ( typeof( id ) == 'object' ) { 
                  $post_id = parseInt( this.getId( id ) );
              }

              if ( $post_id > 0 ) {
                  var $edit_row = $( '#edit-' + $post_id ),
                          $post_row = $( '#post-' + $post_id ),
                          $featured_image = $( '.column-featured_image', $post_row ).html(),
                          $featured_image_id = $( '.column-featured_image', $post_row ).find('img').attr('data-id');


                  if( $featured_image_id != -1 ) {

                      $( ':input[name="_thumbnail_id"]', $edit_row ).val( $featured_image_id ); // ID
                      $( '.afi_upload_featured_image', $edit_row ).html( $featured_image ); // image HTML
                      $( '.afi_remove_featured_image', $edit_row ).show(); // the remove link

                  }
              }
      }
  });
  </script>
  
<?php
}

?>