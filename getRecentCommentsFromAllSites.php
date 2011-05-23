<?php
/*
Plugin Name: Get All Comments Widget
Plugin URI: http://kanedo.net/projekte/get-all-comments-widget/
Description: A widget to display all recent comments across all sites of a multi site wordpress installation
Author: Gabriel Bretschner
Version: 1.2.1
Author URI: http://kanedo.net
*/
class CommentsSite extends WP_Widget {
	function CommentsSite() {
		$widget_ops = array('classname' => 'widgets_comments_across_all_sites', 'description' => 'Display comments from all sites' );
		$this->WP_Widget('comments_site', 'Comments Site', $widget_ops);
	}
	function widget($args, $instance) {
		extract($args, EXTR_SKIP);
		global $wpdb;
		$number = 8; // maximum number of comments to display
		$selects = array();
		$sites = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM wp_blogs ORDER BY blog_id" ) );
		foreach ($sites as $blog){
			if($blog->blog_id == 1){
				$pre = "";
			}else{
				$pre = $blog->blog_id."_";
			}
			// select only the fields you need here!
			$selects[] = "(SELECT ID, comment_post_ID, comment_author, comment_author_email, comment_date_gmt, comment_author_url, comment_content, post_title, {$blog->blog_id} as blog_id FROM {$wpdb->base_prefix}{$pre}comments
			  LEFT JOIN {$wpdb->base_prefix}{$pre}posts
			  ON comment_post_id = id
			  WHERE post_status = 'publish'
				AND post_password = ''
				AND comment_approved = '1'
				AND comment_type = ''
			   ORDER BY comment_date_gmt DESC LIMIT {$number})"; // real number is (number * # of blogs)
		}
	 	
	 		$count = $instance['count'];
			$comments = $wpdb->get_results(implode(" UNION ALL ", $selects)." ORDER BY comment_date_gmt DESC LIMIT 0, {$count}", OBJECT);
			
			$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
			
			echo $before_widget;			
			echo $before_title . $title . $after_title;	
			echo("<ul>");		
			foreach($comments as $comment){
				?>
				<li>
				<?php if($comment->comment_author_url != ""){?>
					<a href="<?php echo $comment->comment_author_url; ?>"><?php echo $comment->comment_author; ?></a>
					<?php
				}else{
					 echo $comment->comment_author;
				} 
					echo " ";
					echo _e("at", 'get-all-comments-widget');
				?>  <a href="<?php echo get_blog_permalink($comment->blog_id, $comment->ID);?>"> <?php echo $comment->post_title; ?></a>
				</li>
				<?php
				
			}
			echo("</ul>");
			echo $after_widget;

	}
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = strip_tags($new_instance['count']);
		return $instance;
	}
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'count' => ''));
		$title = strip_tags($instance['title']);
		$count = strip_tags($instance['count']);
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php echo _e('Title', 'get-all-comments-widget');?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('count'); ?>"><?php echo _e('Number of comments shown', 'get-all-comments-widget') ?>: <input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo attribute_escape($count); ?>" /></label></p>
		<p><small><?php echo _e('Visit', 'get-all-comments-widget'); ?>:&nbsp;<a href="http://kanedo.net?pk_campaign=Plugin&pk_keyword=get-all-comments-widget">kanedo.net</a></small></p>
		<?php
	}
}

function register_CommentsSite(){
	register_widget('CommentsSite');
	$plugin_dir = WP_PLUGIN_URL."/".basename(dirname(__FILE__));	
	load_plugin_textdomain( 'get-all-comments-widget', $plugin_dir."/language"/*'wp-content/plugins/all-comments-widget/language'*/);
}
add_action('init', 'register_CommentsSite', 1);
