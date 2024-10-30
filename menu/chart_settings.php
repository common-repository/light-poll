<?php
wp_register_style('lgpoll_style', plugin_dir_url(__FILE__) . '../css/style.css');
wp_enqueue_style('lgpoll_style');
wp_register_script('lgpoll_chartjs', plugin_dir_url(__FILE__) . '../lib/chart.min.js');
wp_enqueue_script('lgpoll_chartjs');


global $poll_id;
$poll_id = sanitize_key($_GET["id"]);
global $wpdb;
global $table_1;
$table_1 = $wpdb->prefix . 'light_poll_list';
global $table_2;
$table_2 = $wpdb->prefix . 'light_poll_answers';
global $table_3;
$table_3 = $wpdb->prefix . 'light_poll_result';
global $poll_answers;

if(isset($poll_id) && is_numeric($poll_id)){

  function lgpoll_save(){

    $obj = (object) array();
    if(!empty($_POST["bgcolor"])){
    $obj->bgcolor = sanitize_text_field($_POST["bgcolor"]);
    }
    if(!empty($_POST["textcolor"])){
      $obj->textcolor = sanitize_text_field($_POST["textcolor"]);
    }
    if(!empty($_POST["image"])){
    $obj->image = sanitize_text_field($_POST["image"]);
    }
    if(!empty($_POST["chart_color"])){
    $obj->chart_color = sanitize_post($_POST["chart_color"]);
    }
    if(!empty($_POST["chart_width"])){
    $obj->chart_width = sanitize_text_field($_POST["chart_width"]);
    }
    if(!empty($_POST["header"])){
    $obj->header = sanitize_text_field($_POST["header"]);
    }
    if(!empty($_POST["survey"])){
    $obj->survey = sanitize_text_field($_POST["survey"]);
    }
    if((!empty($_POST["chart_width"]) && is_numeric($_POST["chart_width"])) && !empty($_POST["header"])){
        global $wpdb;	
        global $table_1;
        global $poll_id;
        $style = [];
        if(!empty($_POST["style"])){
        $style = sanitize_textarea_field($_POST["style"]);
        }
        if(isset($style)){
        $data = [ 'options' => json_encode($obj), 'style' => $style];
        }else{
        $data = [ 'options' => json_encode($obj)];
        }
        $format = [ NULL ]; 
        $where = [ 'id' => $poll_id ];
        $where_format = [ NULL ]; 
        $wpdb->update($table_1, $data, $where);
    }
    
        
      
    }
  
    function lgpoll_update_type() {
        global $wpdb;	
        global $table_1;
        global $poll_id;
      if(isset($_POST['chart_type'])){
      $chart_type = sanitize_text_field($_POST['chart_type']);
        $data = [ 'chart_type' => $chart_type ];
        $format = [ NULL ]; 
        $where = [ 'id' => $poll_id ];
        $where_format = [ NULL ]; 
        $wpdb->update($table_1, $data, $where);
    
    }
    }
  
    if(isset($_GET['task'])){
      if(function_exists('lgpoll_'.$_GET['task'])) {
          call_user_func('lgpoll_'.$_GET['task']);
       }
      }

$polls = $wpdb->get_results("SELECT * FROM  $table_1 WHERE id=$poll_id");

if(count($polls)>0){

$poll_answers = $wpdb->get_results("SELECT * FROM  $table_2 WHERE question_id=$poll_id");


$dir = plugin_dir_path(__FILE__) . '../chart/';
$files = scandir($dir);
$chart_types = array();
for($i = 0; $i < count($files); $i++) {
if(is_dir($files[$i]) == false){
   if(file_exists($dir.$files[$i].'/settings.json') == true){
 $file = file_get_contents($dir.$files[$i].'/settings.json');

 if($file != false){
 array_push($chart_types, [json_decode($file), $files[$i]]);
 }
}
}
 
}
  
  $polls_options = json_decode($polls[0]->options);

  global $poll_uniq_name;
  $poll_uniq_name = str_replace(" ", "_", $polls[0]->name).'_'.$poll_id;
   
  if(!isset($polls_options)){
    $polls_options = (object)[];
  }
  
  if(!isset($polls_options->chart_width)){
    $polls_options->chart_width = 500;
  }
  
?>
<br/>
<a href="/wp-admin/admin.php?page=poll_settings&&id=<?php echo esc_html($poll_id); ?>"><?php echo __('Back', 'lp_text');?></a>
<br/>
<br/>
<form class="lp_fields_wrap" action="/wp-admin/admin.php?page=chart_settings&id=<?php echo esc_html($poll_id); ?>&task=update_type" method="post">
<select name="chart_type">
<option><?php echo __('None', 'lp_text');?></option>
<?php
foreach ($chart_types as &$type) {

?>

<option value="<?php echo esc_html($type[1]); ?>" <?php if($polls[0]->{"chart_type"} == $type[1]){echo "selected";}; ?>><?php echo esc_html($type[0]->name); ?></option>

<?php
	
}

?>
</select>
<button class="button"><?php echo __('Save', 'lp_text');?></button>
<br/><br/>
</form>
<br/>
<form class="lp_fields_wrap" action="/wp-admin/admin.php?page=chart_settings&id=<?php echo esc_html($poll_id); ?>&task=save" method="post">

<div>
<div><span><?php echo __('Text color:', 'lp_text');?></span><br/>
<input type="text" value="<?php if(isset($polls_options->textcolor)){ echo esc_html($polls_options->textcolor); } ?>" name="textcolor" id="text_color_input"><input type="color" value="<?php if(isset($polls_options->textcolor)){ echo esc_html($polls_options->textcolor); } ?>" id="text_color_picker"></div>
<br/>
<br/>
<div><span><?php echo __('BG color:', 'lp_text');?></span><br/>
<input type="text" value="<?php if(isset($polls_options->bgcolor)){ echo esc_html($polls_options->bgcolor); } ?>" name="bgcolor" id="bg_color_input"><input type="color" value="<?php if(isset($polls_options->bgcolor)){ echo esc_html($polls_options->bgcolor); } ?>" id="bg_color_picker"></div>
<br/>
<br/>
<span>

<?php if(!empty($polls_options->image)){ ?>
  <span class="bg_img_wrap">
<img src="<?php echo esc_html($polls_options->image); ?>" id="bg_img" alt="img"><span class="sp-del-btn dashicons dashicons-no-alt del_img"></span>
</span>
<?php }else{ ?> 
<span class="bg_img_wrap" style="display:none;">
  <img src="" id="bg_img" alt="img"><span class="sp-del-btn dashicons dashicons-no-alt del_img"></span>
</span>
<?php } ?>
<input type="text" value="<?php if(isset($polls_options->image)){ echo esc_html($polls_options->image); } ?>" id="bg_img_src" name="image" hidden>
<button class="bg_btn button" type="button"><?php echo __('BG Img', 'lp_text');?></button>
</span>
<br/>

<br/>
<div><span><?php echo __('Show header:', 'lp_text');?></span><br/>
</div>
<span>
<label><?php echo __('No', 'lp_text');?></label>
  <input type="radio" name="header" value="no" <?php if(isset($polls_options->header)){ if($polls_options->header == "yes"){  }elseif($polls_options->header == "no"){echo "checked";} } ?>>
  
</span>

<span>
<label><?php echo __('Yes', 'lp_text');?></label>
  <input type="radio" name="header" value="yes" <?php if(isset($polls_options->header)){ if($polls_options->header == "yes"){ echo "checked"; }elseif($polls_options->header == "no"){}else{echo "checked";} }else{ echo "checked"; } ?>>
</span>
<br/><br/>
<div><?php echo __('Show survey form:', 'lp_text');?><br/>
</div>
<span>
<label><?php echo __('No', 'lp_text');?></label>
  <input type="radio" name="survey" value="no" <?php if(isset($polls_options->survey)){ if($polls_options->survey == "yes"){  }elseif($polls_options->survey == "no"){echo "checked";} }else{ echo "checked"; } ?>>
</span>

<span>
<label ><?php echo __('Yes', 'lp_text');?></label>
  <input type="radio" name="survey" value="yes" <?php if(isset($polls_options->survey)){ if($polls_options->survey == "yes"){ echo "checked"; }elseif($polls_options->survey == "no"){}else{echo "checked";} } ?>>
</span>

<br/><br/>
<div><span><?php echo __('CSS style:', 'lp_text');?></span><br/>
<textarea name="style" style="width:300px; height:100px;"><?php echo esc_html($polls[0]->style); ?></textarea>

</div>

</div>
<div>

<span><?php echo __('Width:', 'lp_text');?></span><br/>
<input name="chart_width" value="<?php echo esc_html($polls_options->chart_width); ?>" type="number"/>
<br/>
<br/>
<?php
global $def_color;
$def_color =  ["#00FFB1", "#FFB100", "#FF004E", "#B100FF", "#D2FF00", "#FF00D2", "#2D00FF", "#00D2FF", "#FF9A4D", "#9A4DFF", "#4DB2FF", "#4DFF9A", "#00FFB1", "#FFB100",
 "#FF004E", "#B100FF", "#D2FF00", "#FF00D2", "#2D00FF", "#00D2FF", "#FF9A4D", "#9A4DFF", "#4DB2FF", "#4DFF9A"];
$poll_answers_obj = (object)[];
$k = 0;
foreach ($poll_answers as &$value) {

    $poll_answers_obj->{$value->{"id"}} = $value->{"answer"};
    $answer_id = $value->{"id"};
    $answer_results = $wpdb->get_results("SELECT * FROM $table_3 WHERE answer_id=$answer_id AND question_id=$poll_id");
    
?>
<span><?php echo esc_html($value->{"answer"}); ?><br>
<input type="text" value="<?php if(isset($polls_options->chart_color->{$value->{"id"}})){  echo esc_html($polls_options->chart_color->{$value->{"id"}}); }else{ if(isset($def_color[$k])){ echo esc_html($def_color[$k]); } } ?>" class="color_input" name="chart_color[<?php echo esc_html($value->{"id"}); ?>]" data-id="<?php echo esc_html($value->{"id"}); ?>">
<input type="color" value="<?php if(isset($polls_options->chart_color->{$value->{"id"}})){ echo esc_html($polls_options->chart_color->{$value->{"id"}}); }else{ if(isset($def_color[$k])){ echo esc_html($def_color[$k]); } } ?>" class="color_picker" data-id="<?php echo esc_html($value->{"id"}); ?>"></span><br/><br/>

<?php
    $k++;
}
?>

</div>
<br/><br/>
<button class="button" type="submit"><?php echo __('Save', 'lp_text');?></button>
<br/><br/>
</form>
<br/>

<a href="<?php echo admin_url() . 'admin-post.php?action=lgpoll_page&id=' . esc_html($poll_id); ?>"><?php echo __('Current chart', 'lp_text');?></a>
<?php

add_action ( 'admin_enqueue_scripts', function () {
    if (is_admin()){
        wp_enqueue_media();
    }
});

?>
<div style="display:none;">
<?php echo media_buttons();?>
</div>

<script>
jQuery(function($){

  jQuery(".bg_btn").on("click", function(){

var frame = wp.media({
     title: '<?php echo __('Select or Upload Media'); ?>',
     button: {
       text: '<?php echo __('Use this media'); ?>'
     },
     multiple: false
   });
   
   frame.on( 'select', function() {
     
     var attachment = frame.state().get('selection').first().toJSON();
      
      $("#bg_img").attr("src", attachment.url);
      $(".bg_img_wrap").show();
      $("#bg_img_src").val(attachment.url);
      
   });

   frame.open();


});

jQuery(".color_input").on("change", function(){

var id = $(this).attr("data-id");
var val = $(this).val();

$(".color_picker[data-id="+id+"]").val(val);

});

jQuery(".color_picker").on("change", function(){

var id = $(this).attr("data-id");
var val = $(this).val();

$("input[data-id="+id+"]").val(val);

});

jQuery("#bg_color_picker").on("change", function(){

var id = $(this).attr("data-id");
var val = $(this).val();

$("#bg_color_input").val(val);


});

jQuery("#bg_color_input").on("change", function(){

var id = $(this).attr("data-id");
var val = $(this).val();

$("#bg_color_picker").val(val);

});

jQuery("#text_color_picker").on("change", function(){

var id = $(this).attr("data-id");
var val = $(this).val();

$("#text_color_input").val(val);


});

jQuery("#text_color_input").on("change", function(){

var id = $(this).attr("data-id");
var val = $(this).val();

$("#text_color_picker").val(val);

});

jQuery(".del_img").on("click", function(){

$(".bg_img_wrap").hide();
$("#bg_img_src").val("");

});


});

var glob_options = <? echo json_encode($polls_options); ?> 


var glob_labels = [];
var glob_data =  [];
var glob_colors = [];

for(var color in glob_options.chart_color){
  glob_colors.push(glob_options.chart_color[color]);
}
if(glob_colors.length == 0){
glob_colors = <?php echo "['".implode("','", $def_color)."']"; ?>;
}

var q_count = Number(<?php echo count($poll_answers); ?>) || 5;

for(var i = 0; i<q_count; i++){
  
  glob_labels.push("name_"+(i+1));
  glob_data.push(10*(i+1));
  
}


</script>
<br/><br/>
<span><?php echo __('Just for example:', 'lp_text');?></span>
<div style="width:400px; background-color:<?php if(isset($polls_options->bgcolor)){ echo esc_html($polls_options->bgcolor); }?>; background-size: cover; background-origin: border-box; background-repeat: no-repeat; background-image: url(<?php echo esc_html($polls_options->image);?>);">
<?php 
if(file_exists(plugin_dir_path(__DIR__).'chart/'.$polls[0]->chart_type.'/index.php')){
require(plugin_dir_path(__DIR__).'chart/'.$polls[0]->chart_type.'/index.php'); 
}

?>
<div>


<?php } } ?>