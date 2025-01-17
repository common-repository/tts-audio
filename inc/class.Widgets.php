<?php
// Register and load the widget
function ttsaudio_load_widget() {
	register_widget( 'TTSAudio_Playlist' );
}
add_action( 'widgets_init', 'ttsaudio_load_widget' );

class TTSAudio_Playlist extends WP_Widget {

	/**
	 * Sets up a new Recent Posts widget instance.
	 *
	 * @since 2.8.0
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'widget_ttsaudio_playlist',
			'description' => __( 'Your site\'s TTS Audio Posts.','ttsaudio' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'ttsaudio-playlist', __( 'TTS Audio Playlist','ttsaudio' ), $widget_ops );
		$this->alt_option_name = 'widget_ttsaudio_playlist';
	}

	/**
	 * Outputs the content for the current Recent Posts widget instance.
	 *
	 * @since 2.8.0
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current Recent Posts widget instance.
	 */
	public function widget( $args, $instance ) {
		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance['title'], $this->id_base );
		$skin = isset( $instance['skin'] ) ? $instance['skin'] : 'default';
		$cat = isset( $instance['cat'] ) ? $instance['cat'] : 0;
		$tags = isset( $instance['tags'] ) ? $instance['tags'] : '';
		$post_format = isset( $instance['post_format'] ) ? $instance['post_format'] : array();
		$include = isset( $instance['include'] ) ? $instance['include'] : '';
		$exclude = isset( $instance['exclude'] ) ? $instance['exclude'] : '';
		$orderby = isset( $instance['orderby'] ) ? $instance['orderby'] : 'ID';
		$order = isset( $instance['order'] ) ? $instance['order'] : 'DESC';
		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
		if ( ! $number ) $number = 5;
		$show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : false;

		/**
		 * Filters the arguments for the Recent Posts widget.
		 *
		 * @since 3.4.0
		 * @since 4.9.0 Added the `$instance` parameter.
		 *
		 * @see WP_Query::get_posts()
		 *
		 * @param array $args     An array of arguments used to retrieve the recent posts.
		 * @param array $instance Array of settings for the current widget.
		 */
		//extract($instance);
		$wp_query = array(
			'posts_per_page'      => $number,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'meta_query' => array(array('key' => 'ttsaudio_status','value' => 'enable','compare' => '='))
		);
		$wp_query['cat'] =  $cat;
		if ($tags != '') $wp_query['tag'] = trim($tags);

		if ( !empty( $post_format ) ) {
			$tax_query = array();
			foreach ($post_format as &$format) $formats[] = 'post-format-'.$format;
			$tax_query = array(array('taxonomy' => 'post_format', 'field'=> 'slug', 'terms'=> $formats, 'operator' => 'IN'));
			$wp_query['tax_query'] =  $tax_query;
		}

		if ($include != '') $wp_query['post__in'] = explode(',', $include);
		if ($exclude != '') $wp_query['post__not_in'] = explode(',', $exclude);
		$wp_query['orderby'] = $orderby;
		$wp_query['order'] = $order;

		$the_query = new WP_Query( apply_filters( 'widget_posts_args', $wp_query, $instance ) );

		if ( ! $the_query->have_posts() ) return;
		?>
		<?php
		echo $args['before_widget'];

		if ( $title ) echo $args['before_title'] . $title . $args['after_title'];

		$post_count = $the_query->post_count;
		?>

		<div id="plyr-<?php echo $args['widget_id'];?>" class="ttsaudio-plyr ttsaudio-plyr--<?php echo $skin;?> ttsaudio-plyr--playlist">
			<div class="plyr">
				<audio controls></audio>
				<div class="buttons">
					<span class="prev" name="previous">Prev</span>
					<span class="next" name="next">Next</span>
				</div>
			</div>

			<ul class="ttsaudio-plyr--playlist__list">
				<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
					<?php
					$title = ( ! empty( get_the_title() ) ) ? get_the_title() : __( '(no title)' );
					$mp3_url = get_post_meta( get_the_ID(), 'ttsaudio_option_custom_audio', true ) ? : add_query_arg( array('ttsaudio' => get_the_ID() ) , home_url() );
					?>
					<li data-id="<?php echo $the_query->current_post;?>" data-audio="<?php echo esc_url_raw( $mp3_url );?>">
						<?php echo '<b>'.str_pad( $the_query->current_post + 1, 2, "0", STR_PAD_LEFT ) .'</b>. '. $title ; ?>
						<?php if ( $show_date ) : ?>
							<span class="postdate"><?php echo get_the_date( '', get_the_ID() ); ?></span>
						<?php endif; ?>
					</li>
				<?php endwhile; ?>
				<?php wp_reset_postdata(); ?>
			</ul>
			<?php echo TTSAudio::author();?>
		</div>
		<script>
			jQuery(function ($) {
				$('#plyr-<?php echo $args['widget_id'];?>').PlyrPlaylist({nextSongToShow:<?php echo $post_count;?>, prevSongToShow:<?php echo $post_count;?>});
			});
		</script>

		<?php
		echo $args['after_widget'];
	}

	/**
	 * Handles updating the settings for the current Recent Posts widget instance.
	 *
	 * @since 2.8.0
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Updated settings to save.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['skin'] = sanitize_text_field( $new_instance['skin'] );
		$instance['cat'] = (int) $new_instance['cat'];
		$instance['tags'] = sanitize_text_field( $new_instance['tags'] );
		if(!empty($new_instance['post_format']) && is_array($new_instance['post_format'])){
			$instance['post_format'] = array_map( 'sanitize_text_field', $new_instance['post_format'] );
		}
		$instance['include'] = sanitize_text_field( $new_instance['include'] );
		$instance['exclude'] = sanitize_text_field( $new_instance['exclude'] );
		$instance['orderby'] = sanitize_text_field( $new_instance['orderby'] );
		$instance['order'] = sanitize_text_field( $new_instance['order'] );
		$instance['number'] = (int) $new_instance['number'];
		$instance['show_date'] = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;
		return $instance;
	}

	/**
	 * Outputs the settings form for the Recent Posts widget.
	 *
	 * @since 2.8.0
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		$tts = new TTSAudio;
		$options = get_option( 'ttsaudio_options' );

		$title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$skin    = isset( $instance['skin'] ) ? $instance['skin'] : $options['plyr_skin'];
		$cat    = isset( $instance['cat'] ) ? absint( $instance['cat'] ) : 0;
		$tags    = isset( $instance['tags'] ) ? $instance['tags'] : '';
		$post_format    = isset( $instance['post_format'] ) ? $instance['post_format'] : array();
		$include    = isset( $instance['include'] ) ? $instance['include'] : '';
		$exclude    = isset( $instance['exclude'] ) ? $instance['exclude'] : '';
		$orderby    = isset( $instance['orderby'] ) ? $instance['orderby'] : '';
		$order    = isset( $instance['order'] ) ? $instance['order'] : '';
		$number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$show_date = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : false;
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('skin'); ?>"><?php esc_html_e('Skin', 'ttsaudio'); ?>:</label>
			<?php $skins = $tts->PlyrSkin();?>
			<select id="<?php echo $this->get_field_id('skin');?>" name="<?php echo $this->get_field_name('skin');?>">
				<?php foreach($skins as $key =>  $value){?>
				<option value="<?php echo $key;?>" <?php selected( $skin, $key);?>><?php echo $value;?></option>
				<?php } ?>
			</select></p>

		<p><label for="<?php echo $this->get_field_id('cat'); ?>"><?php _e('Category', 'ttsaudio'); ?>:</label>
		<?php
		$args = array(
			'show_option_all'	=> __('All Categories', 'ttsaudio'),
			'orderby'            => 'ID',
			'order'              => 'ASC',
			'show_count'         => 1,
			'selected'           => $cat,
			'hierarchical'       => 0,
			'name'               => $this->get_field_name('cat'),
			'id'                 => $this->get_field_id('cat'),
			'class'              => '',
		);
		wp_dropdown_categories( $args ); ?>
		</p>

		<p><label for="<?php echo $this->get_field_id('tags'); ?>"><?php _e('With tags', 'ttsaudio'); ?>:</label>
			<input name="<?php echo $this->get_field_name('tags'); ?>" id="<?php echo $this->get_field_id('tags'); ?>" value="<?php echo $tags; ?>" class="widefat" type="text" /><br />
			<em><?php _e('Separate tags with commas','ttsaudio');?></em></p>

			<?php
			if ( current_theme_supports( 'post-formats' ) ) {
		    $post_formats = get_theme_support( 'post-formats' );

		    if ( is_array( $post_formats[0] ) ) { $post_formats = $post_formats[0];
				?>
				<p><label for="<?php echo $this->get_field_id('post_format'); ?>"><?php _e('Post formats', 'ttsaudio'); ?>:</label>
					<?php $formats = get_theme_support( 'post-formats' ); $formats =  $formats[0];
					?>
					<?php foreach($post_formats as $format){?>
					 <label for="<?php echo $this->get_field_id('post_format_'.$format); ?>">
						<input class="checkbox" type="checkbox" name="<?php echo $this->get_field_name('post_format[]'); ?>" id="<?php echo $this->get_field_id('post_format_'.$format); ?>" value="<?php echo $format;?>" <?php checked( in_array($format, $post_format) ); ?> /> <?php echo ucwords($format);?></label>
					<?php }?></p>
				<?php
				}
			}
			?>


    <p><label for="<?php echo $this->get_field_id('include'); ?>"><?php _e('Include posts','ttsaudio');?>:</label>
      <input name="<?php echo $this->get_field_name('include'); ?>" id="<?php echo $this->get_field_id('include'); ?>" value="<?php echo $include; ?>" type="text" class="widefat" /><br />
      <em><?php _e('Enter post IDs, separated with commas.','ttsaudio');?></em></p>

    <p><label for="<?php echo $this->get_field_id('exclude'); ?>"><?php _e('Exclude posts','ttsaudio');?>:</label>
      <input name="<?php echo $this->get_field_name('exclude'); ?>" id="<?php echo $this->get_field_id('exclude'); ?>" value="<?php echo $exclude; ?>" type="text" class="widefat" /><br />
      <em><?php _e('Enter post IDs, separated with commas.','ttsaudio');?></em></p>

		<p><label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e('Order by', 'ttsaudio'); ?>:</label>
			<?php $orderby_arr = array('ID'=>'ID','title'=>'Title','date'=>'Date','rand'=>'Rand','post__in'=>'Included post IDs ','comment_count'=>'Comment count'); ?>
			<select id="<?php echo $this->get_field_id('orderby');?>" name="<?php echo $this->get_field_name('orderby');?>">
				<?php foreach($orderby_arr as $key =>  $value){?>
				<option value="<?php echo $key;?>" <?php selected( $orderby, $key);?>><?php echo $value;?></option>
				<?php } ?>
			</select></p>

		<p><label for="<?php echo $this->get_field_id('order'); ?>"><?php esc_html_e('Order', 'ttsaudio'); ?>:</label>
			<?php $orders = array('DESC' => 'Descending', 'ASC' =>'Ascending');?>
			<select id="<?php echo $this->get_field_id('order');?>" name="<?php echo $this->get_field_name('order');?>">
				<?php foreach($orders as $key =>  $value){?>
				<option value="<?php echo $key;?>" <?php selected( $order, $key);?>><?php echo $value;?></option>
				<?php } ?>
			</select></p>

		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:' ); ?></label>
		<input class="tiny-text" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" step="1" min="1" value="<?php echo $number; ?>" size="3" /></p>

		<p><input class="checkbox" type="checkbox"<?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display post date?' ); ?></label></p>

<?php
	}

}
