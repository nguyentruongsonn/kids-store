<?php
/**
 * Cưng Store - News & Blog Sidebar Polish Module
 * Harmonizes the News sidebar (sidebar-1) with the Product sidebar (sidebar-woocommerce).
 */

if (!defined('ABSPATH')) {
    exit;
}

class Cung_News_Sidebar {

    public static function init() {
        // Format category count (3) to <span class="count">3</span> for consistent badge styling
        add_filter('wp_list_categories', [__CLASS__, 'filter_category_counts'], 10, 2);
    }

    public static function filter_category_counts($output, $args = []) {
        // Convert " (3)" or "(3)" into ' <span class="count">3</span>'
        $output = preg_replace('/\s*\((\d+)\)/', ' <span class="count">$1</span>', $output);
        return $output;
    }
}

Cung_News_Sidebar::init();
