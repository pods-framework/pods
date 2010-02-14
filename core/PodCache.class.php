<?php
/*
==================================================
PodCache.class.php

http://pods.uproot.us/codex/
==================================================
*/
class PodCache
{
    var $types;
    var $fields;
    var $helpers;
    var $pod_pages;
    var $templates;

    public static function Instance()
    {
        static $instance = null;
        if (null == $instance)
        {
            $instance = new PodCache();
        }
        return $instance;
    }

    private function __construct()
    {
        // Silence is golden.
    }
}
