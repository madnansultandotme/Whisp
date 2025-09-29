<?php
/**
 * Chat Leads Management
 * Handles lead capture, storage, and admin dashboard
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Whisp_Chat_Leads {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
$this->table_name = $wpdb->prefix . 'whisp_chat_leads';
        
        // Hook into WordPress
        add_action('init', array($this, 'init'));
        add_action('wp_ajax_submit_chat_lead', array($this, 'handle_lead_submission'));
        add_action('wp_ajax_nopriv_submit_chat_lead', array($this, 'handle_lead_submission'));
        add_action('wp_ajax_send_chat_message', array($this, 'handle_chat_message'));
        add_action('wp_ajax_nopriv_send_chat_message', array($this, 'handle_chat_message'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
    }
    
    public function init() {
        // Create database table if it doesn't exist
        $this->create_tables();
    }
    
    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Chat Leads table
        $leads_table = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
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
        dbDelta($leads_table);
    }
    
    /**
     * Handle lead submission via AJAX
     */
    public function handle_lead_submission() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'chat_lead_nonce')) {
            wp_die('Security check failed');
        }
        
        // Sanitize input data
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $country = sanitize_text_field($_POST['country']);
        
        // Validate required fields
        $errors = array();
        
        if (empty($name)) {
            $errors[] = 'Name is required';
        }
        
        if (empty($email) || !is_email($email)) {
            $errors[] = 'Valid email is required';
        }
        
        if (empty($phone)) {
            $errors[] = 'Phone number is required';
        }
        
        if (empty($country)) {
            $errors[] = 'Country is required';
        }
        
        if (!empty($errors)) {
            wp_send_json_error(array('errors' => $errors));
            return;
        }
        
        // Check if email already exists
        global $wpdb;
        $existing_lead = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE email = %s",
            $email
        ));
        
        if ($existing_lead) {
            // Update existing lead
            $lead_id = $existing_lead->id;
            $wpdb->update(
                $this->table_name,
                array(
                    'name' => $name,
                    'phone' => $phone,
                    'country' => $country,
                    'updated_at' => current_time('mysql')
                ),
                array('id' => $lead_id),
                array('%s', '%s', '%s', '%s'),
                array('%d')
            );
        } else {
            // Insert new lead
            $result = $wpdb->insert(
                $this->table_name,
                array(
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'country' => $country,
                    'ip_address' => $this->get_client_ip(),
                    'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT']),
                    'chat_messages' => json_encode(array()),
                    'created_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
            );
            
            $lead_id = $wpdb->insert_id;
        }
        
        if ($lead_id) {
            // Send notification email to admin
            $this->send_new_lead_notification($lead_id);
            
            wp_send_json_success(array(
                'lead_id' => $lead_id,
                'message' => 'Lead captured successfully'
            ));
        } else {
            wp_send_json_error(array('message' => 'Failed to save lead'));
        }
    }
    
    /**
     * Handle chat message submission
     */
    public function handle_chat_message() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'chat_message_nonce')) {
            wp_die('Security check failed');
        }
        
        $lead_id = intval($_POST['lead_id']);
        $message = sanitize_textarea_field($_POST['message']);
        
        if (!$lead_id || empty($message)) {
            wp_send_json_error(array('message' => 'Invalid request'));
            return;
        }
        
        // Get existing messages
        global $wpdb;
        $lead = $wpdb->get_row($wpdb->prepare(
            "SELECT chat_messages FROM {$this->table_name} WHERE id = %d",
            $lead_id
        ));
        
        if (!$lead) {
            wp_send_json_error(array('message' => 'Lead not found'));
            return;
        }
        
        $messages = json_decode($lead->chat_messages, true) ?: array();
        
        // Add new message
        $messages[] = array(
            'message' => $message,
            'sender' => 'user',
            'timestamp' => current_time('mysql')
        );
        
        // Update database
        $updated = $wpdb->update(
            $this->table_name,
            array(
                'chat_messages' => json_encode($messages),
                'initial_message' => empty($lead->initial_message) ? $message : $lead->initial_message,
                'updated_at' => current_time('mysql')
            ),
            array('id' => $lead_id),
            array('%s', '%s', '%s'),
            array('%d')
        );
        
        if ($updated !== false) {
            // Send notification to admin about new message
            $this->send_new_message_notification($lead_id, $message);
            
            wp_send_json_success(array(
                'message' => 'Message saved successfully',
                'response' => 'Thank you for your message! Our partnership team will contact you shortly to discuss your inquiry in detail.'
            ));
        } else {
            wp_send_json_error(array('message' => 'Failed to save message'));
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Chat Leads',
            'Chat Leads',
            'manage_options',
            'forexdrift-chat-leads',
            array($this, 'admin_page'),
            'dashicons-phone',
            30
        );
        
        add_submenu_page(
            'forexdrift-chat-leads',
            'All Leads',
            'All Leads',
            'manage_options',
            'forexdrift-chat-leads',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'forexdrift-chat-leads',
            'Lead Details',
            'Lead Details',
            'manage_options',
            'forexdrift-lead-details',
            array($this, 'lead_details_page')
        );
    }
    
    /**
     * Admin page content
     */
    public function admin_page() {
        global $wpdb;
        
        // Handle status updates
        if (isset($_POST['update_status']) && wp_verify_nonce($_POST['_wpnonce'], 'update_lead_status')) {
            $lead_id = intval($_POST['lead_id']);
            $new_status = sanitize_text_field($_POST['status']);
            
            $wpdb->update(
                $this->table_name,
                array('status' => $new_status, 'updated_at' => current_time('mysql')),
                array('id' => $lead_id),
                array('%s', '%s'),
                array('%d')
            );
            
            echo '<div class="notice notice-success"><p>Lead status updated successfully!</p></div>';
        }
        
        // Get leads with pagination
        $per_page = 20;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;
        
        $leads = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ));
        
        $total_leads = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        $total_pages = ceil($total_leads / $per_page);
        
        ?>
        <div class="wrap">
            <h1>Chat Leads Dashboard</h1>
            
            <div class="lead-stats" style="display: flex; gap: 20px; margin: 20px 0;">
                <?php
                $stats = $wpdb->get_results("SELECT status, COUNT(*) as count FROM {$this->table_name} GROUP BY status");
                foreach ($stats as $stat):
                ?>
                <div class="stat-box" style="background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); min-width: 120px;">
                    <h3 style="margin: 0 0 5px 0; color: #666; font-size: 12px; text-transform: uppercase;"><?php echo ucfirst($stat->status); ?></h3>
                    <p style="margin: 0; font-size: 24px; font-weight: bold; color: #24E3B3;"><?php echo $stat->count; ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            
            <form method="get">
                <input type="hidden" name="page" value="forexdrift-chat-leads">
                <?php
                $list_table = new ForexDrift_Chat_Leads_Table();
                $list_table->prepare_items();
                $list_table->display();
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Lead details page
     */
    public function lead_details_page() {
        if (!isset($_GET['lead_id'])) {
            wp_die('Lead ID is required');
        }
        
        $lead_id = intval($_GET['lead_id']);
        global $wpdb;
        
        $lead = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $lead_id
        ));
        
        if (!$lead) {
            wp_die('Lead not found');
        }
        
        $messages = json_decode($lead->chat_messages, true) ?: array();
        
        ?>
        <div class="wrap">
            <h1>Lead Details: <?php echo esc_html($lead->name); ?></h1>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
                <div class="lead-info" style="background: #fff; padding: 20px; border-radius: 8px;">
                    <h3>Contact Information</h3>
                    <p><strong>Name:</strong> <?php echo esc_html($lead->name); ?></p>
                    <p><strong>Email:</strong> <a href="mailto:<?php echo esc_attr($lead->email); ?>"><?php echo esc_html($lead->email); ?></a></p>
                    <p><strong>Phone:</strong> <a href="tel:<?php echo esc_attr($lead->phone); ?>"><?php echo esc_html($lead->phone); ?></a></p>
                    <p><strong>Country:</strong> <?php echo esc_html($lead->country); ?></p>
                    <p><strong>Status:</strong> 
                        <span class="status-badge status-<?php echo esc_attr($lead->status); ?>">
                            <?php echo esc_html(ucfirst($lead->status)); ?>
                        </span>
                    </p>
                    <p><strong>Created:</strong> <?php echo date('M j, Y g:i A', strtotime($lead->created_at)); ?></p>
                    
                    <form method="post" style="margin-top: 20px;">
                        <?php wp_nonce_field('update_lead_status'); ?>
                        <input type="hidden" name="lead_id" value="<?php echo $lead_id; ?>">
                        <select name="status">
                            <option value="new" <?php selected($lead->status, 'new'); ?>>New</option>
                            <option value="contacted" <?php selected($lead->status, 'contacted'); ?>>Contacted</option>
                            <option value="qualified" <?php selected($lead->status, 'qualified'); ?>>Qualified</option>
                            <option value="converted" <?php selected($lead->status, 'converted'); ?>>Converted</option>
                            <option value="closed" <?php selected($lead->status, 'closed'); ?>>Closed</option>
                        </select>
                        <input type="submit" name="update_status" value="Update Status" class="button-primary">
                    </form>
                </div>
                
                <div class="chat-messages" style="background: #fff; padding: 20px; border-radius: 8px; max-height: 600px; overflow-y: auto;">
                    <h3>Chat Messages</h3>
                    <?php if (!empty($messages)): ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="message" style="margin-bottom: 15px; padding: 10px; background: #f9f9f9; border-radius: 8px;">
                                <div style="font-size: 12px; color: #666; margin-bottom: 5px;">
                                    <strong><?php echo ucfirst($msg['sender']); ?></strong> - 
                                    <?php echo date('M j, Y g:i A', strtotime($msg['timestamp'])); ?>
                                </div>
                                <div><?php echo esc_html($msg['message']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No chat messages yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <style>
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-new { background: #ffeaa7; color: #2d3436; }
        .status-contacted { background: #74b9ff; color: #2d3436; }
        .status-qualified { background: #00b894; color: white; }
        .status-converted { background: #00cec9; color: #2d3436; }
        .status-closed { background: #636e72; color: white; }
        </style>
        <?php
    }
    
    /**
     * Enqueue admin scripts
     */
    public function admin_scripts($hook) {
        if (strpos($hook, 'forexdrift-chat-leads') === false) {
            return;
        }
        
        wp_enqueue_style('forexdrift-admin', get_template_directory_uri() . '/assets/css/admin.css');
    }
    
    /**
     * Send new lead notification email
     */
    private function send_new_lead_notification($lead_id) {
        global $wpdb;
        
        $lead = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $lead_id
        ));
        
        if (!$lead) return;
        
        $admin_email = get_option('admin_email');
        $subject = 'New Chat Lead from ForexDrift';
        
        $message = "
        New chat lead received:
        
        Name: {$lead->name}
        Email: {$lead->email}
        Phone: {$lead->phone}
        Country: {$lead->country}
        
        View lead details: " . admin_url('admin.php?page=forexdrift-lead-details&lead_id=' . $lead_id) . "
        ";
        
        wp_mail($admin_email, $subject, $message);
    }
    
    /**
     * Send new message notification
     */
    private function send_new_message_notification($lead_id, $message) {
        global $wpdb;
        
        $lead = $wpdb->get_row($wpdb->prepare(
            "SELECT name, email FROM {$this->table_name} WHERE id = %d",
            $lead_id
        ));
        
        if (!$lead) return;
        
        $admin_email = get_option('admin_email');
        $subject = 'New Chat Message from ' . $lead->name;
        
        $email_message = "
        New chat message from {$lead->name} ({$lead->email}):
        
        Message: {$message}
        
        View full conversation: " . admin_url('admin.php?page=forexdrift-lead-details&lead_id=' . $lead_id) . "
        ";
        
        wp_mail($admin_email, $subject, $email_message);
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_keys = array('HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) && !empty($_SERVER[$key])) {
                $ip = explode(',', $_SERVER[$key]);
                $ip = trim($ip[0]);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown';
    }
}

// Note: This class is initialized by the main Whisp_Plugin class
// Do not instantiate directly when used as plugin component
