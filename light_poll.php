<?php
/*
 * Plugin Name:       Light Poll
 * Description:       Plugin for creating polls, voting, and displaying results in the form of charts.
 * Version:           1.0.0
 * Author:            Dmytro Popov
 * License:           GPLv2 or later 
 */
if(!function_exists('lgpoll_menu') && !function_exists('lgpoll_settings') && !function_exists('lgpoll_poll_settings')) {
 
global $wpdb;
global $table_1;
$table_1 = $wpdb->prefix . 'light_poll_list';
global $table_2;
$table_2 = $wpdb->prefix . 'light_poll_answers';
global $table_3;
$table_3 = $wpdb->prefix . 'light_poll_result';

add_action('admin_menu', 'lgpoll_menu');

function lgpoll_menu() {
    add_menu_page(
            'Light Poll Settings',
            'Light Poll',
            'manage_options',
            "lp_settings",
            "lgpoll_settings",
            "dashicons-chart-bar"
    );

    add_submenu_page('', 'Poll settings', 'Poll settings',
            'manage_options', 'poll_settings', 'lgpoll_poll_settings'
    );
    add_submenu_page('', 'Chart settings', 'Chart settings',
            'manage_options', 'chart_settings', 'lgpoll_chart_settings'
    );
}

function lgpoll_settings() {
    include(plugin_dir_path(__FILE__) . 'menu/plugin_settings.php');
}

function lgpoll_poll_settings() {
    include(plugin_dir_path(__FILE__) . 'menu/poll_settings.php');
}

function lgpoll_chart_settings() {
    include(plugin_dir_path(__FILE__) . 'menu/chart_settings.php');
}

class LightPoll_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
                'light_poll',
                'Light Poll',
                array('description' => __('Light Poll Widget', 'lp_text'),)
        );
    }

    public function widget($args, $instance) {
        $current_poll_id = !empty($instance['poll_id']) ? $instance['poll_id'] : "";
        $current_poll_view = !empty($instance['poll_view']) ? $instance['poll_view'] : "";
        $user = wp_get_current_user();
        $user_email = $user->user_email;
        $user_id = get_current_user_id();
        global $wpdb;
        global $table_1;
        global $table_2;
        global $table_3;
        $poll = $wpdb->get_results("SELECT * FROM $table_1 WHERE id=$current_poll_id");
        $poll_answers = $wpdb->get_results("SELECT * FROM $table_2 WHERE question_id=$current_poll_id");
        $poll_user_answer = $wpdb->get_results("SELECT * FROM $table_3 WHERE question_id= $current_poll_id AND user_email = '$user_email'");

        if(count($poll)>0){
        $interval = date_diff(new DateTime(date("Y-m-d")), new DateTime($poll[0]->{"expiry"}));
        $date_diff = $interval->format('%R%a');
     
        if((int)$date_diff > 0){
        include(plugin_dir_path(__FILE__) . "page/chart_form.php");
        }else{

            ?>
             
             <div><span class="lp_expired"><?php echo __('Expired', 'lp_text');?></span></div>

            <?php

        }}

}

public function form($instance) {
$current_poll_id = !empty($instance['poll_id']) ? $instance['poll_id'] : "";
$current_poll_view = !empty($instance['poll_view']) ? $instance['poll_view'] : "";
global $wpdb;
global $table_1;
$polls = $wpdb->get_results("SELECT * FROM  $table_1");
?>
<br />
<label><?php echo __('Poll:', 'lp_text'); ?></label>
<select name="<?php echo esc_attr($this->get_field_name('poll_id')); ?>">
    <option>none</option>
<?php
foreach ($polls as &$poll) {
?>
    <option value="<?php echo esc_html($poll->{"id"}); ?>" <?php if ($current_poll_id == $poll->{"id"}) {
    echo "selected";
}; ?>>
    <?php echo esc_html($poll->{"name"}); ?></option>

        <?php
    }
    ?>
</select>
<br />
<br />
<label><?php echo __('Chart link:', 'lp_text'); ?></label>
<select name="<?php echo esc_attr($this->get_field_name('poll_view')); ?>">
<?php if ($current_poll_view == "hide") { ?>
    <option value="show"><?php echo __('Show', 'lp_text');?></option>
    <option value="hide" selected><?php echo __('Hide', 'lp_text');?></option>
<?php } else { ?>
    <option value="show" selected><?php echo __('Show', 'lp_text');?></option>
    <option value="hide"><?php echo __('Hide', 'lp_text');?></option>
    <?php
}
?>
</select>
<br /> <br />
<?php
}

public function update($new_instance, $old_instance) {
$instance = array();

$instance['poll_id'] = (!empty($new_instance['poll_id']) ) ? strip_tags($new_instance['poll_id']) : '';
$instance['poll_view'] = (!empty($new_instance['poll_view']) ) ? strip_tags($new_instance['poll_view']) : '';

return $instance;
}

}

add_action('widgets_init', 'lgpoll_register_widgets');

function lgpoll_register_widgets() {
register_widget('LightPoll_Widget');
}

function lgpoll_db_install() {

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

global $wpdb;

global $table_1;

$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE $table_1 (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		name varchar(50) NOT NULL,
        question tinytext NOT NULL,
		chart_type varchar(30) NOT NULL,
        user_type varchar(30) NOT NULL,
        expiry varchar(50) NOT NULL,
        options tinytext NOT NULL,
        style tinytext NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

dbDelta($sql);

global $table_2;

$sql = "CREATE TABLE $table_2 (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		answer tinytext NOT NULL,
        question_id mediumint(9) NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

dbDelta($sql);

global $table_3;

$sql = "CREATE TABLE $table_3 (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
        answer_id tinytext NOT NULL,
        question_id mediumint(9) NOT NULL,
        user_id mediumint(9) NOT NULL,
        user_email varchar(30) NOT NULL,
        user_name varchar(30) NOT NULL,
        time datetime NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

dbDelta($sql);
}

function lgpoll_db_delete() {
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "light_poll_list");
$wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "light_poll_answers");
$wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "light_poll_result");
}

register_activation_hook(__FILE__, 'lgpoll_db_install');
//register_deactivation_hook( __FILE__, '' );
register_uninstall_hook(__FILE__, 'lgpoll_db_delete');

function lgpoll_save_vote() {

$question_id = sanitize_key($_POST['question_id']);
$user_id = sanitize_key($_POST['user_id']);
$user_email = sanitize_email($_POST['user_email']);
global $wpdb;
global $table_3;
$status = "ok";
if (is_email($_POST['user_email'])) {

$poll_user_answer_email = $wpdb->get_results("SELECT COUNT(*) FROM $table_3 WHERE question_id = $question_id AND user_email = '$user_email'");

if (isset($_POST['question_id']) && isset($_POST['user_id']) && isset($_POST['answer_id'])) {
if (is_numeric($_POST['question_id']) && is_numeric($_POST['user_id']) && is_numeric($_POST['answer_id'])) {
if ($poll_user_answer_email[0]->{"COUNT(*)"} == 0) {
    $wpdb->insert(
            $table_3,
            array(
                'answer_id' => sanitize_key($_POST['answer_id']),
                'question_id' => sanitize_key($_POST['question_id']),
                'user_id' => sanitize_key($_POST['user_id']),
                'time' => date("Y-m-d H:i:s"),
                'user_email' => sanitize_email($_POST['user_email']),
                'user_name' => sanitize_title($_POST['user_name'])
    ));
} else {
$status = "voted";
}
} else {
    $status = "wrongdata";
}
} else {
$status = "nodata";
}
} else {
$status = "notemail";
}

if (isset($_POST["lp_unreg"])) {
if ($_POST["lp_unreg"] == true) {
echo $status;
} else {
if (wp_redirect($_SERVER['HTTP_REFERER'])) {
exit;
}
}
}else{
if (wp_redirect($_SERVER['HTTP_REFERER'])) {
exit;
} 
}
}

add_action('init', 'lgpoll_req_init');

function lgpoll_req_init() {
$user = wp_get_current_user();
$allowed_roles = array('administrator');
if (array_intersect($allowed_roles, $user->roles)) {
add_action('admin_post_save_vote', 'lgpoll_save_vote');
} else {
add_action('admin_post_nopriv_save_vote', 'lgpoll_save_vote');
}
}

add_shortcode('lightpoll', 'lgpoll_shortcode');

function lgpoll_shortcode($atts) {

if(!isset($atts['link'])){
    $link = "show";
}else{
    $link = $atts['link'];
}
if(isset($atts['id'])){
$instance = ["poll_id" => $atts['id'], "poll_view" => $link];
the_widget('LightPoll_Widget', $instance);
}
}
// [lightpoll id='3' link='show']


function lgpoll_page() {

  $current_poll_id = sanitize_key($_GET['id']);

  include_once(plugin_dir_path(__FILE__) . 'page/chart_page.php');

}

add_action('admin_post_lgpoll_page', 'lgpoll_page');

add_action('admin_post_nopriv_lgpoll_page', 'lgpoll_page');

}

?>