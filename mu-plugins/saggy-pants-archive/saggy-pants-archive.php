<?php
/**
 * Plugin Name: Saggy Pants Archive
 * Description: Archive post type, term images, and jukebox auto-updater
 * Version: 1.0.0
 * Author: Glynn Quelch
 */

namespace SaggyPants;

defined('ABSPATH') || exit;

define('SAGGY_PANTS_PATH', __DIR__);
define('SAGGY_PANTS_URL', plugin_dir_url(__FILE__));
define('SAGGY_PANTS_ARCHIVE_DIR_URL', SAGGY_PANTS_URL); // Alias for legacy class

// Autoload namespaced classes
spl_autoload_register(function ($class) {
    if (strpos($class, 'SaggyPants\\') !== 0) return;
    $file = SAGGY_PANTS_PATH . '/src/' . str_replace('\\', '/', substr($class, 11)) . '.php';
    if (file_exists($file)) require_once $file;
});

// Load non-namespaced classes
require_once SAGGY_PANTS_PATH . '/src/class-archive-type-image.php';

// Initialize
add_action('plugins_loaded', function () {
    // Jukebox updater
    Settings::init();
    Scheduler::init();
    JukeboxUpdater::init();
    
    // Archive type term images
    \Archive_Type_Image::init();
});
