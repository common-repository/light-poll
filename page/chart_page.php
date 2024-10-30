<?php

$user = wp_get_current_user();

global $wpdb;
global $table_1;
$table_1 = $wpdb->prefix . 'light_poll_list';
global $table_2;
$table_2 = $wpdb->prefix . 'light_poll_answers';
global $table_3;
$table_3 = $wpdb->prefix . 'light_poll_result';
global $poll_id;
$poll_id = $current_poll_id;

if (isset($poll_id) && is_numeric($poll_id)) {

$polls = $wpdb->get_results("SELECT * FROM  $table_1 WHERE id=$poll_id");
global $poll_uniq_name;
$poll_uniq_name = str_replace(" ", "_", $polls[0]->name).'_'.$poll_id;
if(count($polls)>0){
if (is_user_logged_in() || (!is_user_logged_in() && $polls[0]->{'user_type'} == "unregistered")) {

    $poll_answers = $wpdb->get_results("SELECT * FROM $table_2 WHERE question_id=$poll_id");

    $polls_options = json_decode($polls[0]->options);

    $poll_answers_obj = (object) [];
    $total_voters = 0;
    $glob_labels = [];
    $glob_data = [];
    $glob_colors = [];
    foreach ($poll_answers as &$value) {

        $answer_id = $value->{"id"};
        $answer_results = $wpdb->get_results("SELECT * FROM $table_3 WHERE answer_id=$answer_id AND question_id=$poll_id");
        $poll_answers_obj->{$value->{"id"}} = (object) [];
        $poll_answers_obj->{$value->{"id"}}->name = $value->{"answer"};
        $poll_answers_obj->{$value->{"id"}}->id = $value->{"id"};
        $poll_answers_obj->{$value->{"id"}}->count = count($answer_results);
        $total_voters += count($answer_results);
        array_push($glob_labels, $value->{"answer"});
        array_push($glob_data, count($answer_results));
        if (isset($polls_options->chart_color)) {
        array_push($glob_colors, $polls_options->chart_color->{$value->{"id"}});
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
        <head>
                  <?php
                    wp_register_script('lgpoll_chartjs', plugin_dir_url(__FILE__) . '../lib/chart.min.js');
                    wp_enqueue_script('lgpoll_chartjs');
                    wp_head();
                    ?>
        </head>
        <body  style="background-color:<?php if (isset($polls_options->bgcolor)) { echo $polls_options->bgcolor; } ?>; 
        background-image: <?php if (isset($polls_options->bgcolor)) { echo 'url('.$polls_options->image.')'; } else { echo 'none'; } ?>;">
            <?php
            if (isset($polls_options->header)) {
                if ($polls_options->header == "yes") {
                    ?>
                    <h1 class="lp_header"><?php echo esc_html($polls[0]->question); ?></h1>
                <?php
                }
            }else{
                ?>
                <h1 class="lp_header"><?php echo esc_html($polls[0]->question); ?></h1>
            <?php

            }
            ?>
            <script>

                var glob_options = <?php echo json_encode($polls_options); ?>;
                var glob_labels = <?php echo json_encode($glob_labels); ?>;
                var glob_data = <?php echo json_encode($glob_data); ?>;
                var glob_colors = <?php echo json_encode($glob_colors); ?>;

            </script>
         

            <?php
            if (file_exists(plugin_dir_path(__DIR__) . 'chart/' . $polls[0]->chart_type . '/index.php')) {
                require(plugin_dir_path(__DIR__) . 'chart/' . $polls[0]->chart_type . '/index.php');
            }
            ?>
            <br/><br/>
            <div class="lp_total_wrap"><strong class="lp_total"><?php echo __('Total:', 'lp_text'); ?></strong> <?php echo esc_html($total_voters); ?><br/><br/>
                <div class="lp_results_wrap">
    <?php foreach ($poll_answers_obj as &$obj) { ?>

                        <span class="lp_result"><strong><?php echo esc_html($obj->name) . ':'; ?></strong> <?php echo esc_html($obj->count); ?> </span>

                        <?php
                    }
                    ?></div><br/>
                
                <div class="lp_form_wrap">
                    <?php
                    if (isset($polls_options->survey)) {
                        if ($polls_options->survey == "yes") {
                            $instance = ["poll_id" => $poll_id, "poll_view" => false];
                            the_widget('LightPoll_Widget', $instance);
                        }
                    }

                    $poll_width = 500;
                    if (isset($polls_options->chart_width)) {
                        $poll_width = esc_html($polls_options->chart_width);
                    }
                    ?>
                </div>

        </body>
        <style>
           
            body{
                padding: 10px;
                background-size: cover;
                background-origin: border-box; 
                background-repeat: no-repeat;
                color: <?php echo esc_html($polls_options->textcolor); ?>;
            }

             h1{
                text-align: center;
            }
            .container{
                margin: auto;
                width:<?php echo esc_html($poll_width); ?>px;
            }

            .lp_form_wrap{
                text-align: center;
            }

            .lp_form_wrap form{
                display: inline-block;
                padding:4px;
                text-align: left;
            }

            .lp_total_wrap{
                text-align: center;
            }

            .lp_result{
                padding: 10px;
            }

    <?php echo esc_html($polls[0]->style); ?>

        </style>
    </html>

<?php } } }?>