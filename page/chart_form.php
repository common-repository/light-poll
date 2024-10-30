<?php if ($current_poll_id != "") {
            if (count($poll) > 0) {
                if (is_user_logged_in() || (!is_user_logged_in() && $poll[0]->{'user_type'} == "unregistered")) {

                    if (!is_user_logged_in()) {
                        $current_user = (object) array();
                        $current_user->display_name = "visitor";
                        $current_user->roles = "visitor";
                        $user_id = 0;
                    } else {
                        $current_user = wp_get_current_user();
                    }
                    if (in_array($poll[0]->{'user_type'}, (array) $current_user->roles) || in_array('administrator', (array) $current_user->roles) || $poll[0]->{'user_type'} == "unregistered") {
                        if (count($poll_answers) > 0) {
                            if (count($poll_user_answer) == 0 || !is_user_logged_in()) {
                                ?>

                                <form action="<?php echo get_admin_url() . 'admin-post.php'; ?>" method="POST" class="lp_form"
                                      data-id="<?php echo esc_html($current_poll_id); ?>">
                                    <div class="lp_qst" style="margin-bottom: 10px;"><?php echo esc_html($poll[0]->{'question'}); ?></div>
                                    <input hidden style="display: none;" name="action" value="save_vote">

                                    <input type="text" hidden style="display: none;" name="user_name" value="<?php echo esc_html($current_user->display_name); ?>">
                                    <?if(!is_user_logged_in()) { ?>
                                    <span>
                                    <label><?php echo __('Email:', 'lp_text');?></label><br/>
                                    <input type="text" name="user_email" required>
                                    </span>
                                    <br/> <br/>
                            <?php } else { ?>
                                   
                                    <input type="text" hidden style="display: none;" name="user_email" value="<?php echo esc_html($current_user->user_email); ?>">
                                    
                            <?php } ?>

                                <input name="user_id" hidden style="display: none;" value="<?php echo esc_html($user_id); ?>" type="text">
                                <input name="question_id" hidden style="display: none;" value="<?php echo esc_html($current_poll_id); ?>" type="text">
                                <div class="lp_answ_wrap" style="margin-bottom: 10px;">
                            <?php
                            foreach ($poll_answers as &$value) {
                                ?>
                                       <span class="lp_answ" style="display:block">
                                        <input type="radio" data-id="answ_<?php echo esc_html($value->{'id'}); ?>" name="answer_id"
                                               value="<?php echo esc_html($value->{'id'}); ?>">

                                        <label><?php echo esc_html($value->{'answer'}); ?></label>
                                        </span>

                                <?php
                            }
                            ?>
                                </div>
                                <? if(!is_user_logged_in() && $poll[0]->{'user_type'} == "unregistered"){ ?>

                                <button type="button" onclick="lp_send(event);" class="lp_send_button_1"><?php echo __('Submit', 'lp_text');?></button>

                                <? }else{ ?>
                                <button type="submit" class="lp_send_button_2"><?php echo __('Submit', 'lp_text');?></button>

                        <?php } ?>
                            <br />
                            <div class="lp_send_status"></div>

                            <br />
                            <?php if ($current_poll_view == "show") { ?>
                                <a href="<?php echo admin_url() . 'admin-post.php?action=lgpoll_page&id=' . $current_poll_id; ?>"><?php echo __('Chart', 'lp_text'); ?></a>
                        <?php } ?>
                        <br />
                        </form>

                        <script>
                            function lp_send(e) {
                                e.preventDefault();
                                var form = e.target.closest("form");
                                var form_data = new FormData(form);
                                form_data.append("lp_unreg", true);
                                var xhr = new XMLHttpRequest();
                                XMLHttpRequest.responseType = "document";
                                xhr.open("POST", "<?php echo get_admin_url() . 'admin-post.php'; ?>", true);

                                form.getElementsByClassName("lp_send_status")[0].innerHTML = "Sending...";

                                xhr.onreadystatechange = function () {
                                    if (xhr.readyState == XMLHttpRequest.DONE && xhr.status == 200) {

                                        if (this.responseText == "voted") {
                                            form.getElementsByClassName("lp_send_status")[0].innerHTML =
                                                    "<span class='lp_voted'><?php echo __('You have already voted!', 'lp_text');?></span>";
                                        } else if (this.responseText == "notemail") {
                                            form.getElementsByClassName("lp_send_status")[0].innerHTML =
                                                    "<span class='lp_notemail'><?php echo __('Wrong email!', 'lp_text');?></span>";
                                        } else if (this.responseText == "nodata") {
                                            form.getElementsByClassName("lp_send_status")[0].innerHTML =
                                                    "<span class='lp_nodata'><?php echo __('Fill all fields!', 'lp_text');?></span>";
                                        } else if (this.responseText == "ok") {
                                            form.getElementsByClassName("lp_send_status")[0].innerHTML =
                                                    "<span class='lp_sendok'><?php echo __('Your vote has been counted!', 'lp_text');?></span>";
                                        } else if (this.responseText == "wrongdata") {
                                            form.getElementsByClassName("lp_send_status")[0].innerHTML =
                                                    "<span class='lp_sendok'><?php echo __('Wrong data!', 'lp_text');?></span>";
                                        } else {

                                            form.getElementsByClassName("lp_send_status")[0].innerHTML = "<span class='lp_sendinfo'>" + this
                                                    .responseText + "</span>";
                                        }

                                    }
                                }
                                xhr.send(form_data);
                            }
                            ;
                        </script>


                        <?php } else {
                        ?>

                        <div class="lp_block">
                        <span class="lp_info_1"><?php echo __('You are voted.', 'lp_text'); ?></span><br />
                        <?php if ($current_poll_view == "show") { ?>
                                <a href="<?php echo admin_url() . 'admin-post.php?action=lgpoll_page&id=' . $current_poll_id; ?>"><?php echo __('Chart', 'lp_text'); ?></a>
                        <?php } ?>
                        </div>


                    <?php
                    }
                }
            } else { ?>
<div class="lp_block">
<span class="lp_info_2"><?php echo __('You have no access for it.', 'lp_text'); ?></span>
</div>
        <?php
            }
        } else {
            ?>
<div class="lp_block">
<span class="lp_info_3"><?php echo __('You are not logged user. Please log in for poll', 'lp_text');?></span>
</div>
            <?php
        }
    }
} else{
?>

<span class="lp_info_4"><?php echo __('No poll is selected.', 'lp_text');?></span>

<?php
} ?>