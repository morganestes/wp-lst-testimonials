<?php
/*
  Plugin Name: Testimonials for LST
  Version: 1.0
  Plugin URI: https://github.com/morganestes/wp-lst-testimonials
  Description: This plugin lets you add rotating testimonials to a page using a shortcode or widget. Originally developed by <a href="http://www.wpbeginner.com/wp-tutorials/how-to-add-rotating-testimonials-in-wordpress/" target="_blank">WPBeginner</a>; customized for LST by <a href="http://morganestes.me" target="_blank">Morgan Estes</a>.
  Author: Morgan Estes
  Author URI: http://morganestes.me/
 */

if (! defined('LST_TESTIMONIALS_PLUGIN_URL'))
  define ( 'LST_TESTIMONIALS_PLUGIN_URL', plugin_dir_url(__FILE__));
// Output something like: http://example.com/wp-content/plugins/your-plugin/

if (!defined('LST_TESTIMONIALS_PLUGIN_PATH'))
	define('LST_TESTIMONIALS_PLUGIN_PATH', plugin_dir_path(__FILE__));
// Output something like: /home/mysite/www/wp-content/plugins/your-plugin/


function wpb_register_cpt_testimonial() {

	$labels = array(
		'name' => _x( 'Testimonials', 'testimonial' ),
		'singular_name' => _x( 'testimonial', 'testimonial' ),
		'add_new' => _x( 'Add New', 'testimonial' ),
		'add_new_item' => _x( 'Add New testimonial', 'testimonial' ),
		'edit_item' => _x( 'Edit testimonial', 'testimonial' ),
		'new_item' => _x( 'New testimonial', 'testimonial' ),
		'view_item' => _x( 'View testimonial', 'testimonial' ),
		'search_items' => _x( 'Search Testimonials', 'testimonial' ),
		'not_found' => _x( 'No testimonials found', 'testimonial' ),
		'not_found_in_trash' => _x( 'No testimonials found in Trash', 'testimonial' ),
		'parent_item_colon' => _x( 'Parent testimonial:', 'testimonial' ),
		'menu_name' => _x( 'Testimonials', 'testimonial' ),
	);

	$args = array(
		'labels' => $labels,
		'hierarchical' => false,
		'supports' => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'custom-fields', 'revisions' ),
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_icon' => LST_TESTIMONIALS_PLUGIN_URL . 'images/comments-16-grey.png',
		'show_in_nav_menus' => true,
		'publicly_queryable' => true,
		'exclude_from_search' => false,
		'has_archive' => true,
		'query_var' => true,
		'can_export' => true,
		'rewrite' => true,
		'capability_type' => 'post'
	);

	register_post_type( 'testimonial', $args );
}

add_action( 'init', 'wpb_register_cpt_testimonial' );

/**
 * Adds large custom icon to edit page, and adds color icon to hovered/active menu.
 * @global type $post_type
 */
function wpb_lst_testimonials_header() {
	global $post_type;
	echo '<style>';
	if ( ($_GET[ 'post_type' ] == 'testimonial') || ($post_type == 'testimonial') ) {
		echo '#icon-edit {background: transparent url(' . LST_TESTIMONIALS_PLUGIN_URL . 'images/comments-32-grey.png) no-repeat !important;}';
	}
	echo '#menu-posts-testimonial:hover .wp-menu-image > img, #menu-posts-testimonial.wp-menu-open .wp-menu-image > img { content: url(' . LST_TESTIMONIALS_PLUGIN_URL . 'images/comments-16-blue.png) no-repeat center center; }';
	echo '</style>';
}

add_action( 'admin_head', 'wpb_lst_testimonials_header' );

$key = "testimonial";
$meta_boxes = array(
	"person-name" => array(
		"name" => "person-name",
		"title" => "Person's Name",
		"description" => "Enter the name of the person who gave you the testimonial." ),
	"location" => array(
		"name" => "location",
		"title" => "Location",
		"description" => "Where is this person from (or where was the LST project city)?" )
);

function wpb_create_meta_box() {
	global $key;

	if ( function_exists( 'add_meta_box' ) ) {
		add_meta_box( 'new-meta-boxes', ucfirst( $key ) . ' Information', 'display_meta_box', 'testimonial', 'normal', 'high' );
	}
}

function display_meta_box() {
	global $post, $meta_boxes, $key;
	?>

	<div class="form-wrap">

		<?php
		wp_nonce_field( plugin_basename( __FILE__ ), $key . '_wpnonce', false, true );

		foreach ( $meta_boxes as $meta_box ) {
			$data = get_post_meta( $post->ID, $key, true );
			?>

			<div class="form-field form-required">
				<label for="<?php echo $meta_box[ 'name' ]; ?>"><?php echo $meta_box[ 'title' ]; ?></label>
				<input type="text" name="<?php echo $meta_box[ 'name' ]; ?>" value="<?php echo htmlspecialchars( $data[ $meta_box[ 'name' ] ] ); ?>" />
				<p><?php echo $meta_box[ 'description' ]; ?></p>
			</div>

		<?php } ?>

	</div>
	<?php
}

function wpb_save_meta_box( $post_id ) {
	global $post, $meta_boxes, $key;

	foreach ( $meta_boxes as $meta_box ) {
		$data[ $meta_box[ 'name' ] ] = $_POST[ $meta_box[ 'name' ] ];
	}

	if ( ! wp_verify_nonce( $_POST[ $key . '_wpnonce' ], plugin_basename( __FILE__ ) ) )
		return $post_id;

	if ( ! current_user_can( 'edit_post', $post_id ) )
		return $post_id;

	update_post_meta( $post_id, $key, $data );
}

add_action( 'admin_menu', 'wpb_create_meta_box' );
add_action( 'save_post', 'wpb_save_meta_box' );

// Display the testimonial on screen
function wpb_display_testimonials() {
	?>
	<script>
		jQuery(function($) {
			setInterval(function() {
				$('#testimonials .t-slide').filter(':visible').fadeOut(800, function() {
					if( $(this).next('.t-slide').length ) {
						$(this).next().fadeIn(800);
					}
					else {
						$('#testimonials .t-slide').eq(0).fadeIn(800);
					}
				});
			}, 6000);
		});
	</script>
	<style>
		#testimonials .t-slide {
			color: #644f4b;
			width: 90%;
			padding: 0 15px;
			margin: 0 auto;
		}
		#testimonials .client-contact-info {
			margin: 0 25px 0 0;
			float: right;
		}
		#testimonials blockquote {
			padding: 3px 0 0 65px;
			line-height: 1.5em;
			font-family: "Lato", Helvetica, Arial, sans-serif !important;
			font-size: 18px;
			font-weight: normal;
			font-style: italic;
			margin: 10px 0 20px 0;
		}
	</style>
	<div id="testimonials">
		<?php
		$args = array( 'post_type' => 'testimonial', 'posts_per_page' => 100, 'orderby' => 'menu_order', 'order' => 'ASC' );
		$loop = new WP_Query( $args );
		if ( $loop->have_posts() ) : while ( $loop->have_posts() ) : $loop->the_post();
				$data = get_post_meta( $loop->post->ID, 'testimonial', true );
				static $count = 0;
				if ( $count == "1" ) {
					?>

					<div class="t-slide" style="display: none;">
						<blockquote><?php the_content(); ?>
							<span class="client-contact-info"><?php echo $data[ 'person-name' ]; ?>,&nbsp;<?php echo $data[ 'location' ]; ?></a></span>
						</blockquote>
					</div>
				<?php } else { ?>

					<div class="t-slide">
						<blockquote><?php the_content(); ?>
							<span class="client-contact-info"><?php echo $data[ 'person-name' ]; ?>,&nbsp;<?php echo $data[ 'location' ]; ?></a></span>
						</blockquote>
					</div>


					<?php
					$count ++;
				}
			endwhile;
		endif;
		echo '</div>' /* close div#testimonials */;
	}

	// Make shortcode available for use in front-end editor
	add_shortcode( 'wpb_display_testimonials', 'wpb_display_testimonials' );

	/**
	 * Adds Lst_Testimonials_Widget widget.
	 */
	class Lst_Testimionials_Widget extends WP_Widget {

		/**
		 * Register widget with WordPress.
		 */
		public function __construct() {
			parent::__construct(
					'lst_testimonials_widget', // Base ID
					'LST Testimonials', // Name
					array( 'description' => __( 'Display testimonials and rotate through them at intervals.', 'text_domain' ), ) // Args
			);
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
			$title = apply_filters( 'widget_title', $instance[ 'title' ] );

			echo $before_widget;
			if ( ! empty( $title ) ) {
				echo $before_title . $title . $after_title;
			}
			echo do_shortcode( '[wpb_display_testimonials]' );
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
			$instance = array( );
			$instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );

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
			if ( isset( $instance[ 'title' ] ) ) {
				$title = $instance[ 'title' ];
			} else {
				$title = __( 'New title', 'text_domain' );
			}
			?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</p>
			<?php
		}

	}

// Register the widget with WordPress
	function register_lst_testimonials_widget() {
		register_widget( 'Lst_Testimionials_Widget' );
	}

// Initialze the widget for use when the plugin is active.
	add_action( 'widgets_init', 'register_lst_testimonials_widget' );
	?>
