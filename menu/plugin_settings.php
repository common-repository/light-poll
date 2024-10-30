<?php
wp_register_style('lgpoll_style', plugin_dir_url(__FILE__) . '../css/style.css');
wp_enqueue_style('lgpoll_style');
$user = wp_get_current_user();
if (in_array( 'administrator', (array) $user->roles)) {

?>

<h1>Light Poll</h1>
<br/>
<div class="lp_fields_wrap">
<div>
<span><?php echo __('Add new poll:', 'lp_text');?></span>
<form  action="/wp-admin/admin.php?page=lp_settings&task=add" method="post">
<input type="text" name="name" maxlength="50">

<button class="button"><?php echo __('Add', 'lp_text');?></button>

</form>
</div>
<div>
<span><?php echo __('Search poll:', 'lp_text');?></span>
<form method="post">
<input type="text" value="<?php if(isset($_POST['filter'])){echo esc_html($_POST['filter']);}?>" name="filter" maxlength="50">

<button class="button"><?php echo __('Search', 'lp_text');?></button>

</form>
</div>
</div>
<br/>
<?php



global $wpdb;
global $table_1;
$table_1 = $wpdb->prefix . 'light_poll_list';
global $table_2;
$table_2 = $wpdb->prefix . 'light_poll_answers';
global $table_3;
$table_3 = $wpdb->prefix . 'light_poll_result';

function lgpoll_add() {
	global $wpdb;	
	global $table_1;

	if(isset($_POST['name'])){
	$name = sanitize_text_field(trim($_POST['name']));
	$name = str_replace("\\", "", $name);
	if(strlen($name)>0){
	$polls = $wpdb->get_results("SELECT * FROM  $table_1 WHERE name='$name'");	

	if(count($polls)==0){
	$wpdb->insert( 
		$table_1, 
		array( 
			'name' => $name,
			'user_type' => 'unregistered',
			'chart_type' => 'chart_1',
			'expiry' => date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")+10, date("Y")))
		) 
	);
}
}
}
}

function lgpoll_remove() {
	global $wpdb;	
	global $table_1;
	global $table_2;
	global $table_3;
	$id = sanitize_key($_GET['id']);
	if(isset($id) && is_numeric($id)){
	
	$wpdb->delete( 
		$table_1, 
		array( 
			'id' => $id
		) 
	);

	$wpdb->delete( 
		$table_2, 
		array( 
			'question_id' => $id
		) 
	);

	$wpdb->delete( 
		$table_3, 
		array( 
			'question_id' => $id
		) 
	);

}
}

if(isset($_GET['task'])){
    if(function_exists('lgpoll_'.sanitize_file_name($_GET['task']))) {
        call_user_func('lgpoll_'.sanitize_file_name($_GET['task']));
     }
}

if(isset($_POST['filter'])){
$filter = sanitize_text_field($_POST['filter']);
$polls = $wpdb->get_results("SELECT * FROM  $table_1 WHERE name LIKE '%$filter%'");
}else{
$polls = $wpdb->get_results("SELECT * FROM  $table_1");	
}
?>
<div class="lp_list_wrap">
<ol class="lp_list">

<?php
foreach ($polls as &$value) {
?>

<li><span><a href="/wp-admin/admin.php?page=poll_settings&id=<?php echo esc_html($value->{"id"}); ?>"><?php echo esc_html($value->{"name"}); ?></a>
<span data-name="<?php echo esc_html($value->{"name"}); ?>" data-ref="/wp-admin/admin.php?page=lp_settings&task=remove&id=<?php echo esc_html($value->{"id"}); ?>" 
class="lp_del_btn dashicons dashicons-no-alt" onclick="lgpoll_delete(event);"></span>
</span></li>
<br/>
<?php
}

?>


</ol>
</div>


<?php }else{

  echo __('You do not have access to this menu.', 'lp_text');

} ?>
 

 <script>

function lgpoll_delete(e){

var name = e.target.getAttribute("data-name");
var url = e.target.getAttribute("data-ref");
var status = confirm("<?php echo __('You really want to delete', 'lp_text');?> '"+name+"' ?");
if(status == true){
	window.location.href = url;
}
}

 </script>