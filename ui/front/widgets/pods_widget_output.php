<?php echo $before_widget; ?>
<?php if ( !empty( $title ) ): ?>
<?php echo $before_title . $title . $after_title; ?>
<?php endif; ?>

<?php echo do_shortcode( $shortcode ); ?>

<?php echo $after_widget; ?>
