<?php

namespace Tribe\Admin;

/**
 * Admin Settings class.
 *
 * @since 5.0.0
 */

class Settings {

    /**
     * Loaded image field assets if not already loaded.
     *
     * @since 5.0.0
     *
     * @return void
     */
    public function maybe_load_image_field_assets() {
        if ( has_filter( 'tec_admin_load_image_fields_assets', '__return_true' ) ) {
            return;
        }
        add_filter( 'tec_admin_load_image_fields_assets', '__return_true' );
    }

    /**
     * Logic to load image field assets.
     *
     * @since 5.0.0
     *
     * @return bool
     */
    public function should_load_image_field_assets() {
        /**
         * Filters whether or not we should load the image field assets on the settings page.
         *
         * @since 5.0.0
         *
         * @param boolean  $return  True or false whether to load image field assets.
         */
        $load_assets = apply_filters( 'tec_admin_load_image_fields_assets', false );
        if ( $load_assets ) {
            wp_enqueue_media();
        }
        return $load_assets;
    }

    /**
     * Load color field assets if not already loaded.
     *
     * @since 5.0.0
     *
     * @return void
     */
    public function maybe_load_color_field_assets() {
        if ( has_filter( 'tec_admin_load_color_field_assets', '__return_true' ) ) {
            return;
        }
        add_filter( 'tec_admin_load_color_field_assets', '__return_true' );
    }

    /**
     * Logic to load color field assets.
     *
     * @since 5.0.0
     *
     * @return bool
     */
    public function should_load_color_field_assets() {
        /**
         * Filters whether or not we should load the color field assets on the settings page.
         *
         * @since 5.0.0
         *
         * @param boolean  $return  True or false whether to load color field assets.
         */
        $load_assets = apply_filters( 'tec_admin_load_color_field_assets', false );
        return $load_assets;
    }

}