<?php

namespace JACIntranet\Theme\Nav;

use Roots\Soil\Utils;

/**
 * Custom nav walker for use in left sidebar inside "LeftNav" element.
 * Adapted from Soil's NavWalker class.
 */
class LeftNavWalker extends \Walker_Nav_Menu {
  private $cpt; // Boolean, is current post a custom post type
  private $archive; // Stores the archive page for current URL

  private $topLvlCount = 0;

  public function __construct() {
    add_filter('nav_menu_css_class', array($this, 'cssClasses'), 10, 2);
    add_filter('nav_menu_item_id', '__return_null');
    $cpt           = get_post_type();
    $this->cpt     = in_array($cpt, get_post_types(array('_builtin' => false)));
    $this->archive = get_post_type_archive_link($cpt);
  }

  public function checkCurrent($classes) {
    return preg_match('/(current[-_])|active/', $classes);
  }

  // @codingStandardsIgnoreStart
  public function display_element($element, &$children_elements, $max_depth, $depth = 0, $args, &$output) {
    $element->is_subitem = ((!empty($children_elements[$element->ID]) && (($depth + 1) < $max_depth || ($max_depth === 0))));

    if ($element->is_subitem) {
      foreach ($children_elements[$element->ID] as $child) {
        if ($child->current_item_parent || Utils\url_compare($this->archive, $child->url)) {
          $element->classes[] = 'active';
        }
      }
    }

    $element->is_active = strpos($this->archive, $element->url);

    if ($element->is_active) {
      $element->classes[] = 'active';
    }

    parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
  }
  // @codingStandardsIgnoreEnd

  public function cssClasses($classes, $item) {
    $slug = sanitize_title($item->title);

    if ($this->cpt) {
      $classes = str_replace('current_page_parent', '', $classes);

      if (Utils\url_compare($this->archive, $item->url)) {
        $classes[] = 'active';
      }
    }

    $classes = preg_replace('/(current(-menu-|[-_]page[-_])(item|parent|ancestor))/', 'active', $classes);
    $classes = preg_replace('/^((menu|page)[-_\w+]+)+/', '', $classes);

    $classes[] = 'menu-' . $slug;

    $classes = array_unique($classes);

    return array_filter($classes, function ($element) {
      $element = trim($element);
      return !empty($element);
    });
  }

  /**
   * Start the element output.
   *
   * @see Walker::start_el()
   *
   * @since 3.0.0
   *
   * @param string $output Passed by reference. Used to append additional content.
   * @param object $item   Menu item data object.
   * @param int    $depth  Depth of menu item. Used for padding.
   * @param array  $args   An array of arguments. @see wp_nav_menu()
   * @param int    $id     Current item ID.
   */
  public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
    $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

    $classes = empty( $item->classes ) ? array() : (array) $item->classes;
    $classes[] = 'menu-item-' . $item->ID;

    $is_heading = ( $item->url == '#' && $depth === 0 );

    /**
     * Filter the CSS class(es) applied to a menu item's list item element.
     *
     * @since 3.0.0
     * @since 4.1.0 The `$depth` parameter was added.
     *
     * @param array  $classes The CSS classes that are applied to the menu item's `<li>` element.
     * @param object $item    The current menu item.
     * @param array  $args    An array of {@see wp_nav_menu()} arguments.
     * @param int    $depth   Depth of menu item. Used for padding.
     */
    $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
    $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

    /**
     * Filter the ID applied to a menu item's list item element.
     *
     * @since 3.0.1
     * @since 4.1.0 The `$depth` parameter was added.
     *
     * @param string $menu_id The ID that is applied to the menu item's `<li>` element.
     * @param object $item    The current menu item.
     * @param array  $args    An array of {@see wp_nav_menu()} arguments.
     * @param int    $depth   Depth of menu item. Used for padding.
     */
    $id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args, $depth );
    $id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

    if (!$is_heading) {
      $output .= $indent . '<li' . $id . $class_names .'>';
    }

    $atts = array();
    $atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
    $atts['target'] = ! empty( $item->target )     ? $item->target     : '';
    $atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
    $atts['href']   = ! empty( $item->url )        ? $item->url        : '';

    /**
     * Filter the HTML attributes applied to a menu item's anchor element.
     *
     * @since 3.6.0
     * @since 4.1.0 The `$depth` parameter was added.
     *
     * @param array $atts {
     *     The HTML attributes applied to the menu item's `<a>` element, empty strings are ignored.
     *
     *     @type string $title  Title attribute.
     *     @type string $target Target attribute.
     *     @type string $rel    The rel attribute.
     *     @type string $href   The href attribute.
     * }
     * @param object $item  The current menu item.
     * @param array  $args  An array of {@see wp_nav_menu()} arguments.
     * @param int    $depth Depth of menu item. Used for padding.
     */
    $atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

    $li_class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
    if ($this->checkCurrent($li_class_names)) {
      $atts['class'] = 'active';
    }

    $attributes = '';
    foreach ( $atts as $attr => $value ) {
      if ($is_heading && $attr == 'href') {
        continue;
      }

      if ( ! empty( $value ) ) {
        $value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
        $attributes .= ' ' . $attr . '="' . $value . '"';
      }
    }

    $item_output = $args->before;
    if ($is_heading) {
      $tag = 'h2';
    } else {
      $tag = 'a';
    }
    $item_output .= '<' . $tag . $attributes .'>';

    $link_before = $args->link_before;
    $link_after = $args->link_after;
    if ($depth === 1) {
      $link_before .= '<span class="leftnavArrow">';
      $link_after .= '</span>';
    }

    /** This filter is documented in wp-includes/post-template.php */
    $item_output .= $link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $link_after;
    $item_output .= '</' . $tag . '>';
    $item_output .= $args->after;

    /**
     * Filter a menu item's starting output.
     *
     * The menu item's starting output only includes `$args->before`, the opening `<a>`,
     * the menu item's title, the closing `</a>`, and `$args->after`. Currently, there is
     * no filter for modifying the opening and closing `<li>` for a menu item.
     *
     * @since 3.0.0
     *
     * @param string $item_output The menu item's starting HTML output.
     * @param object $item        Menu item data object.
     * @param int    $depth       Depth of menu item. Used for padding.
     * @param array  $args        An array of {@see wp_nav_menu()} arguments.
     */
    $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
  }

  /**
   * Ends the element output, if needed.
   *
   * @see Walker::end_el()
   *
   * @since 3.0.0
   *
   * @param string $output Passed by reference. Used to append additional content.
   * @param object $item   Page data object. Not used.
   * @param int    $depth  Depth of page. Not Used.
   * @param array  $args   An array of arguments. @see wp_nav_menu()
   */
  public function end_el( &$output, $item, $depth = 0, $args = array() ) {
    $is_heading = ( $item->url == '#' && $depth === 0 );

    if (!$is_heading) {
      $output .= "</li>\n";
    }
  }

  /**
   * Starts the list before the elements are added.
   *
   * @see Walker::start_lvl()
   *
   * @since 3.0.0
   *
   * @param string $output Passed by reference. Used to append additional content.
   * @param int    $depth  Depth of menu item. Used for padding.
   * @param array  $args   An array of arguments. @see wp_nav_menu()
   */
  public function start_lvl( &$output, $depth = 0, $args = array() ) {
    $indent = str_repeat("\t", $depth);

    $listIDs = [
      0 => 'navitem2',
      1 => 'navitem4',
      2 => 'navitem5',
    ];

    // Assign ul id attribute if its index exists in the $listIDs array
    if ($depth === 0 && array_key_exists($this->topLvlCount, $listIDs)) {
      $attrID = $listIDs[$this->topLvlCount];
      $output .= "\n$indent<ul id=\"" . $attrID . "\">\n";

      $this->topLvlCount++;
    } else {
      $output .= "\n$indent<ul>\n";
    }
  }
}
