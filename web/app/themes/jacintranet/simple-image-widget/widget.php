<?php
/**
 * Widget template for use on homepage sidebar.
 */
?>

<?php if ( ! empty( $image_id ) ) : ?>
    <?php
    echo $link_open;
    echo wp_get_attachment_image( $image_id, 'sidebar-image' );
    echo $link_close;
    ?>
<?php endif; ?>

<?php
if ( ! empty( $text ) ) :
  echo wpautop( $text );
endif;
?>

<?php if ( ! empty( $link_text ) ) : ?>
    <?php
    echo $text_link_open;
    echo $link_text;
    echo $text_link_close;
    ?>
<?php endif; ?>
