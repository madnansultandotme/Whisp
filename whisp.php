<?php
/**
 * Plugin Name: Whisp - Professional Chat Widget
 * Plugin URI: https://your-domain.com/whisp
 * Description: A comprehensive WordPress chat widget plugin for lead generation and customer communication with progressive form capture and integrated dashboard management.
 * Version: 1.0.0
 * Author: Your Team
 * Author URI: https://your-domain.com
 * Text Domain: whisp
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Whisp
 * @version 1.0.0
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WHISP_VERSION', '1.0.0');
define('WHISP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WHISP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WHISP_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Whisp Plugin Class
 */
class Whisp_Plugin {
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize plugin hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'render_chat_widget'));
        
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load includes
        $this->load_includes();
        
        // Initialize chat leads system
        if (class_exists('Whisp_Chat_Leads')) {
            new Whisp_Chat_Leads();
        }
    }
    
    /**
     * Load plugin includes
     */
    private function load_includes() {
        require_once WHISP_PLUGIN_DIR . 'includes/chat-leads.php';
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        // Enqueue CSS
        wp_enqueue_style(
            'whisp-chat-widget',
            WHISP_PLUGIN_URL . 'assets/css/chat-widget.css',
            array(),
            WHISP_VERSION
        );
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'whisp-chat-widget',
            WHISP_PLUGIN_URL . 'assets/js/chat-widget.js',
            array('jquery'),
            WHISP_VERSION,
            true
        );
        
        // Localize script for AJAX
        wp_localize_script('whisp-chat-widget', 'whispAjax', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('whisp_chat_nonce'),
            'leadNonce' => wp_create_nonce('whisp_lead_nonce'),
            'messageNonce' => wp_create_nonce('whisp_message_nonce')
        ));
    }
    
    /**
     * Render chat widget in footer
     */
    public function render_chat_widget() {
        include WHISP_PLUGIN_DIR . 'template-parts/chat-widget.php';
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Whisp Chat',
            'Whisp Chat',
            'manage_options',
            'whisp-dashboard',
            array($this, 'admin_dashboard'),
            'dashicons-format-chat',
            30
        );
    }
    
    /**
     * Admin dashboard page
     */
    public function admin_dashboard() {
        echo '<div class="wrap">';
        echo '<h1>Whisp Chat Dashboard</h1>';
        echo '<p>Welcome to Whisp - Professional Chat Widget Plugin</p>';
        echo '</div>';
    }
    
    /**
     * Enqueue admin scripts
     */
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'whisp') !== false) {
            // Add admin styles if needed
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        $this->create_tables();
        
        // Set default options
        $this->set_default_options();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Cleanup temporary data if needed
    }
    
    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'whisp_chat_leads';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(50) NOT NULL,
            country varchar(100) NOT NULL,
            initial_message text,
            chat_messages longtext,
            status varchar(50) DEFAULT 'new',
            assigned_to bigint(20) DEFAULT NULL,
            ip_address varchar(45),
            user_agent text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY email (email),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Set default plugin options
     */
    private function set_default_options() {
        $defaults = array(
            'whisp_enabled' => true,
            'whisp_position' => 'bottom-right',
            'whisp_color' => '#24E3B3',
            'whisp_welcome_message' => 'Welcome to our chat! How can we help you today?'
        );
        
        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }
}

// Initialize the plugin
add_action('plugins_loaded', array('Whisp_Plugin', 'get_instance'));