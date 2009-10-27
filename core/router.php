<?php
/*
==================================================
1. Create pods.php in your theme directory
2. Style it to suit your needs

header      <?php get_header(); ?>
content     <?php pods_content(); ?>
sidebar     <?php get_sidebar(); ?>
footer      <?php get_footer(); ?>
==================================================
*/
$pods_theme_path = STYLESHEETPATH . '/pods.php';

if (!empty($page_template) && file_exists(STYLESHEETPATH . '/' . $page_template))
{
    include STYLESHEETPATH . '/' . $page_template;
}
elseif (file_exists($pods_theme_path))
{
    include $pods_theme_path;
}
else
{
    get_header();
    pods_content();
    get_sidebar();
    get_footer();
}
