<?php get_header(); ?>

<?php global $post; ?>

<div id="content">

<h2><?php echo $post->post_title; ?></h2>

<div class="main">
    <?php echo $post->post_content; ?>
</div>

<?php
require 'Pod.class.php';

foreach ($_GET as $key => $val)
{
    ${$key} = mysql_real_escape_string($val);
}

include $tpl_path;
?>

<div class="meta group">
    <p><span class="edit"><?php edit_post_link('Edit'); ?></span></p>
</div>

</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>

<?php die(); ?>

