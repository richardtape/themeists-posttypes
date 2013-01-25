<?php

	if( !class_exists( 'ThemeistsTestimonialsFromProjects' ) )
	{

		class ThemeistsTestimonialsFromProjects extends WP_Widget
		{
		
			
			/**
			 * The name shown in the widgets panel
			 *
			 * @author Richard Tape
			 * @package ThemeistsTestimonialsFromProjects
			 * @since 1.0
			 */
			
			const name 		= 'Themeists Project Testimonials';

			/**
			 * For helping with translations
			 *
			 * @author Richard Tape
			 * @package ThemeistsTestimonialsFromProjects
			 * @since 1.0
			 */

			const locale 	= THEMENAME;

			/**
			 * The slug for this widget, which is shown on output
			 *
			 * @author Richard Tape
			 * @package ThemeistsTestimonialsFromProjects
			 * @since 1.0
			 */
			
			const slug 		= 'ThemeistsTestimonialsFromProjects';
		

			/* ============================================================================ */
		
			/**
			 * The widget constructor. Specifies the classname and description, instantiates
			 * the widget, loads localization files, and includes necessary scripts and
			 * styles. 
			 *
			 * @author Richard Tape
			 * @package ThemeistsTestimonialsFromProjects
			 * @since 1.0
			 * @param None
			 * @return None
			 */
			
			function ThemeistsTestimonialsFromProjects()
			{
		
				//load_plugin_textdomain( self::locale, false, plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . '/lang/' );

		
				$widget_opts = array (

					'classname' => 'ThemeistsTestimonialsFromProjects', 
					'description' => __( 'A simple widget to show a testimonial from a particular project', self::locale )

				);

				$control_options = array(

					'width' => '400'

				);

				//Register the widget
				$this->WP_Widget( self::slug, __( self::name, self::locale ), $widget_opts, $control_options );
		
		    	// Load JavaScript and stylesheets
		    	$this->register_scripts_and_styles();
		
			}/* ThemeistsTestimonialsFromProjects() */
		

			/* ============================================================================ */


			/**
			 * Outputs the content of the widget.
			 *
			 * @author Richard Tape
			 * @package ThemeistsTestimonialsFromProjects
			 * @since 1.0
			 * @param (array) $args - The array of form elements
			 * @param (array) $instance - The saved options from the widget controls
			 * @return None
			 */
			

			function widget( $args, $instance )
			{
		
				extract( $args, EXTR_SKIP );
		
				echo $before_widget;
		
					//Get vars
		    		$title					=	$instance['title'];
		    		$post_id				=	$instance['post_id'];

		    		echo $before_title . $title . $after_title;

		    		if( !empty( $post_id ) )
		    		{

		    			$quote = get_post_meta( $post_id, '_testimonial', true );
		    			$person = get_post_meta( $post_id, '_quotee', true );
		    			$company = get_post_meta( $post_id, '_company', true );

		    			?>

		    			<blockquote class="testimonial">
		    				<p>&#8220; <?php echo $quote; ?> &#8222;</p>
		    			</blockquote>

		    			<p>
		    				<span class="person_name"><?php echo $person; ?></span>
		    				<span class="company_name"><?php echo $company; ?></span>
		    			</p>

		    			<?php

		    		}

				echo $after_widget;
		
			}/* widget() */


			/* ============================================================================ */

		
			/**
			 * Processes the widget's options to be saved.
			 *
			 * @author Richard Tape
			 * @package ThemeistsTestimonialsFromProjects
			 * @since 1.0
			 * @param $new_instance	The previous instance of values before the update.
			 * @param @old_instance	The new instance of values to be generated via the update. 
			 * @return $instance The saved values
			 */
			
			function update( $new_instance, $old_instance )
			{
		
				$instance = $old_instance;
		
		    	$instance['title'] 			= 	$new_instance['title'];
		    	$instance['post_id'] 		= 	$new_instance['post_id'];
		    
				return $instance;
		
			}/* update() */


			/* ============================================================================ */


			/**
			 * Generates the administration form for the widget.
			 *
			 * @author Richard Tape
			 * @package ThemeistsTestimonialsFromProjects
			 * @since 1.0
			 * @param $instance	The array of keys and values for the widget.
			 * @return None
			 */
			

			function form( $instance )
			{
		
				$instance = wp_parse_args(

					(array)$instance,
					array(
						'title' => 'Testimonial',
						'post_id' => ''
					)

				);

				//Custom loop to show all project post types
				$all_projects = array();

				$loop_args = array( 'post_type' => 'project', 'posts_per_page' => -1 );
				$query = new WP_Query( $loop_args );

				while ( $query->have_posts() ) : $query->the_post();
					$all_projects[get_the_ID()] = get_the_title();
				endwhile;

				// Restore original Query & Post Data
				wp_reset_query();
				wp_reset_postdata();
		
		    	?>
		    	
		    		<p>
						<label for="<?php echo $this->get_field_id( 'title' ); ?>">
							<?php _e( "Title", THEMENAME ); ?>
						</label>
						<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
					</p>

					<p>
						<label for="<?php echo $this->get_field_id( 'post_id' ); ?>">
							<?php _e( "Select Project", THEMENAME ); ?>
						</label>
						<select class="widefat" id="<?php echo $this->get_field_id( 'post_id' ); ?>" name="<?php echo $this->get_field_name( 'post_id' ); ?>">
							<?php foreach( $all_projects as $id => $title ) : ?>
							<option <?php selected( $id, $instance['post_id'] ); ?> value="<?php echo $id ?>"><?php echo $title; ?></option>
							<?php endforeach; ?>
						</select>
					</p>
		    	
		    	<?php
		
			}/* form() */


			/* ============================================================================ */
		

			/**
			 * Registers and enqueues stylesheets for the administration panel and the
			 * public facing site.
			 *
			 * @author Richard Tape
			 * @package ThemeistsTestimonialsFromProjects
			 * @since 1.0
			 * @param None
			 * @return None
			 */
			

			private function register_scripts_and_styles()
			{

				if( is_admin() )
				{

		      		//$this->load_file('friendly_widgets_admin_js', '/themes/'.THEMENAME.'/admin/js/widgets.js', true);

				}
				else
				{ 

		      		//$this->load_file('friendly_widgets', '/themes/'.THEMENAME.'/theme_assets/js/widgets.js', true);

				}

			}/* register_scripts_and_styles() */


			/* ============================================================================ */


			/**
			 * Helper function for registering and enqueueing scripts and styles.
			 *
			 * @author Richard Tape
			 * @package ThemeistsTestimonialsFromProjects
			 * @since 1.0
			 * @param $name 		The ID to register with WordPress
			 * @param $file_path	The path to the actual file
			 * @param $is_script	Optional argument for if the incoming file_path is a JavaScript source file.
			 * @return None
			 */
			
			function load_file( $name, $file_path, $is_script = false )
			{
		
		    	$url = content_url( $file_path, __FILE__ );
				$file = $file_path;
					
				if( $is_script )
				{

					wp_register_script( $name, $url, '', '', true );
					wp_enqueue_script( $name );

				}
				else
				{

					wp_register_style( $name, $url, '', '', true );
					wp_enqueue_style( $name );

				}
			
			}/* load_file() */
		
		
		}/* class ThemeistsTestimonialsFromProjects */

	}

	//Register The widget
	//register_widget( "ThemeistsTestimonialsFromProjects" );
	add_action( 'widgets_init', create_function( '', 'register_widget( "ThemeistsTestimonialsFromProjects" );' ) );

?>