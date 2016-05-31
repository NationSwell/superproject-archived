<?php

class Bitly_Widget extends WP_Widget
{
  function Bitly_Widget()
  {
    $widget_ops = array('classname' => 'Bitly_Widget', 'description' => 'Displays Bitly bitmarks (your most popular or recent bitmarks, or the top results from a search of all currently popular bitly links)' );

    // title
    $this->WP_Widget('Bitly_Widget', 'Bitly Bitmarks', $widget_ops);
  }

  function form($instance)
  {
    // include jquery
    wp_enqueue_script('jquery');

    // include our js script
    wp_enqueue_script('bitly-js', plugins_url('/js/bitly.js',__FILE__) );

    $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'num_results' => 5, 'sort_by' => 'popular', 'search' => '' ) );
    $title = $instance['title'];
    $num_results = $instance['num_results'];
    $sort_by = $instance['sort_by'];
    $search = $instance['search'];
?>
  <p>
    <label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label>
  </p>

  <p>
    <label for="<?php echo $this->get_field_id('num_results'); ?>">Number of Bitmarks to show:</label>
    <input id="<?php echo $this->get_field_id('num_results'); ?>" name="<?php echo $this->get_field_name('num_results'); ?>" type="text" value="<?php echo $num_results; ?>" size="3" class="bitly-admin-num-results" />
    </p>

    <p>
    <label for="<?php echo $this->get_field_id('sort_by'); ?>">Sort by:</label>
    <select class="bitly_widget_type_select" name="<?php echo $this->get_field_name('sort_by'); ?>" id="<?php echo $this->get_field_id('sort_by'); ?>">
        <option value="popular" <?php selected( 'popular', $sort_by ); ?>>Most Popular</option>
        <option value="recent" <?php selected( 'recent', $sort_by ); ?>>Most Recent</option>
        <option value="search" <?php selected( 'search', $sort_by ); ?>>Custom Search</option>
    </select>

  </p>
    <p <?php if ( $sort_by != 'search' ) { ?> style="display:none;"<?php } ?> class="sort_by_search">
          <label for="<?php echo $this->get_field_id('sort_by'); ?>">Search term:</label>
          <input id="<?php echo $this->get_field_id('search'); ?>" name="<?php echo $this->get_field_name('search'); ?>" type="text" value="<?php echo $search; ?>" />
  </p>
<?php
  }

  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
    $instance['num_results'] = $new_instance['num_results'];
    $instance['sort_by'] = $new_instance['sort_by'];

    if ( $instance['sort_by'] == 'search' )
    {
        $instance['search'] = $new_instance['search'];
    }
    else
    {
        $instance['search'] = '';
    }

    return $instance;
  }

  function get_bitly_titles($links){
      global $wpdb, $bitly;

      $needLongUrls = array();
      $infos = $bitly->info($links);
      //ensure that it's an array
      $infos = is_array($infos) ? $infos : array($infos);
      foreach($infos as $info){
          // if valid then cache for 365 days, as titles shouldn't change
          if ( isset($info['title']) ){
              // decode our title
              $decoded_title = urldecode($info['title']);
              set_transient( 'bitly_url_title_' . $info['short_url'], $decoded_title, 86400 * 365 );
          }else{
              set_transient( 'bitly_url_title_' . $info['short_url'], '', 86400 * 365 );
              $needLongUrls[] = $info['short_url'];
          }
      }
      return($needLongUrls);
  }

  function get_bitly_long_urls($links){
      global $wpdb, $bitly;

      $infos = $bitly->expand($links);
      //ensure that it's an array
      $infos = is_array($infos) ? $infos : array($infos);
      foreach($infos as $info){
          // if valid then cache for 365 days, as titles shouldn't change
          if($info['long_url'] && strlen($info['long_url']) > 0){
              set_transient( 'bitly_url_long_' . $info['short_url'], $info['long_url'], 86400 * 365 );
          }
      }
  }


    function format_bitly_data($bitly_links)
    {
        global $wpdb, $bitly;

        $statistics = '';
      $needTitles = array();
      $needLongUrls = array();
        // loop through!

      // FIRST PASS -----------------------------------
        // first pass, check which links we have info for in the cache

        //ensure that it's an array
        $bitly_links = is_array($bitly_links) ? $bitly_links : array($bitly_links);
      foreach ( $bitly_links as $data )
      {
            // check cache for title
            $title = get_transient( 'bitly_url_title_' . $data['link'] );
            if($title === false){
                $needTitles[] = $data;
            }
      }
      // now get the titles we need and add them to the cache
      $linkbuffer = array();
      $linkcounter = 0;
      foreach ($needTitles as $nt){
          // We can only get 15 at once from bitly, set up a counter
          $linkcounter++;
          $linkbuffer[] = $nt['link'];
          if($linkcounter == 15){
              // We've got 15 links
              // Get their titles and add them to the cache.
              // This function returns any links which did not have titles,
              // which we add to the array of bitmarks we need long URLs for
              $needLongUrls = array_merge($needLongUrls, $this->get_bitly_titles($linkbuffer));
              // clear the linkbuffer
              $linkbuffer = array();
              $linkcounter = 0;
              // Pause execution for 2 sec to ease up on the bitly API
              sleep(2);
          }
      }
      // grab titles for any links left in the buffer.
      if(count($linkbuffer) > 0){
          $needLongUrls = array_merge($needLongUrls, $this->get_bitly_titles($linkbuffer));
      }

      // SECOND PASS -------------------------------------------
        // second pass, get needed long URLs in batches of 15
      // now get the titles we need and add them to the cache
      $linkbuffer = array();
      $linkcounter = 0;
      foreach ($needLongUrls as $nlu){
          $linkcounter++;
          $linkbuffer[] = $nlu;
          if($linkcounter == 15){
              // We've got 15 links (the max bitly lets us expand at once)
              // Get their long URLs and add them to the cache.
              // this function does not return anything.
              $this->get_bitly_long_urls($linkbuffer);
              // clear the linkbuffer
              $linkbuffer = array();
              $linkcounter = 0;
              // Pause execution for 2 sec to ease up on the bitly API
              sleep(2);
          }
      }
      // grab long_urls for any links left in the buffer.
      if(count($linkbuffer) > 0){
          $this->get_bitly_long_urls($linkbuffer);
      }
        // final pass, write out the HTML to a variable to be returned
        foreach ( $bitly_links as $data )
        {
            // get the title from the cache
            $title = get_transient( 'bitly_url_title_' . $data['link'] );
            $long_url = get_transient( 'bitly_url_long_' . $data['link']);

            // title found in our cache
            if ( strlen($title) > 0 ){
                $statistics .= "<li style='margin-bottom:2px;'><a href='" . $data['link'] . "'>" . $title . "</a></li>";
            }else if(strlen($long_url)){ // only occurs if bitly doesn't have a title for a URL
                // echo("getting transient for bitly_url_long_" . $data['link'] . " - " . $long_url . "<br>");
                if ( strlen($long_url) > 32 ){
                    $long_url = str_replace('http://', '', $long_url);
                    $long_url = str_replace('https://', '', $long_url);

                    //take 8 characters to the left
                    $first_url_slice = substr($long_url,0,14);

                    //take 8 characters from the end
                    $second_url_slice = substr($long_url,-8);

                    $long_url = $first_url_slice . "..." . $second_url_slice;
                }
                $statistics .= "<li style='margin-bottom:2px;'><a href='" . $data['link'] . "'>" . $long_url . "</a></li>";
            }else{
                $statistics .= "<li style='margin-bottom:2px;'><a href='" . $data['link'] . "'>" . $data['link'] . "</a></li>";
            }
        }

        if ( strlen($statistics) > 0 )
        {
            return '<ul>' . $statistics . '</ul>';
        }

        return '';
    }

    function format_search_results($bitly_links)
    {
        global $bitly;

        $statistics = '';
        //ensure that it's an array
        $bitly_links = is_array($bitly_links) ? $bitly_links : array($bitly_links);
        foreach ( $bitly_links as $data )
        {
            $statistics .= "<li style='padding-bottom:2px;'><a href='" . $data['aggregate_link'] . "'>" . $data['title'] . "</a></li>";
        }

        if ( strlen($statistics) > 0 )
        {
            return '<ul>' . $statistics . '</ul>';
        }

        return '';
    }

    function widget($args, $instance)
    {
        global $bitly, $wpdb;

        extract($args, EXTR_SKIP);

        echo $before_widget;
        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
        $num_results = $instance['num_results'];
        $search = $instance['search'];

        // error checking
        if ( !is_numeric($num_results) || intval($num_results) <= 0 )
            $num_results = 5;

        if (!empty($title))
            echo $before_title . $title . $after_title;

        $statistics = '';

        // do work
        switch ($instance['sort_by'])
        {
            case 'search':
                $data = $bitly->get_search_cache($num_results, urlencode($search));
                $statistics .= $this->format_search_results($data['results']);
                break;

            case 'recent':
                $data = $bitly->get_most_recent_cache($num_results);
                $statistics .= $this->format_bitly_data($data['link_history']);
                break;

            // most popular is our default
            case 'popular';
            default:
                $data = $bitly->get_most_popular_cache($num_results);
                $statistics .= $this->format_bitly_data($data['popular_links']);
                break;
        }

        if ( strlen($statistics) == 0 )
        {
            echo 'No click data<br \>';
        }
        else
        {
            echo $statistics;
        }

        echo $after_widget;
    }
}

add_action( 'widgets_init', create_function('', 'return register_widget("Bitly_Widget");') );?>
