
<?php
wp_register_style('lgpoll_style', plugin_dir_url(__FILE__) . '../css/style.css');
wp_enqueue_style('lgpoll_style');
global $wpdb;
global $table_1;
$table_1 = $wpdb->prefix . 'light_poll_list';
global $table_2;
$table_2 = $wpdb->prefix . 'light_poll_answers';
global $table_3;
$table_3 = $wpdb->prefix . 'light_poll_result';
global $poll_id;
$poll_id = sanitize_key($_GET['id']);

if(isset($poll_id) && is_numeric($poll_id)){

$polls = $wpdb->get_results("SELECT * FROM  $table_1 WHERE id=$poll_id");

if(count($polls)>0){

function lgpoll_update_question() {
    global $wpdb;
    global $table_1;
    global $poll_id;
    if (isset($_POST['question'])) {
        $question = sanitize_text_field(trim($_POST['question']));
        if(strlen($question)<150){
        $data = ['question' => $question];
        $where = ['id' => $poll_id];
        $wpdb->update($table_1, $data, $where);
        }
    }
}

function lgpoll_update_user_type() {
    global $wpdb;
    global $table_1;
    global $poll_id;
    if (isset($_POST['user_type'])) {
        $user_type = sanitize_text_field(trim($_POST['user_type']));
        if(strlen($user_type)<50){
        $data = ['user_type' => $user_type];
        $where = ['id' => $poll_id];
        $wpdb->update($table_1, $data, $where);
        }
    }
}

function lgpoll_update_expiry() {
    global $wpdb;
    global $table_1;
    global $poll_id;
    if (isset($_POST['expiry'])) {
        $expiry = sanitize_text_field(trim($_POST['expiry']));
        if(strlen($expiry)<50){
        $data = ['expiry' => $expiry];
        $where = ['id' => $poll_id];
        $wpdb->update($table_1, $data, $where);
        }
    }
}

function lgpoll_add_answer() {
    global $wpdb;
    global $table_2;
    global $poll_id; 
    if (isset($_POST['name']) && strlen(preg_replace('/\s+/', '', $_POST['name'])) > 0) {
        $name = sanitize_text_field(trim($_POST['name']));
        $name = str_replace("\\", "", $name);
        $poll_answers = $wpdb->get_results("SELECT * FROM $table_2 WHERE question_id=$poll_id AND answer='$name'");
        if(count($poll_answers)==0 && strlen($name)<100){
        $data = ['answer' => $name, 'question_id' => $poll_id];
        $wpdb->insert($table_2, $data);
    }
}
}

function lgpoll_remove_answer() {
    global $wpdb;
    global $table_2;
    global $wpdb;
    global $table_3;
    global $poll_id;
    if (isset($_GET['answer_id']) && is_numeric($_GET['answer_id'])) {
        $id = sanitize_key($_GET['answer_id']);

        $wpdb->delete(
                $table_2,
                array(
                    'id' => $id
                )
        );

        $wpdb->delete(
                $table_3,
                array(
                    'answer_id' => $id,
                    'question_id' => $poll_id,
                )
        );
    }
}

if(isset($_GET['task'])){
    if(function_exists('lgpoll_'.$_GET['task'])) {
        call_user_func('lgpoll_'.$_GET['task']);
     }
    }

$polls = $wpdb->get_results("SELECT * FROM  $table_1 WHERE id=$poll_id");
$poll_answers = $wpdb->get_results("SELECT * FROM $table_2 WHERE question_id=$poll_id");
?>
<br/>
<a href="/wp-admin/admin.php?page=lp_settings"><?php echo __('Back', 'lp_text');?></a>
<div class="lp_fields_wrap">
    <h1><span class="lp_title"><?php echo __('Name:', 'lp_text');?></span> <?php echo esc_html($polls[0]->{"name"}); ?></h1>
</div>
<div class="lp_fields_wrap">
    <h2><span class="lp_title"><?php echo __('Question:', 'lp_text');?></span> <?php echo esc_html($polls[0]->{"question"}); ?></h2>
    <br/>
    <form  action="/wp-admin/admin.php?page=poll_settings&id=<?php echo esc_html($poll_id); ?>&task=update_question" method="post">
        <label><?php echo __('Update question:', 'lp_text');?></label>
        <input type="text" name="question" >

        <button class="button"><?php echo __('Save', 'lp_text');?></button>

    </form>
    <br/>
</div>
<br/>
<div class="lp_fields_wrap">
<div>  
    <form  action="/wp-admin/admin.php?page=poll_settings&id=<?php echo esc_html($poll_id); ?>&task=update_user_type" method="post">
        <label><?php echo __('Available for:', 'lp_text');?></label><br/>
        <select name="user_type">
      
<?php if(!empty($polls[0]->{"user_type"}) && $polls[0]->{"user_type"} != "unregistered") {?>
    <option value="unregistered"><?php echo __('Unregistered', 'lp_text');?></option>
<?php echo wp_dropdown_roles($polls[0]->{"user_type"}); }else{?>
    <option value="unregistered" selected="selected"><?php echo __('Unregistered', 'lp_text');?></option>
<?php echo wp_dropdown_roles(); } ?>
        </select>
        <button class="button"><?php echo __('Save', 'lp_text');?></button>
    </form>    
    </div> 
    <div>   
    <form  action="/wp-admin/admin.php?page=poll_settings&id=<?php echo esc_html($poll_id); ?>&task=update_expiry" method="post">
        <label><?php echo __('Available to:', 'lp_text');?></label><br/>
    <input type="date" name="expiry" value="<?php echo esc_html($polls[0]->{"expiry"}); ?>">
        <button class="button"><?php echo __('Save', 'lp_text');?></button>
    </form>
    </div>  
    <br/>  <br/>
</div>
<br/>
<div class="lp_fields_wrap">
    <a class="button" href="/wp-admin/admin.php?page=chart_settings&id=<?php echo esc_html($poll_id); ?>"><?php echo __('Edit chart', 'lp_text');?></a>
    <a href="<?php echo admin_url() . 'admin-post.php?action=lgpoll_page&id=' . esc_html($poll_id); ?>"><?php echo __('Chart', 'lp_text'); ?></a>
    <br/><br/>
</div>

<br/>


<div class="lp_fields_wrap">
    <div>    
        <form action="/wp-admin/admin.php?page=poll_settings&id=<?php echo esc_html($poll_id); ?>&task=add_answer" method="post">
            <label><?php echo __('Add answer:', 'lp_text');?></label><br/>
            <input type="text" name="name" >

            <button class="button"><?php echo __('Add', 'lp_text');?></button>

        </form>

        <br/><br/>

        <table>
            <tr><th><?php echo __('Answer', 'lp_text');?></th><th><?php echo __('Count', 'lp_text');?></th></tr>
<?php
$total_voters = 0;
$poll_answers_obj = (object) [];
foreach ($poll_answers as &$value) {
    $poll_answers_obj->{$value->{"id"}} = $value->{"answer"};
    $answer_id = $value->{"id"};
    $answer_results = $wpdb->get_results("SELECT * FROM $table_3 WHERE answer_id=$answer_id AND question_id=$poll_id");
    $total_voters += count($answer_results);
    ?>

                <tr>
                    <td><?php echo esc_html($value->{"answer"}); ?> 
                        <span onclick="lgpoll_delete(event);" data-name="<?php echo esc_html($value->{"answer"}); ?>" 
                        data-ref="/wp-admin/admin.php?page=poll_settings&task=remove_answer&id=<?php echo esc_html($poll_id); ?>&answer_id=<?php echo esc_html($value->{"id"}); ?>" 
                        class="lp_del_btn dashicons dashicons-no-alt"></span>
                    </td>

                    <td><?php echo count($answer_results); ?></td>

                </tr>

                <?php
            }
            ?>

        </table>
        <br/>
        <span><strong><?php echo __('Total:', 'lp_text');?></strong>  <?php echo $total_voters; ?>;</span>
        <br/>
    </div>
    <div>

        <span><?php echo __('Check user by email:', 'lp_text');?></span>
        <br/>
        <form action="/wp-admin/admin.php?page=poll_settings&id=<?php echo esc_html($poll_id); ?>&task=find_user_answer" method="POST">

            <input type="text" name="user_email">
            <button class="button"><?php echo __('Search', 'lp_text');?></button>
        </form>

<?php

if($_GET['task'] == "find_user_answer"){

    global $poll_user_results;
    global $poll_id;
    global $wpdb;
    global $table_3;
    global $poll_user_results;

if (isset($_POST['user_email'])) {
    $user_email = sanitize_email($_POST['user_email']);
    if (strlen(str_replace(' ', '', $_POST['user_email']))) {
    $poll_user_results = $wpdb->get_results("SELECT * FROM $table_3 WHERE question_id=$poll_id AND user_email LIKE '%$user_email%'");
if (isset($poll_user_results)) {
    if (count($poll_user_results) > 0) {
        ?>
                <br/>
                <table>
                    <tr><th><?php echo __('Name', 'lp_text');?></th><th><?php echo __('Answer', 'lp_text');?></th><th><?php echo __('Email', 'lp_text');?></th></tr>
                <?php
                foreach ($poll_user_results as &$user) {
                    ?>

                        <tr><td><?php echo esc_html($user->{'user_name'}); ?></td><td><?php echo esc_html($poll_answers_obj->{$user->{'answer_id'}}); ?></td><td><?php echo esc_html($user->{'user_email'}); ?></td></tr>



                    <?php
                }
                ?>
                </table>
                    <?php
                } else {
                    echo "<span>User '" . esc_html($_POST['user_email']) . "' did not vote.</span>";
                }
            }}else{
                 echo "<span>".__('Field is empty!', 'lp_text')."</span>";
            }}}
            ?>
            <br/>
    </div>
    <br/> <br/>
</div>
<script>

    function lgpoll_delete(e) {

        var name = e.target.getAttribute("data-name");
        var url = e.target.getAttribute("data-ref");
        var status = confirm("<?php echo __('You really want to delete', 'lp_text');?> '" + name + "' ?");
        if (status == true) {
            window.location.href = url;
        }
    }

</script>


<?php } }?>