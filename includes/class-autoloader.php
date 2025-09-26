<?php
/**
 * Autoloader for WP Dynamic Survey Plugin
 *
 * @package WP_Dynamic_Survey
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Autoloader class for the plugin
 */
class WP_Dynamic_Survey_Autoloader {

    /**
     * File extension as a string. Defaults to ".php".
     */
    protected static $file_extension = '.php';

    /**
     * The top-level directory where auto-loaded files are located.
     */
    protected static $directory = null;

    /**
     * A log of files that have been loaded by the autoloader.
     */
    protected static $loaded_files = array();

    /**
     * Registers the autoloader with SPL.
     */
    public static function register() {
        self::$directory = WP_DYNAMIC_SURVEY_PATH;
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    /**
     * Loads a class file based on the class name.
     *
     * @param string $class_name The name of the class to load.
     * @return bool True if the file was loaded, false otherwise.
     */
    public static function autoload($class_name) {
        // Check if this is a plugin class
        if (strpos($class_name, 'WP_Dynamic_Survey') !== 0) {
            return false;
        }

        // Convert class name to file path
        $file_path = self::get_file_path_from_class($class_name);

        if ($file_path && file_exists($file_path)) {
            require_once $file_path;
            self::$loaded_files[] = $file_path;
            return true;
        }

        return false;
    }

    /**
     * Converts a class name to a file path.
     *
     * @param string $class_name The class name to convert.
     * @return string|false The file path or false if not found.
     */
    protected static function get_file_path_from_class($class_name) {
        // Remove WP_Dynamic_Survey prefix
        $class_name = str_replace('WP_Dynamic_Survey_', '', $class_name);

        // Convert to lowercase and replace underscores with hyphens
        $file_name = 'class-' . strtolower(str_replace('_', '-', $class_name)) . self::$file_extension;

        // Define possible directories to search
        $directories = array(
            'includes/',
            'admin/',
            'public/',
            'includes/database/',
            'includes/api/',
        );

        // Search for the file in each directory
        foreach ($directories as $directory) {
            $file_path = self::$directory . $directory . $file_name;
            if (file_exists($file_path)) {
                return $file_path;
            }
        }

        return false;
    }

    /**
     * Gets the list of files that have been loaded by the autoloader.
     *
     * @return array Array of loaded file paths.
     */
    public static function get_loaded_files() {
        return self::$loaded_files;
    }

    /**
     * Manually loads a specific class file.
     *
     * @param string $class_name The class name to load.
     * @return bool True if loaded successfully, false otherwise.
     */
    public static function load_class($class_name) {
        return self::autoload($class_name);
    }

    /**
     * Checks if a class has been loaded by the autoloader.
     *
     * @param string $class_name The class name to check.
     * @return bool True if the class has been loaded, false otherwise.
     */
    public static function is_class_loaded($class_name) {
        $file_path = self::get_file_path_from_class($class_name);
        return $file_path && in_array($file_path, self::$loaded_files);
    }

    /**
     * Loads all core classes at once (for performance in some cases).
     */
    public static function load_core_classes() {
        $core_classes = array(
            'WP_Dynamic_Survey_DB_Migrator',
            'WP_Dynamic_Survey_Manager',
            'WP_Dynamic_Survey_Participant_Manager',
            'WP_Dynamic_Survey_Question_Flow_Handler',
            'WP_Dynamic_Survey_Session_Manager',
            'WP_Dynamic_Survey_Security',
            'WP_Dynamic_Survey_Admin',
            'WP_Dynamic_Survey_Frontend',
            'WP_Dynamic_Survey_Shortcode',
            'WP_Dynamic_Survey_Assets',
        );

        foreach ($core_classes as $class_name) {
            self::autoload($class_name);
        }
    }

    /**
     * Gets statistics about loaded files.
     *
     * @return array Statistics array.
     */
    public static function get_stats() {
        return array(
            'loaded_files_count' => count(self::$loaded_files),
            'loaded_files' => self::$loaded_files,
            'plugin_directory' => self::$directory,
            'file_extension' => self::$file_extension
        );
    }
}

// Register the autoloader immediately
WP_Dynamic_Survey_Autoloader::register();