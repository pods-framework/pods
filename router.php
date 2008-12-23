<?php
require 'Pod.class.php';

foreach ($_GET as $key => $val)
{
    ${$key} = mysql_real_escape_string($val);
}

/*
==================================================
Modify the code below to match your theme
==================================================
*/
?>

<?php get_header(); ?>

<div id="content">
    <div class="post"><?php eval("?>$phpcode"); ?></div>
</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>

<?php die(); ?>

