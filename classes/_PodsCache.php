<?php
class PodCache
{
    var $debug = false;
    var $cache_enabled = true;
    var $form_count = 0;
    var $results;

    /**
     * PodCache singleton
     *
     * @since 1.8.3
     */
    public static function instance()
    {
        static $instance = null;
        if (null == $instance)
        {
            $instance = new PodCache();
        }
        return $instance;
    }
}
