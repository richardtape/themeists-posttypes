<?php
/*
Plugin Name: Themeists Custom Post Types
Plugin URI: #
Description: This plugin adds custom post types to WordPress. It means your content isn't locked into yout theme. It also adds some options to your Themeists Options Panel allowing you to alter how these post types work. If you switch themes away from Autify, then you will still have these post types (as long as this plugin is still active). You are also be able to adjust how these post types work using filters in your theme's functions.php file.
Version: 1.0
Author: Themeists
Author URI: #
License: GPL2
*/

if( !class_exists( 'ThemeistsPostTypes' ) ):


	/**
	 * Adds custom post types for projects and FAQs. Also, if a themeists them is active, then it will 
	 * add in customisation options to the theme options panel.
	 *
	 * @author Richard Tape
	 * @package ThemeistsPostTypes
	 * @since 1.0
	 */
	
	class ThemeistsPostTypes
	{


		/**
		 * We might not be using a themeists theme (which means we can't add anything to the options panel). By default,
		 * we'll say we are not. We check if the theme's author is Themeists to set this to true during instantiation.
		 *
		 * @author Richard Tape
		 * @package ThemeistsPostTypes
		 * @since 1.0
		 */
		
		var $using_themeists_theme = false;


		/**
		 * CPT name default, which is over-ridden via filters
		 *
		 * @author Richard Tape
		 * @package ThemeistsPostTypes
		 * @since 1.0
		 */
		
		var $project_post_type_default = "Project";


		/**
		 * Clients taxonomy for the Project post type, CT name default, overwritable by filters
		 *
		 * @author Richard Tape
		 * @package ThemeistsPostTypes
		 * @since 1.0
		 */

		var $clients_taxonomy_default = "Client";
		

		/**
		 * Initialise ourselves and do a bit of setup
		 *
		 * @author Richard Tape
		 * @package ThemeistsPostTypes
		 * @since 1.0
		 * @param None
		 * @return None
		 */

		function ThemeistsPostTypes()
		{

			$theme_data = wp_get_theme();
			$theme_author = $theme_data->display( 'Author', false );

			if( strtolower( trim( $theme_author ) ) == "themeists" )
				$this->using_themeists_theme = true;

			add_action( 'init', array( &$this, 'register_post_types' ) );
			add_action( 'init', array( &$this, 'register_taxonomies' ) );

			//Register our metaboxes
			add_action( 'init', array( &$this, 'register_cpt_metaboxes' ) );

			if( $this->using_themeists_theme )
			{

				//If we're using a Themeists theme, let's add some options to the options panel so people can adjust the
				//slug easier
				add_action( 'of_set_options_in_advanced_page_end', array( &$this, 'add_options_to_themeists_options_panel' ), 10, 1 );

				//These 2 functions alter the project cpt and client tax based on the optins set in the back end.
				add_filter( 'ff_project_cpt_name', array( &$this, 'adjust_project_cpt_based_on_option' ), 10, 1 );
				add_filter( 'ff_client_tax_name', array( &$this, 'adjust_clients_tax_based_on_option' ), 10, 1 );

			}

			register_activation_hook( __FILE__, array( &$this, 'on_plugin_activation' ) );

			//Add a link to our home page in the meta line of the plugin activation page
			add_filter( 'plugin_row_meta', array( &$this, 'add_meta_link' ), 10, 2 );


		}/* ThemeistsPostTypes() */
		


		/**
		 * Register our custom post types. We only register them if the current theme supports them (that's if
		 * we're running a Themeists theme, otherwise, we just register them)
		 *
		 * @author Richard Tape
		 * @package ThemeistsPostTypes
		 * @since 1.0
		 * @param None
		 * @return None
		 */
		
		function register_post_types()
		{

			//Find out if this theme adds support for custom-post-types.
			$cpts = array();
			$cpts = get_theme_support( 'custom-post-types' );

			//If we have 'project' cpt support or we're not on a themeists theme, register this CPT
			if( ( in_array( 'project', $cpts[0] ) ) || ( !$this->using_themeists_theme ) )
			{

				//This CPT's *singular* name - run it through a filter
				$default_name = $this->project_post_type_default;
				$this->project_post_type_default = apply_filters( 'ff_project_cpt_name', $default_name, $default_name );
				$cpt_name = $this->project_post_type_default;

				//CPT Labels
				$labels = array(
				
					'name' => 				__( $this->ff_pluralize_string( $cpt_name ), THEMENAME ),
					'singular_name' => 		__( $cpt_name, THEMENAME ),
					'add_new' => 			__( 'Add New', THEMENAME ),
					'add_new_item' => 		__( 'Add New ' . $cpt_name, THEMENAME ),
					'edit_item' => 			__( 'Edit ' . $cpt_name, THEMENAME ),
					'new_item' => 			__( 'New ' . $cpt_name, THEMENAME ),
					'all_items' => 			__( 'All ' . $this->ff_pluralize_string( $cpt_name ), THEMENAME ),
					'view_item' => 			__( 'View ' . $cpt_name, THEMENAME ),
					'search_items' => 		__( 'Search ' . $this->ff_pluralize_string( $cpt_name ) , THEMENAME ),
					'not_found' =>  		__( 'No ' . $this->ff_pluralize_string( $cpt_name ) . ' found', THEMENAME ),
					'not_found_in_trash' => __( 'No ' . $this->ff_pluralize_string( $cpt_name ) . ' found in Trash', THEMENAME ), 
					'parent_item_colon' => 	'',
					'menu_name' => 			__( $this->ff_pluralize_string( $cpt_name ) , THEMENAME )

				);

				//CPT Arguments
				$cpt_args = array(
				
					'labels' => 			$labels,
					'public' => 			true,
					'publicly_queryable' => true,
					'show_ui' => 			true, 
					'show_in_menu' => 		true, 
					'query_var' => 			true,
					'rewrite' => 			true,
					'capability_type' => 	'post',
					'has_archive' => 		true, 
					'hierarchical' => 		false,
					'menu_position' => 		null,
					'supports' => 			array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )/*,
					'menu_icon' => 			get_stylesheet_directory_uri() . '/_a/images/icon-project-16.png'*/

				);

				//If we're using a themeists theme, the cpt icon will be in the theme assets folder
				if( $this->using_themeists_theme )
					$cpt_args['menu_icon'] = get_stylesheet_directory_uri() . '/_a/images/icon-project-16.png';

				//Run our CPTs arguments through a filter so we can customise this externally
				$cpt_args = 				apply_filters( 'ff_project_cpt_args', $cpt_args );

				//Register our post type
				$register_post_type = 		register_post_type( $cpt_name, $cpt_args );

			}

		}/* register_post_types() */


		/**
		 * Register our taxonomies. Similar to the post types, if we're using a Themeists theme, we check for theme
		 * support, otherwise we just register them.
		 *
		 * @author Richard Tape
		 * @package ThemeistsPostTypes
		 * @since 1.0
		 * @param None
		 * @return None
		 */

		function register_taxonomies()
		{

			//Find out if this theme supports custom taxonomies
			$cts = array();
			$cts = get_theme_support( 'custom-taxonomies' );
			
			//If we have 'project' cpt support and have an appropriate CT or we're not on a themeists theme, register this CPT
			if( ( in_array( 'clients', $cts[0]["project"] ) ) || ( !$this->using_themeists_theme ) )
			{

				//This CPT's *singular* name - run it through a filter
				$default_name = $this->clients_taxonomy_default;
				$this->clients_taxonomy_default = apply_filters( 'ff_clients_taxonomy_name', $default_name, $default_name );
				$taxonomy_name = $this->clients_taxonomy_default;

				//CT Labels
				$tax_labels = array(

					'name' => 							__( $this->ff_pluralize_string( $taxonomy_name ), THEMENAME ),
					'singular_name' => 					__( $taxonomy_name, THEMENAME ),
					'search_items' => 					__( 'Search ' . $this->ff_pluralize_string( $taxonomy_name ) , THEMENAME ),
					'popular_items' => 					__( 'Popular ' . $this->ff_pluralize_string( $taxonomy_name ) , THEMENAME ),
					'all_items' => 						__( 'All ' . $this->ff_pluralize_string( $taxonomy_name ) , THEMENAME ),
					'parent_item' => 					null,
					'parent_item_colon' => 				null,
					'edit_item' => 						__( 'Edit ' . $taxonomy_name , THEMENAME ), 
					'update_item' => 					__( 'Update ' . $taxonomy_name , THEMENAME ),
					'add_new_item' => 					__( 'Add New ' . $taxonomy_name , THEMENAME ),
					'new_item_name' => 					__( 'New ' . $taxonomy_name . ' Name', THEMENAME ),
					'separate_items_with_commas' => 	__( 'Separate ' . $this->ff_pluralize_string( $taxonomy_name ) . ' with commas', THEMENAME ),
					'add_or_remove_items' => 			__( 'Add or remove ' . $this->ff_pluralize_string( $taxonomy_name ) , THEMENAME ),
					'choose_from_most_used' => 			__( 'Choose from the most used ' . $this->ff_pluralize_string( $taxonomy_name ) , THEMENAME ),
					'menu_name' => 						__( $this->ff_pluralize_string( $taxonomy_name ) , THEMENAME )

				);

				//CT Arguments
				$tax_args = array(

					'hierarchical' =>					false,
					'labels' => 						$tax_labels,
					'show_ui' => 						true,
					'query_var' => 						true

				);

				//Run the CT Arguments through a filter so we can adjust them externally
				$tax_args = 							apply_filters( 'ff_client_tax_args', $tax_args );

				//Register our custom taxonomy
				$register_tax = 						register_taxonomy( $this->ff_uglify_string( $taxonomy_name ), $this->ff_uglify_string( $this->project_post_type_default ), $tax_args );

			}

		}/* register_taxonomies() */
		


		/**
		 * If we are using a themeists theme then a filter cmb_meta_boxes exists. We hook into that
		 * to load our metaboxes. If the filter doesn't exist then we load the HumanMade metabox framework
		 * (included in this plugin) and then hook into that filter.
		 *
		 * @author Richard Tape
		 * @package ThemeistsPostTypes
		 * @since 1.0
		 * @param None
		 * @return None
		 */
		
		function register_cpt_metaboxes()
		{

			//If we're using a themeists theme or the filter exists, just add the filter call
			if( $this->using_themeists_theme || has_filter( 'cmb_meta_boxes' ) )
			{

				add_filter( 'cmb_meta_boxes', array( &$this, 'add_cpt_metaboxes' ) );

			}
			else
			{

				//We're not using a themeists theme or the cmb_meta_boxes filter doesn't exist for some reason
				//Double check that the CMB_Meta_Box class doesn't exist, if not, load it from the folder in this plugin
				if( !class_exists( 'CMB_Meta_Box' ) )
				{

					require_once( 'metaboxes/custom-meta-boxes.php' );
					add_filter( 'cmb_meta_boxes', array( &$this, 'add_cpt_metaboxes' ) );

				}

			}

		}/* register_cpt_metaboxes() */



		/**
		 * This function actually adds the metaboxes. It is called by the register_cpt_metaboxes() method above.
		 *
		 * @author Richard Tape
		 * @package 
		 * @since 1.0
		 * @param (array) $metaboxes - The already created metaboxes
	 	* @return $meta_boxes - The array of meta boxes that we've just added to 
		 */
		
		function add_cpt_metaboxes( array $meta_boxes )
		{

			// Start with an underscore to hide fields from custom fields list
			$prefix = '_';

			$meta_boxes[] = array(
				'title' => 'Testimonials',
				'pages' => 'project',
				'context'    => 'normal',
				'priority'   => 'high',
				'show_names' => true, // Show field names on the left
				'fields' => array(
					array( 'id' => $prefix . 'quotee', 'name' => 'Client Name', 'type' => 'text', 'cols' => 12, 'repeatable' => false, 'desc' => 'Test' ),
					array( 'id' => $prefix . 'company', 'name' => 'Company Name', 'type' => 'text', 'cols' => 12, 'repeatable' => false ),
					array( 'id' => $prefix . 'testimonial', 'name' => 'Quote', 'type' => 'textarea', 'cols' => 12, 'repeatable' => false )
				)
			);



			//Add the 'show Below Menu Above Content Sidebar' metabox
			$meta_boxes[] = array(
				'title' => 'Show Below Menu but Above Content Sidebar',
				'pages' => array( 'post', 'page', 'project' ),
				'context'    => 'side', //normal, advanced or side
				'priority'   => 'default', //core, high, default or low
				'show_names' => true, // Show field names on the left
				'fields' => array(
					array( 'id' => $prefix . 'show_below_menu_above_content_metabox', 'name' => __( 'Show Sidebar', THEMENAME ), 'type' => 'checkbox', 'cols' => 12, 'repeatable' => false, 'std' => apply_filters( 'autify_show_below_menu_above_content_sidebar_by_default', 1 ), 'desc' => __( 'If this checkbox is ticked then the sidebar called "Below Menu Above Content" will be shown (as long as it contains widgets). You are able to replace this sidebar on a page-by-page basis.', THEMENAME ) )
				)
			);

			return $meta_boxes;

		}/* add_cpt_metaboxes() */


		/**
		 * If we are using a themeists theme (so $this->using_themeists_theme is true) then we add options to the 
		 * theme options panel, allowing people to adjust the name/slug of the CPTs/CTs easily. If we're not,
		 * then the user has the option to do this using filters in their theme. We should only add these if the
		 * current theme supports custom post types
		 *
		 * @author Richard Tape
		 * @package Incipio
		 * @since 1.0
		 * @param None
		 * @return None
		 */

		function add_options_to_themeists_options_panel()
		{

			global $options;

			if( current_theme_supports( 'custom-post-types' ) ) :

				// Post Type Options ================================================

				$options[] = array(
					'name' => __('Project Post Type Slug', THEMENAME ),
					'desc' => __('By default, your project items have "project" in front of their URL. You can adjust that by changing this option. If you adjust this, you may need to visit Settings>Permalinks to make your changes stick.', THEMENAME ),
					'id' => 'project_slug',
					'std' => 'project',
					'type' => 'text'
				);

				$options[] = array(
					'name' => __('Client Taxonomy Slug', THEMENAME ),
					'desc' => __('By default, the project post type has a custom taxonomy called "Clients". If you want to rename that, simply change this option and save. You may need to visit settings>permalinks to make your changes stick.', THEMENAME ),
					'id' => 'clients_slug',
					'std' => 'client',
					'type' => 'text'
				);

			endif;

			// Post Type Options ============================================


		}/* add_options_to_themeists_options_panel() */



		/**
		 * We have a filter ff_project_cpt_name for the project cpt name. If we have an option set to
		 * adjust that, then we need to add a filter based on that option
		 *
		 * @author Richard Tape
		 * @package Autify
		 * @since 1.0
		 * @param $default_name - the name
		 * @return $project_slug - the project slug from the saved option
		 */
		
		function adjust_project_cpt_based_on_option( $default_name )
		{

			$chosen_project_name = $this->of_get_option( 'project_slug' );

			if( $chosen_project_name == "" || $chosen_project_name === false )
			{
				return $default_name;
			}
			else
			{
				return $chosen_project_name;
			}


		}/* adjust_project_cpt_based_on_option() */



		/**
		 * Adjust the client taxonomy if the option is changed using the ff_client_tax_name filter
		 *
		 * @author Richard Tape
		 * @package Autify
		 * @since 1.0
		 * @param (string) $default_name - the default name of the taxonomy
		 * @return $taxonomy_slug - the adjusted (or default) taxonomy slug
		 */

		function adjust_clients_tax_based_on_option( $default_name )
		{

			$chosen_tax_name = $this->of_get_option( 'clients_slug' );

			if( $chosen_tax_name == "" || $chosen_tax_name === false )
			{
				return $default_name;
			}
			else
			{
				return $chosen_tax_name;
			}

		}/* adjust_clients_tax_based_on_option() */
		


		/**
		 * When we activate the plugin, we make the post types, then we need to flush the permalinks
		 *
		 * @author Richard Tape
		 * @package Incipio
		 * @since 1.0
		 * @param None
		 * @return None
		 */
		
		function on_plugin_activation()
		{

			flush_rewrite_rules();

		}/* on_plugin_activation() */



		/**
		 * Add a link to our portfolio with a referrer so we know if people are clicking on the link to see more about us
		 *
		 * @author Richard Tape
		 * @package Incipio
		 * @since 1.0
		 * @param (array) $links - list of links already on meta line. (string) $file - which plugin this is referring to
		 * @return (array) $links - Modified array of links with our extra link
		 */
		
		public function add_meta_link( $links, $file  )
		{

			$plugin = plugin_basename( __FILE__ );

			if( $file == $plugin )
			{

				return array_merge( $links, array( '<a href="http://themeists.com/?ref=cpt_plugin" title="Go to the themeists.com home page to see our awesome premium and free WordPress themes and plugins" id="plugin_meta_themeists_home">by the Themeists</a>' ) );

			}

			return $links;

		}/* add_meta_link */



		/**
		 * Helper function to pluralize a CPT name neatly
		 * 
		 * @package Incipio
		 * @author Richard Tape
		 * @version 1.0
		 * @since 1.0
		 */

		public function ff_pluralize_string( $string )
		{

			$last = $string[strlen( $string ) - 1];
			
			if( $last == 'y' )
			{

				$cut = substr( $string, 0, -1 );
				
				//convert y to ies
				$plural = $cut . 'ies';

			}
			else
			{

				// just attach a s
				$plural = $string . 's';

			}
			
			return $plural;

		}/* ff_pluralize_string() */


		/**
		 * Beautifies a string. Capitalize words and remove underscores
		 *
		 * @author Richard Tape
		 * @package Incipio
		 * @since 1.0
		 * @param (string) $string
		 * @return string
		 */
		
		public function ff_beautify_string( $string )
		{

			return ucwords( str_replace( '_', ' ', $string ) );

		}/* ff_beautify_string() */
		


		/**
		 * Uglifies a string. Remove underscores and lower strings
		 *
		 * @author Richard Tape
		 * @package Incipio
		 * @since 1.0
		 * @param string $string
		 * @return string
		 */
		

		public function ff_uglify_string( $string )
		{

			return strtolower( preg_replace( '/[^A-z0-9]/', '_', $string ) );

		}/* ff_uglify_string() */


		/**
		 * Get our theme options
		 *
		 * @author Richard Tape
		 * @package 
		 * @since 1.0
		 * @param 
		 * @return 
		 */
		

		function of_get_option( $name, $default = false )
		{

			$config = get_option( 'optionsframework' );

			if( ! isset( $config['id'] ) )
			{
				return $default;
			}

			$options = get_option( $config['id'] );

			if( isset( $options[$name] ) )
			{
				return $options[$name];
			}

			return $default;

		}/* of_get_option */

	}/* class ThemeistsPostTypes */

endif;


//And so it begins
$themeists_custom_post_types = new ThemeistsPostTypes;