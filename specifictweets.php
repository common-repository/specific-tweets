<?php
/*
Plugin Name: Specific Tweets
Plugin URI: http://protoco.de/project/specific-tweets/
Description: Show your tweets in a widget, but only tweets containing a certain phrase of your choosing. Added ability to change text color, show username, and show the user's avatar. Next up: caching
Version: 0.5
Author: Protocode
Author URI: http://protoco.de
License: A "Slug" license name e.g. GPL2

Copyright 2012  Zach Silveira  (email : zackify@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
/* Create admin menu for settings */
/**
 * Adds Specific_Tweets widget.
 */
class Specific_Tweets extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'specific_tweets', // Base ID
			'Specific Tweets', // Name
			array( 'description' => __( 'Show specific tweets', 'text_domain' ), ) // Args
		);
	}
	private function linkify($text){
		$text = preg_replace("@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@",'<a class="fancy" data-fancybox-type="iframe" href="$1">$1</a>',$text);
		$text = preg_replace("/@(\w+)/",'<a target="_blank" href="http://twitter.com/$1">@$1</a>',$text);
		$text = preg_replace("/#(\w+)/",'<a target="_blank" href="http://twitter.com/search/$1">#$1</a>',$text);
		return $text;

	}
	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		//grab the variables
		$title = apply_filters( 'widget_title', $instance['title'] );
		$twitter = $instance['twitter'];
		$term = $instance['term'];
		$color = $instance['color'];
		$count = $instance['count'];
		$show_twitter = $instance['show_twitter'];
		$show_avatar = $instance['show_avatar'];
		
		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		//encode the search info
		$search = urlencode($twitter . ' '.$term);
		$tweets = file_get_contents('http://search.twitter.com/search.json?q='.$search.'&rpp='.$count.'&result_type=recent');
		$tweets = json_decode($tweets);
		//if the api is down...
		if(empty($tweets)){
			echo 'The Twitter API appears to be down :O';
		}
		//if the api is up, but there are no results
		if(!empty($tweets) && empty($tweets->results)){
			echo 'No Tweets matching that term were found :(';
		}
		$first = 0;
			foreach($tweets->results as $tweet){
			$first++;
				//if this is the first item in the loop
				if($first == 1){
					if(!empty($show_avatar)){
					echo '<a target="_blank" href="http://twitter.com/'.$twitter.'"><img width="50px" height="50px" style="border-radius:5px;-webkit-border-radius:5px;-moz-border-radius:5px; margin-right:5px;"  src="'.$tweet->profile_image_url.'" /></a>';
					}
					if(!empty($show_twitter)){
					echo '<div style="font-size:14px;padding-top:5px;">From <a target="_blank" href="http://twitter.com/'.$twitter.'">@'.$twitter.'</a>:</div>';
					}
				}
				if(isset($color)){
					echo '<div style="color:'.$color.'">'.$this->linkify($tweet->text) . '</div>';
				}
				else{
					echo '<div>'.$this->linkify($tweet->text) . '</div>';
				}

			}
		
		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['twitter'] = strip_tags( $new_instance['twitter'] );
		$instance['term'] = strip_tags( $new_instance['term'] );
		$instance['color'] = strip_tags( $new_instance['color'] );
		$instance['show_twitter'] = strip_tags( $new_instance['show_twitter'] );
		$instance['show_avatar'] = strip_tags( $new_instance['show_avatar'] );
		$instance['count'] = strip_tags( $new_instance['count'] );
		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'twitter' ] ) ) {
			$twitter = $instance[ 'twitter' ];
		}
		else {
			$twitter = __( 'zackify', 'text_domain' );
		}
		if ( isset( $instance[ 'term' ] ) ) {
			$term = $instance[ 'term' ];
		}
		else {
			$term = __( '#blog', 'text_domain' );
		}
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Specific Tweets', 'text_domain' );
		}
		if ( isset( $instance[ 'color' ] ) ) {
			$color = $instance[ 'color' ];
		}
		else {
			$color = __( '', 'text_domain' );
		}
		if ( isset( $instance[ 'count' ] ) ) {
			$count = $instance[ 'count' ];
		}
		else {
			$count = __( '5', 'text_domain' );
		}
		if ( isset( $instance[ 'show_twitter' ] ) ) {
			$show_twitter = $instance[ 'show_twitter' ];
			if($show_twitter == 'on'){
				$show_twitter = 'checked="checked"';
			}
			else{
				$show_twitter = '';
			}
		}
		else {
			$show_twitter = __( '', 'text_domain' );
		}
		if ( isset( $instance[ 'show_avatar' ] ) ) {
			$show_avatar = $instance[ 'show_avatar' ];
			if($show_avatar == 'on'){
				$show_avatar = 'checked="checked"';
			}
			else{
				$show_avatar = '';
			}
		}
		else {
			$show_avatar = __( '', 'text_domain' );
		}
?>
		<p>Use this plugin to grab specific tweets from anyone by using a word or prase, ex: '#site', 'hello','gaming'.</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		<label for="<?php echo $this->get_field_id( 'twitter' ); ?>"><?php _e( 'Twitter Username:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'twitter' ); ?>" name="<?php echo $this->get_field_name( 'twitter' ); ?>" type="text" value="<?php echo esc_attr( $twitter ); ?>" />
		<label for="<?php echo $this->get_field_id( 'term' ); ?>"><?php _e( 'Search Term:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'term' ); ?>" name="<?php echo $this->get_field_name( 'term' ); ?>" type="text" value="<?php echo esc_attr( $term ); ?>" />
		<label for="<?php echo $this->get_field_id( 'color' ); ?>"><?php _e( 'HTML Color Code:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'color' ); ?>" name="<?php echo $this->get_field_name( 'color' ); ?>" type="text" value="<?php echo esc_attr( $color ); ?>" placeholder="#333" />
		<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( '# of Tweets to Show:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="text" value="<?php echo esc_attr( $count ); ?>" placeholder="5" /><br><br>
	
		<input  id="<?php echo $this->get_field_id( 'show_twitter' ); ?>" name="<?php echo $this->get_field_name( 'show_twitter' ); ?>" type="checkbox" <?php echo $show_twitter; ?>/> Show Twitter Name<br><br>
		<input  id="<?php echo $this->get_field_id( 'show_avatar' ); ?>" name="<?php echo $this->get_field_name( 'show_avatar' ); ?>" type="checkbox" <?php echo $show_avatar; ?>/> Show Avatar

		</p>
		<?php
	}

}
// register the widget
add_action( 'widgets_init', create_function( '', 'register_widget( "specific_tweets" );' ) );
