<?php
/*
==================================================
1. Create pods.php in your theme directory
2. Style it to suit your needs

header      <?php get_header(); ?>
content     <?php get_content(); ?>
sidebar     <?php get_sidebar(); ?>
footer      <?php get_footer(); ?>
==================================================
*/
$pods_theme_path = TEMPLATEPATH . '/pods.php';

if (file_exists($pods_theme_path))
{
    include $pods_theme_path;
}
else
{
?>

<?php get_header(); ?>

<?php get_content(); ?>

<?php get_sidebar(); ?>

<?php get_footer(); ?>

<?php
}

