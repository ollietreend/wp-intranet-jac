<?php

namespace JACIntranet\Theme\Widgets;

/**
 * Register custom widgets
 */
function widgets_init() {
  register_widget(__NAMESPACE__ . '\\Widget_Google_Search');
}
add_action('widgets_init', __NAMESPACE__ . '\\widgets_init');

/**
 * Search widget class
 *
 * @since 2.8.0
 */
class Widget_Google_Search extends \WP_Widget {

  public function __construct() {
    $widget_ops = array('classname' => 'widget_google_search', 'description' => __( "A Google search form.") );
    parent::__construct( 'google-search', 'Google Search', $widget_ops );
  }

  /**
   * @param array $args
   * @param array $instance
   */
  public function widget( $args, $instance ) {
    /** This filter is documented in wp-includes/default-widgets.php */
    $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

    echo $args['before_widget'];
    if ( $title ) {
      echo $args['before_title'] . $title . $args['after_title'];
    }

    locate_template('/templates/googlesearchform.php', true, false);

    echo $args['after_widget'];
  }

  /**
   * @param array $instance
   */
  public function form( $instance ) {
    $instance = wp_parse_args( (array) $instance, array( 'title' => '') );
    $title = $instance['title'];
    ?>
    <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
    <?php
  }

  /**
   * @param array $new_instance
   * @param array $old_instance
   * @return array
   */
  public function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $new_instance = wp_parse_args((array) $new_instance, array( 'title' => ''));
    $instance['title'] = strip_tags($new_instance['title']);
    return $instance;
  }

}
