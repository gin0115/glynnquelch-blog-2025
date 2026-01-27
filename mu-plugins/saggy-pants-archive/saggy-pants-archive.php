<?php
/**
 * Plugin Name: Saggy Pants Archive
 * Description: Auto-updates jukebox block from audio archive page
 * Version: 1.0.0
 * Author: Glynn Quelch
 */

namespace SaggyPants;

defined('ABSPATH') || exit;

define('SAGGY_PANTS_PATH', __DIR__);
define('SAGGY_PANTS_URL', plugin_dir_url(__FILE__));

// Autoload classes
spl_autoload_register(function ($class) {
    if (strpos($class, 'SaggyPants\\') !== 0) return;
    $file = SAGGY_PANTS_PATH . '/src/' . str_replace('\\', '/', substr($class, 11)) . '.php';
    if (file_exists($file)) require_once $file;
});

// Initialize
add_action('plugins_loaded', function () {
    Settings::init();
    Scheduler::init();
    JukeboxUpdater::init();
});
