<?php
/*
  Plugin Name: YouTube widget responsive
  Description: Widgets responsive and shorcode to embed youtube in your sidebar or in your content, with all available options.
  Author: StefanoAI
  Version: 1.1.4
  Author URI: http://www.stefanoai.com
 */

class YouTubeResponsive extends \WP_Widget {

    static $footer = "";

    public function __construct() {
        parent::__construct(
                'youtube_responsive', // Base ID
                YOUTUBE_name, // Name
                array('description' => YOUTUBE_description,) // Args
        );
    }

    static function wp_head() {
        wp_enqueue_script('jquery');
        add_shortcode('youtube', array('YouTubeResponsive', 'shortcode'));
    }

    static function wp_footer() {
        ?><script type="text/javascript">
                    function AI_responsive_widget() {
                        jQuery('iframe.StefanoAI-youtube-responsive').each(function () {
                            var width = jQuery(this).parent().innerWidth();
                            var maxwidth = jQuery(this).css('max-width').replace(/px/, '');
                            var pl = parseInt(jQuery(this).parent().css('padding-left').replace(/px/, ''));
                            var pr = parseInt(jQuery(this).parent().css('padding-right').replace(/px/, ''));
                            width = width - pl - pr;
                            if (maxwidth < width) {
                                width = maxwidth;
                            }
                            jQuery(this).css('width', width + "px");
                            jQuery(this).css('height', width / (16 / 9) + "px");
                        });
                    }
                    if (typeof jQuery !== 'undefined') {
                        jQuery(document).ready(function () {
                            AI_responsive_widget();
                        });
                        jQuery(window).resize(function () {
                            AI_responsive_widget();
                        });
                    }
        <?php if (!empty(YouTubeResponsive::$footer)) { ?>
                        function onYouTubeIframeAPIReady() {
            <?php echo YouTubeResponsive::$footer; ?>
                        }
                        function StefanoAI_trackYoutubeVideo(state, video) {
                            if (typeof _config !== 'undefined') {
                                var forceSyntax = _config.forceSyntax || 0;
                            } else {
                                var forceSyntax = 0;
                            }
                            if (typeof window.dataLayer !== 'undefined' && !forceSyntax) {
                                window.dataLayer.push({
                                    'event': 'youTubeTrack',
                                    'attributes': {
                                        'videoUrl': video,
                                        'videoAction': state
                                    }
                                });
                            }
                            if (typeof window.ga === 'function' && typeof window.ga.getAll === 'function' && forceSyntax !== 2) {
                                window.ga('send', 'event', 'YoutubeWidgetResponsive', state, video, 0);
                            } else if (typeof window._gaq !== 'undefined' && forceSyntax !== 1) {
                                window._gaq.push(['_trackEvent', 'YoutubeWidgetResponsive', state, video]);
                            }
                        }
        <?php } ?>
        </script><?php
    }

    static function makeEmbedUrl($params) {
        if (!empty($params['track'])) {
            wp_enqueue_script("youtube-iframe-api", "https://www.youtube.com/iframe_api", false, false, true);
        }
        global $youtube_id;
        //https://www.youtu.be/_9tBFVKhn5o
        //https://www.youtube.com/watch?v=_9tBFVKhn5o
        //https://www.youtube.com/embed/_9tBFVKhn5o
        //https://www.youtube-nocookie.com/embed/_9tBFVKhn5o
        $idvideo = $idlist = NULL;
        preg_match('/youtu.be\/([^\/\?]+)/', $params['video'], $m);
        $idvideo = !empty($m[1]) ? $m[1] : null;
        if (empty($idvideo)) {
            preg_match('/(&|&amp;|\?|&#038;)v=([^&]+)/', $params['video'], $m);
            $idvideo = !empty($m[2]) ? $m[2] : null;
        }
        if (empty($idvideo)) {
            preg_match('/\/embed\/([^\/|\?]+)/', $params['video'], $m);
            $idvideo = !empty($m[1]) ? $m[1] : null;
        }


        if (preg_match('/(^|&|&amp;|\?|&#038;)list=([^&]+)/', $params['video'], $l)) {
            $idlist = !empty($l[2]) ? $l[2] : '';
            if (empty($idvideo)) {
                $idvideo = '';
            }
        } else if (empty($idvideo)) {
            $idvideo = !empty($m[2]) ? $m[2] : $params['video'];
        }

        if (empty($idlist) && !empty($params['list'])) {
            $idlist = $params['list'];
        }

        if (!empty($idvideo) || !empty($idlist)) {
            $w3c = !empty($params['w3c']) ? 1 : 0;
            $and = $w3c ? '&amp;' : '&';
            $idlist = !empty($idlist) ? $and . "list=$idlist" : '';
            $autohide = isset($params['autohide']) ? $and . "autohide=" . $params['autohide'] : '';
            $autoplay = !empty($params['autoplay']) ? $and . 'autoplay=1' : '';
            $cc_load = !empty($params['cc_load']) ? $and . 'cc_load_policy=1' : '';
            $cc_lang = !empty($params['cc_lang']) ? $and . 'hl=' . $params['cc_lang'] : '';
            $color = isset($params['color']) ? $and . 'color=' . $params['color'] : '';
            $controls = isset($params['controls']) ? $and . 'controls=' . $params['controls'] : '';
            $disablekb = isset($params['disablekb']) ? $and . 'disablekb=' . $params['disablekb'] : '';
            $endtime = (!empty($params['end_m']) ? intval($params['end_m']) * 60 : 0) + (!empty($params['end_s']) ? intval($params['end_s']) : 0);
            $end = !empty($endtime) ? $and . "end=$endtime" : '';
            $allowfullscreen = !empty($params['allowfullscreen']) ? 'allowfullscreen="true"' : '';
            $fs = !empty($params['allowfullscreen']) ? $and . 'fs=1' : $and . 'fs=0';
            $iv_load_policy = isset($params['iv_load_policy']) ? $and . 'iv_load_policy=' . $params['iv_load_policy'] : '';
            $loop = !empty($params['loop']) ? $and . 'loop=' . $params['loop'] : '';
            if (!empty($loop) && empty($idlist) && !empty($idvideo)) {
                $idlist = $and . "playlist=$idvideo";
            }
            $modestbranding = isset($params['modestbranding']) ? $and . 'modestbranding=' . $params['modestbranding'] : '';
            $rel = !empty($params['suggested']) && $params['suggested'] == '1' ? '' : $and . 'rel=0';
            $showinfo = !empty($params['showinfo']) && $params['showinfo'] == '1' ? '' : $and . 'showinfo=0';
            $starttime = (!empty($params['start_m']) ? intval($params['start_m']) * 60 : 0) + (!empty($params['start_s']) ? intval($params['start_s']) : 0);
            $start = (!empty($starttime)) ? $and . "start=$starttime" : "";
            $theme = isset($params['theme']) ? $and . 'theme=' . $params['theme'] : '';
            $quality = isset($params['quality']) ? $and . 'vq=' . $params['quality'] : '';
            $wmode = !empty($params['wmode']) ? $and . 'wmode=transparent' : '';
            $url = !empty($params['privacy']) && $params['privacy'] == '1' ? '//www.youtube-nocookie.com/embed/' : '//www.youtube.com/embed/';

            $class = isset($params['class']) ? esc_attr($params['class']) : '';
            $style = isset($params['style']) ? esc_attr($params['style']) : '';
            $maxw = !empty($params['maxw']) ? 'max-width:' . intval($params['maxw']) . 'px;' : '';
            @$id = ++$youtube_id;

            $track = !empty($params['track']) ? $and . "enablejsapi=1" . $and . "playerapiid=$id" : "";

            @$urlembed = "<iframe id='$id' class='StefanoAI-youtube-responsive $class' width='160' height='90' src='$url$idvideo?$idlist$autohide$autoplay$cc_load$cc_lang$color$controls$disablekb$end$fs$iv_load_policy$loop$modestbranding$rel$showinfo$start$theme$quality$wmode$track' frameborder='0' $allowfullscreen style='$maxw$style'></iframe>";
            if (!empty($params['track'])) {
                YouTubeResponsive::$footer.=<<<SCRIPT
                var player_$id;
                player_$id = new YT.Player('$id', {
                    events: {
                        'onStateChange': function () {
                            var d = player_$id.getVideoData();
                            switch (player_$id.getPlayerState()) {
                                case -1:
                                    //unstarted
                                    break;
                                case 0:
                                    //ended
                                    //StefanoAI_trackYoutubeVideo('Ended', d.title + " | " + d.video_id + " (" + d.author + ")");
                                    break;
                                case 1:
                                    //playing
                                    StefanoAI_trackYoutubeVideo('Playing', d.title + " | " + d.video_id + " (" + d.author + ")");
                                    break;
                                case 2:
                                    //paused
                                    //StefanoAI_trackYoutubeVideo('Paused', d.title + " | " + d.video_id + " (" + d.author + ")");
                                    break;
                                case 3:
                                    //buffering
                                    break;
                                case 5:
                                    //video cued (When a video is queued and ready for playback)
                                    break;
                            }
                        }
                    }
                });
SCRIPT;
            }
            return apply_filters('youtube_iframe', $urlembed);
        }
        return '';
    }

    function widget($args, $instance) {
        extract($args);
        $title = apply_filters('widget_title', $instance['title']);
        preg_match('/\?v=([^&]+)/', $instance['video'], $m);
        $urlembed = YouTubeResponsive::makeEmbedUrl($instance);
        if (!empty($urlembed)) {
            echo $before_widget;
            echo $before_title . $title . $after_title;
            echo $urlembed;
            echo $after_widget;
        }
    }

    function update($new_instance, $old_instance) {
//save widget settings
        $instance = array();
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['video'] = strip_tags($new_instance['video']);
        $instance['autohide'] = isset($new_instance['autohide']) && ($new_instance['autohide'] == 1 || $new_instance['autohide'] == 0) ? $new_instance['autohide'] : 2;
        $instance['autoplay'] = !empty($new_instance['autoplay']) ? 1 : 0;
        $instance['cc_load'] = !empty($new_instance['cc_load']) ? 1 : 0;
        $instance['cc_lang'] = strip_tags($new_instance['cc_lang']);
        $instance['color'] = !empty($new_instance['color']) && $new_instance['color'] == "white" ? 'white' : 'red';
        $instance['controls'] = isset($new_instance['controls']) && ( $new_instance['controls'] == 0 || $new_instance['controls'] == 2) ? $new_instance['controls'] : 1;
        $instance['disablekb'] = isset($new_instance['disablekb']) && $new_instance['disablekb'] == 1 ? 1 : 0;
        $instance['end_m'] = strip_tags($new_instance['end_m']);
        $instance['end_s'] = strip_tags($new_instance['end_s']);
        $instance['allowfullscreen'] = !empty($new_instance['allowfullscreen']) ? $new_instance['allowfullscreen'] : 0;
        $instance['iv_load_policy'] = !empty($new_instance['iv_load_policy']) && $new_instance['iv_load_policy'] == 3 ? 3 : 1;
        $instance['loop'] = !empty($new_instance['loop']) ? 1 : 0;
        $instance['modestbranding'] = !empty($new_instance['modestbranding']) ? 1 : 0;
        $instance['suggested'] = !empty($new_instance['suggested']) ? $new_instance['suggested'] : 0;
        $instance['showinfo'] = isset($new_instance['showinfo']) && $new_instance['showinfo'] == 0 ? 0 : 1;
        $instance['start_m'] = strip_tags($new_instance['start_m']);
        $instance['start_s'] = strip_tags($new_instance['start_s']);
        $instance['theme'] = !empty($new_instance['theme']) && $new_instance['theme'] == 'light' ? 'light' : 'dark';
        $instance['quality'] = !empty($new_instance['quality']) ? $new_instance['quality'] : 'default';
        $instance['class'] = !empty($new_instance['class']) ? $new_instance['class'] : '';
        $instance['style'] = !empty($new_instance['style']) ? $new_instance['style'] : '';
        $instance['maxw'] = !empty($new_instance['maxw']) ? $new_instance['maxw'] : '';
        $instance['w3c'] = isset($new_instance['w3c']) && $new_instance['w3c'] == 0 ? 0 : 1;
        $instance['privacy'] = !empty($new_instance['privacy']) ? $new_instance['privacy'] : 0;
        $instance['wmode'] = !empty($new_instance['wmode']) ? $new_instance['wmode'] : 0;
        $instance['track'] = !empty($new_instance['track']) ? $new_instance['track'] : 0;
        return $instance;
    }

    function form($instance) {
        $title = (isset($instance['title'])) ? $instance['title'] : '';
        $video = (isset($instance['video'])) ? $instance['video'] : '';
        $autohide = (isset($instance['autohide'])) ? $instance['autohide'] : 2;
        $autoplay = (isset($instance['autoplay'])) ? $instance['autoplay'] : 0;
        $cc_load = (isset($instance['cc_load'])) ? $instance['cc_load'] : 0;
        $cc_lang = (isset($instance['cc_lang'])) ? $instance['cc_lang'] : '';
        $color = !empty($instance['color']) && $instance['color'] == "white" ? 'white' : 'red';
        $controls = isset($instance['controls']) && ( $instance['controls'] == 0 || $instance['controls'] == 2) ? $instance['controls'] : 1;
        $disablekb = isset($instance['disablekb']) && $instance['disablekb'] == 1 ? 1 : 0;
        $end_m = (isset($instance['end_m'])) ? $instance['end_m'] : 0;
        $end_s = (isset($instance['end_s'])) ? $instance['end_s'] : 0;
        $allowfullscreen = !empty($instance['allowfullscreen']) ? $instance['allowfullscreen'] : 0;
        $iv_load_policy = !empty($instance['iv_load_policy']) && $instance['iv_load_policy'] == 3 ? 3 : 1;
        $loop = !empty($instance['loop']) ? 1 : 0;
        $modestbranding = !empty($instance['modestbranding']) ? 1 : 0;
        $suggested = !empty($instance['suggested']) ? $instance['suggested'] : 0;
        $showinfo = !empty($instance['showinfo']) ? 1 : 0;
        $start_m = (isset($instance['start_m'])) ? $instance['start_m'] : 0;
        $start_s = (isset($instance['start_s'])) ? $instance['start_s'] : 0;
        $theme = !empty($instance['theme']) && $instance['theme'] == 'light' ? 'light' : 'dark';
        $quality = !empty($instance['quality']) ? $instance['quality'] : 'default';
        $class = !empty($instance['class']) ? $instance['class'] : '';
        $style = !empty($instance['style']) ? $instance['style'] : '';
        $maxw = !empty($instance['maxw']) ? $instance['maxw'] : '';
        $w3c = !empty($instance['w3c']) ? 1 : 0;
        $privacy = !empty($instance['privacy']) ? $instance['privacy'] : 0;
        $wmode = !empty($instance['wmode']) ? $instance['wmode'] : 0;
        $track = !empty($instance['track']) ? $instance['track'] : 0;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo YOUTUBE_title ?>: </label> 
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('video'); ?>"><?php echo YOUTUBE_video ?>: </label> 
            <input class="widefat" id="<?php echo $this->get_field_id('video'); ?>" name="<?php echo $this->get_field_name('video'); ?>" type="text" value="<?php echo esc_attr($video); ?>" />
        </p>
        <p>
            <label><?php echo YOUTUBE_start_time ?>: </label> 
            <input class="widefat" style="width:30px;display: inline-block;" id="<?php echo $this->get_field_id('start_m'); ?>" name="<?php echo $this->get_field_name('start_m'); ?>" type="text" value="<?php echo esc_attr($start_m); ?>" /> <?php echo YOUTUBE_minutes ?>
            <input class="widefat" style="width:30px;display: inline-block;" id="<?php echo $this->get_field_id('start_s'); ?>" name="<?php echo $this->get_field_name('start_s'); ?>" type="text" value="<?php echo esc_attr($start_s); ?>" /> <?php echo YOUTUBE_seconds ?>
        </p>
        <p>
            <label><?php echo YOUTUBE_end_time ?>: </label> 
            <input class="widefat" style="width:30px;display: inline-block;" id="<?php echo $this->get_field_id('end_m'); ?>" name="<?php echo $this->get_field_name('end_m'); ?>" type="text" value="<?php echo esc_attr($end_m); ?>" /> <?php echo YOUTUBE_minutes ?>
            <input class="widefat" style="width:30px;display: inline-block;" id="<?php echo $this->get_field_id('end_s'); ?>" name="<?php echo $this->get_field_name('end_s'); ?>" type="text" value="<?php echo esc_attr($end_s); ?>" /> <?php echo YOUTUBE_seconds ?>
        </p>
        <p>
            <input  id="<?php echo $this->get_field_id('cc_load'); ?>" name="<?php echo $this->get_field_name('cc_load'); ?>" type="checkbox" value="1" <?php echo esc_attr($cc_load) == "1" ? 'checked' : ''; ?> />
            <label for="<?php echo $this->get_field_id('cc_load'); ?>"><?php echo YOUTUBE_cc_load ?> </label> <br/>
            <label for="<?php echo $this->get_field_id('cc_lang'); ?>"><?php echo YOUTUBE_cc_lang ?>: </label> 
            <input maxlength="2" style="width:30px" class="widefat" id="<?php echo $this->get_field_id('cc_lang'); ?>" name="<?php echo $this->get_field_name('cc_lang'); ?>" type="text" value="<?php echo esc_attr($cc_lang); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('autohide'); ?>"><?php echo YOUTUBE_autohide ?> </label> 
            <select id="<?php echo $this->get_field_id('autohide'); ?>" name="<?php echo $this->get_field_name('autohide'); ?>">
                <option value="2" <?php echo $autohide == 2 ? 'selected' : '' ?>><?php echo YOUTUBE_autohide_default ?></option>
                <option value="1" <?php echo $autohide == 1 ? 'selected' : '' ?>><?php echo YOUTUBE_autohide_hide_video_progress_bar_after_the_video_starts_playing ?></option>
                <option value="0" <?php echo $autohide == 0 ? 'selected' : '' ?>><?php echo YOUTUBE_autohide_show_always ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('theme'); ?>"><?php echo YOUTUBE_theme ?> </label> 
            <select id="<?php echo $this->get_field_id('theme'); ?>" name="<?php echo $this->get_field_name('theme'); ?>">
                <option value="dark" <?php echo $theme == 'dark' ? 'selected' : '' ?>><?php echo YOUTUBE_theme_dark ?></option>
                <option value="light" <?php echo $theme == 'light' ? 'selected' : '' ?>><?php echo YOUTUBE_theme_light ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('color'); ?>"><?php echo YOUTUBE_color ?> </label> 
            <select id="<?php echo $this->get_field_id('color'); ?>" name="<?php echo $this->get_field_name('color'); ?>">
                <option value="red" <?php echo $color == 'red' ? 'selected' : '' ?>><?php echo YOUTUBE_color_red ?></option>
                <option value="white" <?php echo $color == 'white' ? 'selected' : '' ?>><?php echo YOUTUBE_color_white ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('controls'); ?>"><?php echo YOUTUBE_controls ?> </label> 
            <select id="<?php echo $this->get_field_id('controls'); ?>" name="<?php echo $this->get_field_name('controls'); ?>">
                <option value="1" <?php echo $controls == 1 ? 'selected' : '' ?>><?php echo YOUTUBE_controls_always ?></option>
                <option value="2" <?php echo $controls == 2 ? 'selected' : '' ?>><?php echo YOUTUBE_controls_on_video_playback ?></option>
                <option value="0" <?php echo $controls == 0 ? 'selected' : '' ?>><?php echo YOUTUBE_controls_never ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('quality'); ?>"><?php echo YOUTUBE_resolution ?> </label> 
            <select id="<?php echo $this->get_field_id('quality'); ?>" name="<?php echo $this->get_field_name('quality'); ?>">
                <option value="default" <?php echo empty($quality) || $quality == 'default' ? 'selected' : '' ?>><?php echo YOUTUBE_autohide_default ?></option>
                <option value="small" <?php echo $quality == 'small' ? 'selected' : '' ?>>240px</option>
                <option value="medium" <?php echo $quality == 'medium' ? 'selected' : '' ?>>360px</option>
                <option value="large" <?php echo $quality == 'large' ? 'selected' : '' ?>>480px</option>
                <option value="hd720" <?php echo $quality == 'hd720' ? 'selected' : '' ?>>720px</option>
                <option value="hd1080" <?php echo $quality == 'hd1080' ? 'selected' : '' ?>>1080px</option>
                <option value="highres" <?php echo $quality == 'highres' ? 'selected' : '' ?>> &gt; 1080px</option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('class'); ?>">class: </label> 
            <input class="widefat" id="<?php echo $this->get_field_id('class'); ?>" name="<?php echo $this->get_field_name('class'); ?>" type="text" value="<?php echo esc_attr($class); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('style'); ?>">style: </label> 
            <input style="widefat" id="<?php echo $this->get_field_id('style'); ?>" name="<?php echo $this->get_field_name('style'); ?>" type="text" value="<?php echo esc_attr($style); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('maxw'); ?>">max-width: </label> 
            <input id="<?php echo $this->get_field_id('maxw'); ?>" name="<?php echo $this->get_field_name('maxw'); ?>" type="text" value="<?php echo esc_attr($maxw); ?>" style="width: 4em" /> px
        </p>
        <p>
            <input  id="<?php echo $this->get_field_id('loop'); ?>" name="<?php echo $this->get_field_name('loop'); ?>" type="checkbox" value="1" <?php echo esc_attr($loop) == "1" ? 'checked' : ''; ?> />
            <label for="<?php echo $this->get_field_id('loop'); ?>">Loop</label> 
        </p>
        <p>
            <input  id="<?php echo $this->get_field_id('allowfullscreen'); ?>" name="<?php echo $this->get_field_name('allowfullscreen'); ?>" type="checkbox" value="1" <?php echo esc_attr($allowfullscreen) == "1" ? 'checked' : ''; ?> />
            <label for="<?php echo $this->get_field_id('allowfullscreen'); ?>"><?php echo YOUTUBE_allowfullscreen ?> </label> 
        </p>
        <p>
            <input  id="<?php echo $this->get_field_id('disablekb'); ?>" name="<?php echo $this->get_field_name('disablekb'); ?>" type="checkbox" value="1" <?php echo esc_attr($disablekb) == "1" ? 'checked' : ''; ?> />
            <label for="<?php echo $this->get_field_id('disablekb'); ?>"><?php echo YOUTUBE_disablekb ?> </label> 
        </p>
        <p>
            <input  id="<?php echo $this->get_field_id('iv_load_policy'); ?>" name="<?php echo $this->get_field_name('iv_load_policy'); ?>" type="checkbox" value="3" <?php echo esc_attr($iv_load_policy) == "3" ? 'checked' : ''; ?> />
            <label for="<?php echo $this->get_field_id('iv_load_policy'); ?>"><?php echo YOUTUBE_hide_iv_load_policy ?> </label> 
        </p>
        <p>
            <input  id="<?php echo $this->get_field_id('autoplay'); ?>" name="<?php echo $this->get_field_name('autoplay'); ?>" type="checkbox" value="1" <?php echo esc_attr($autoplay) == "1" ? 'checked' : ''; ?> />
            <label for="<?php echo $this->get_field_id('autoplay'); ?>"><?php echo YOUTUBE_autoplay ?> </label> 
        </p>
        <p>
            <input  id="<?php echo $this->get_field_id('modestbranding'); ?>" name="<?php echo $this->get_field_name('modestbranding'); ?>" type="checkbox" value="1" <?php echo esc_attr($modestbranding) == "1" ? 'checked' : ''; ?> />
            <label for="<?php echo $this->get_field_id('modestbranding'); ?>"><?php echo YOUTUBE_modestbranding ?> </label> 
        </p>
        <p>
            <input  id="<?php echo $this->get_field_id('showinfo'); ?>" name="<?php echo $this->get_field_name('showinfo'); ?>" type="checkbox" value="0" <?php echo esc_attr($showinfo) == "0" ? 'checked' : ''; ?> />
            <label for="<?php echo $this->get_field_id('showinfo'); ?>"><?php echo YOUTUBE_hide_showinfo ?> </label> 
        </p>
        <p>
            <input  id="<?php echo $this->get_field_id('w3c'); ?>" name="<?php echo $this->get_field_name('w3c'); ?>" type="checkbox" value="1" <?php echo esc_attr($w3c) == "1" ? 'checked' : ''; ?> />
            <label for="<?php echo $this->get_field_id('w3c'); ?>">W3C standard </label> 
        </p>
        <p>
            <input  id="<?php echo $this->get_field_id('privacy'); ?>" name="<?php echo $this->get_field_name('privacy'); ?>" type="checkbox" value="1" <?php echo esc_attr($privacy) == "1" ? 'checked' : ''; ?> />
            <label for="<?php echo $this->get_field_id('privacy'); ?>"><?php echo YOUTUBE_privacy ?> </label> 
        </p>
        <p>
            <input  id="<?php echo $this->get_field_id('suggested'); ?>" name="<?php echo $this->get_field_name('suggested'); ?>" type="checkbox" value="1" <?php echo esc_attr($suggested) == "1" ? 'checked' : ''; ?> />
            <label for="<?php echo $this->get_field_id('suggested'); ?>"><?php echo YOUTUBE_suggested ?> </label> 
        </p>
        <p>
            <input  id="<?php echo $this->get_field_id('wmode'); ?>" name="<?php echo $this->get_field_name('wmode'); ?>" type="checkbox" value="1" <?php echo esc_attr($wmode) == "1" ? 'checked' : ''; ?> />
            <label for="<?php echo $this->get_field_id('wmode'); ?>">wmode transparent</label> 
        </p>
        <p>
            <input  id="<?php echo $this->get_field_id('track'); ?>" name="<?php echo $this->get_field_name('track'); ?>" type="checkbox" value="1" <?php echo esc_attr($track) == "1" ? 'checked' : ''; ?> />
            <label for="<?php echo $this->get_field_id('track'); ?>">Track video (Google Analytics/Universal Analytics)</label> 
        </p>
        <?php
    }

    static function shortcode($args) {
        if (!empty($args['video'])) {
            return YouTubeResponsive::makeEmbedUrl($args);
        }
    }

}

if (file_exists(plugin_dir_path(__FILE__) . "lang/" . get_locale() . '.php')) {
    include_once plugin_dir_path(__FILE__) . "lang/" . get_locale() . '.php';
} else {
    include_once plugin_dir_path(__FILE__) . "lang/en_US.php";
}

function register_youtuberesponsive_widgets() {
    register_widget('YouTubeResponsive');
}

add_action('widgets_init', 'register_youtuberesponsive_widgets');
add_action('wp_footer', array('YouTubeResponsive', 'wp_footer'), 99);
add_action('wp_head', array('YouTubeResponsive', 'wp_head'), 99);
