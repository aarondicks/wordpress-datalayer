<?php
/**
 * Plugin Name: WordPress DataLayer
 * Plugin URI: http://wordpress.org/plugins/wordpres-datalayer/
 * Description: A simple developer friendly dataLayer drop-in
 * Author: Aaron Dicks
 * Version: 1.0
 * Author URI: https://www.impression.co.uk
 *
 * @package wordpress-datalayer
 * @version 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class WPDataLayer {
  public static $js = [];

	public function __construct() {
    $this->objectName = get_option('wmpdatalayer', 'dataLayer');

    add_action( 'wp_head', array( $this, 'print' ), 0 );


    $this->new_general_setting();
	}

  public function print() {
    $vars = [];

    if (is_singular()) {
      $post = get_queried_object();

      $vars['page_ID'] = intval( $post->ID );
      $vars['page_type'] = $post->post_type;
      $vars['publish_date'] = $post->post_date;
      $vars['page_title'] = $post->post_title;
      $vars['author_ID'] = intval( $post->post_author );

      $categories = [];
      foreach (wp_get_object_terms( $post->ID, 'category' ) as $terms) {
        $categories[] = $terms->name;
      }
      $vars['post_categories'] = $categories;
    }

    if (is_archive()) {
      $vars['pagepost_type'] = "archive";
    }

    if (is_front_page()) {
      $vars['pagepost_type2'] = "frontpage";
    }

    if (is_home()) {
      $vars['pagepost_type2'] = "homepage";
    }

    if (is_search()) {
      $vars['pagepost_type'] = "search";
      $vars['search_terms'] = get_search_query();
    }

    // post/page category names
    // post/page tag names
    // post/page ID
    // post types

    //$current_page = get_queried_object();

    $encoded_vars = json_encode($vars);

    $js = "{$this->objectName}.push({$encoded_vars});";
    array_push(self::$js, $js);
    echo "<script>{$this->objectName}=window.{$this->objectName}||[];".implode("", self::$js)."</script>";
  }

  public function new_general_setting( ) {
    add_filter( 'admin_init', array( &$this , 'register_fields' ) );
  }

  public function register_fields() {
    register_setting('general', 'wmpdatalayer', 'esc_attr' );
    add_settings_field('wmpdatalayer', '<label for="wmpdatalayer">WP Data Later object</label>' , array(&$this, 'fields_html') , 'general' );
  }

  public function fields_html() {
    $value = get_option('wmpdatalayer', 'dataLayer');
    echo '<input type="text" id="wmpdatalayer" name="wmpdatalayer" value="' . $value . '" />';
  }

}

$WPDataLayer = new WPDataLayer();
