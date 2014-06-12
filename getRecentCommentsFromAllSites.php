<?php
/*
Plugin Name: Get All Comments Widget
Plugin URI: http://kanedo.net/projekte/get-all-comments-widget/
Description: A widget to display all recent comments across all sites of a multi site wordpress installation
Author: Gabriel Bretschner
Version: 1.3.2
Author URI: http://kanedo.net
*/
class CommentsSite extends WP_Widget {
  static function isValidEnviroment(){
    if(!is_multisite()){
      return false;
    }
    return true;
  }


	function CommentsSite() {
		$widget_ops = array('classname' => 'widgets_comments_across_all_sites', 'description' => 'Display comments from all sites' );
		$this->WP_Widget('comments_site', 'Comments Site', $widget_ops);
	}

  /**
   * render the actual widget
   */
	function widget($args, $instance) {
		extract($args, EXTR_SKIP);
		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
		echo $before_widget;
		echo $before_title . $title . $after_title;
    echo("<ul>");
    foreach ($this->getComments($instance['count']) as $comment) {
      ?>
      <li>
        <?php if ($comment->comment_author_url != "") { ?>
          <a href="<?php echo $comment->comment_author_url; ?>"><?php echo $comment->comment_author; ?></a>
        <?php
        } else {
          echo $comment->comment_author;
        }
        echo " ";
        echo _e("on", 'get-all-comments-widget');
        ?>  <a
            href="<?php echo get_blog_permalink($comment->blog_id, $comment->ID); ?>"> <?php echo $comment->post_title; ?></a>
      </li>
    <?php
    }
    echo("</ul>");
    echo $after_widget;
	}

  /**
   * retrieve comments accross all blogs of a multisite
   * returns an array ob Objects
   * @param int (optional) number of comments to be retrieved (default: 10)
   * @return array|NULL
   */
  function getComments($count = 10){
    global $wpdb;

    $selects = array();
    $sites   = array();
    if(function_exists('wp_get_sites')){
      $sites = wp_get_sites();
    }
    foreach ($sites as $blog){
      $selects[] = $this->getSQLStatementForBlogId($blog['blog_id'], $count); // real number is (number * # of blogs)
    }

    return $wpdb->get_results(implode(" UNION ALL ", $selects)." ORDER BY comment_date_gmt DESC LIMIT 0, {$count}", OBJECT);
  }

  /**
   * returns prepared SQL Statement to get all comments from blog ID
   * @param int $id the blog id @see wp_get_sites()
   * @param int $number number of comments per blog
   * @return string SQL Query
   * @protected
   */
  function getSQLStatementForBlogId( $id, $number = 0 ){
    global $wpdb;

    if($id == 1){
      $pre = "";
    }else{
      $pre = $id."_";
    }
    $post_table_name = $wpdb->base_prefix.$pre."posts";
    $comments_table_name = $wpdb->base_prefix.$pre."comments";

    // select only the fields you need here!
    return $wpdb->prepare("(SELECT {$post_table_name}.`ID`, comment_post_ID, comment_author, comment_author_email, comment_date_gmt, comment_author_url, comment_content, {$post_table_name}.`post_title`, %d as blog_id FROM {$comments_table_name}
			  LEFT JOIN {$post_table_name}
			  ON comment_post_id = id
			  WHERE post_status = 'publish'
				AND post_password = ''
				AND comment_approved = '1'
				AND comment_type = ''
			   ORDER BY comment_date_gmt DESC LIMIT %d)",
        $id,
        $number); // real number is (number * # of blogs)
  }

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = strip_tags($new_instance['count']);
		return $instance;
	}
	function form($instance) {
    if(!self::isValidEnviroment()){
      return;
    }
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'count' => ''));
		$title = strip_tags($instance['title']);
		$count = strip_tags($instance['count']);
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php echo _e('Title', 'get-all-comments-widget');?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('count'); ?>"><?php echo _e('Number of comments shown', 'get-all-comments-widget') ?>: <input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo esc_attr($count); ?>" /></label></p>
		<p><small><?php echo _e('Visit', 'get-all-comments-widget'); ?>:&nbsp;<a href="http://kanedo.net?pk_campaign=Plugin&pk_keyword=get-all-comments-widget">kanedo.net</a></small></p>
		<?php
	}
}
//is_multisite
function register_CommentsSite(){
  if(!CommentsSite::isValidEnviroment()){
    echo "<div class='error'><p><strong>Get All Comments Widget</strong>: your site need to be a multisite installation in order for the plugin to work</p></div>";
    return;
  }
	register_widget('CommentsSite');
	load_plugin_textdomain( 'get-all-comments-widget', false, dirname( plugin_basename( __FILE__ ) ) ."/language");
}
add_action('init', 'register_CommentsSite', 1);
