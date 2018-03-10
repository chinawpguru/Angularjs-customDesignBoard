<?php
/**
 * Created by PhpStorm.
 * User: bhall
 * Date: 7/29/2017
 * Time: 5:35 PM
 */

class Wineshop_Label_Post_Type {

	var $single = "Custom Label";  // this represents the singular name of the post type
	var $plural = "Custom Labels";  // this represents the plural name of the post type
	var $type = "wineshop-label";  // this is the actual type

	function __construct() {

		# Place your add_actions and add_filters here
		//add_action('init', array(&$this, 'init'));
		add_action( 'init', array( &$this, 'add_post_type' ) );
		add_action( 'init', array( &$this, 'add_taxonomies' ) );

		# Add meta box
		add_action( 'add_meta_boxes', array( &$this, 'add_custom_metaboxes' ) );
		add_action( 'admin_menu', array( $this, 'remove_meta_box' ) );
		//add_action( 'add_meta_boxes', array(__CLASS__,'add_meta_box'));

		# Add Custom Columns
		add_filter( "manage_edit-" . $this->type . "_columns", array( &$this, "edit_columns" ) );
		add_action( "manage_posts_custom_column", array( &$this, "custom_columns" ) );

	}


	function init( $options = null ) {

	}


	function add_post_type() {
		// Register Post Type
		register_post_type( $this->type, array(
			'labels'             => array(
				'name'               => _x( $this->plural, 'post type general name' ),
				'singular_name'      => _x( $this->single, 'post type singular name' ),
				'all_items'          => _x( 'All ' . $this->plural, 'post type general name' ),
				'add_new'            => _x( 'Add ' . $this->single, $this->single ),
				'add_new_item'       => __( 'Add New ' . $this->single ),
				'edit_item'          => __( 'Edit ' . $this->single ),
				'new_item'           => __( 'New ' . $this->single ),
				'view_item'          => __( 'View ' . $this->single ),
				'search_items'       => __( 'Search ' . $this->plural ),
				'not_found'          => __( 'No ' . $this->plural . ' Found' ),
				'not_found_in_trash' => __( 'No ' . $this->plural . ' found in Trash' ),
				'parent_item_colon'  => ''
			),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => false,
			/*'capability_type' => 'post',
			'capabilities' => array(
				'create_posts' => 'do_not_allow', // false < WP 4.5, credit @Ewout
			),
			'map_meta_cap' => true, // Set to `false`, if users are not allowed to edit/delete existing posts*/
			'hierarchical'       => false,
			'has_archive'        => false,
			'menu_position'      => 56,
			#'_edit_link' => 'media.php?attachment_id=%d',
			'menu_icon' => 'dashicons-grid-view',
			'supports'           => array(
				'title',
                //'thumbnail',
				'custom-fields',
			)
		) );
		/*register_extended_post_type( 'label', array(
			'menu_icon' => 'dashicons-grid-view',
			'has_archive' => false,
			'rewrite' => false,
			'hierarchical' => false,
			'menu_position' => null,
			'supports'  => array(
				'title',
			),
		) );*/
	}

	function add_taxonomies() {

		register_taxonomy( 'label-status', array( $this->type ), array(
				'hierarchical' => true,
				'labels'       => array(
					'name'          => __( 'Statuses' ),
					'singular_name' => __( 'Status' ),
					'all_items'     => __( 'All Statuses' ),
					'add_new_item'  => __( 'Add Status' )
				),
				'show_ui'      => true,
				'show_in_menu' => true,
				'public'       => true,
				'query_var'    => false,
				'rewrite'      => false,
			)
		);
	}

	//Remove taxonomy meta box
	function remove_meta_box() {
		//The taxonomy metabox ID. This is different for non-hierarchical taxonomies
		remove_meta_box( 'label-statusdiv', $this->type, 'normal' );
	}

	function add_custom_metaboxes() {

		add_meta_box( 'label_status', 'Status', array( $this, 'taxonomy_metabox' ), $this->type, 'side', 'core' );

		remove_meta_box( 'wpseo_meta', $this->type, 'normal' );
		remove_meta_box( 'dualbrain-header-HeaderMetaBox', $this->type, 'side' );
	}

	//Callback to set up metabox
	function taxonomy_metabox( $post ) {
		//Get taxonomy and terms
		$taxonomy = 'label-status';
		$tax      = get_taxonomy( $taxonomy );
		$name     = 'tax_input[' . $taxonomy . ']';
		$terms    = get_terms( array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => 0
		) );

		//Get current and popular terms
		$popular   = get_terms( $taxonomy, array(
			'orderby'      => 'count',
			'order'        => 'DESC',
			'number'       => 10,
			'hierarchical' => false
		) );
		$postterms = get_the_terms( $post->ID, $taxonomy );
		$current   = ( $postterms ? array_pop( $postterms ) : false );
		$current   = ( $current ? $current->term_id : 0 );
		?>
        <div id="taxonomy-<?php echo $taxonomy; ?>" class="categorydiv">

            <!-- Display tabs-->
            <ul id="<?php echo $taxonomy; ?>-tabs" class="category-tabs">
                <li class="tabs"><a href="#<?php echo $taxonomy; ?>-all"
                                    tabindex="3"><?php echo $tax->labels->all_items; ?></a></li>
                <li class="hide-if-no-js"><a href="#<?php echo $taxonomy; ?>-pop"
                                             tabindex="3"><?php _e( 'Most Used' ); ?></a></li>
            </ul>

            <!-- Display popular taxonomy terms -->
            <div id="<?php echo $taxonomy; ?>-pop" class="tabs-panel" style="display: none;">
                <ul id="<?php echo $taxonomy; ?>checklist-pop" class="categorychecklist form-no-clear">
					<?php foreach ( $popular as $term ) {
						$id = "id='in-popular-event-category-$term->term_id'";
						echo "<li id='popular-event-category-$taxonomy-$term->term_id'><label class='selectit'>";
						echo "<input type='radio' {$id}" . checked( $current, $term->term_id, false ) . "value='$term->term_id' />$term->name<br />";
						echo "</label></li>";
					} ?>
                </ul>
            </div>
            <!-- Display taxonomy terms -->
            <div id="<?php echo $taxonomy; ?>-all" class="tabs-panel">
                <ul id="<?php echo $taxonomy; ?>checklist"
                    class="list:<?php echo $taxonomy ?> categorychecklist form-no-clear">
					<?php foreach ( $terms as $term ) {
						$id = "id='in-event-category-$term->term_id'";
						echo "<li id='event-category-$taxonomy-$term->term_id'><label class='selectit'>";
						echo "<input type='radio' {$id} name='{name}'" . checked( $current, $term->term_id, false ) . "value='$term->term_id' />$term->name<br />";
						echo "</label></li>";
					} ?>
                </ul>
            </div>
        </div>
		<?php
	}


	function edit_columns( $columns ) {
		$columns = array(
			"cb"     => "<input type=\"checkbox\" />",
			"label"  => "Label",
			"title"  => "Title",
			"status" => "Status",
			"date"   => "Date",
		);

		return $columns;
	}

	function custom_columns( $column ) {
		global $post;
		$custom = get_post_custom( $post->ID );

		switch ( $column ) {

			case "label":

				$custom = get_post_custom($post->ID);

				// Make sure we have a Order and Product ID stored as meta
				$order_id = $custom['bydesign_online_order_id'][0] ?: false;
				$product_id = $custom['bydesign_product_id'][0] ?: false;
				if( $order_id && $product_id )
				{
				    $thumb = self::get_thumbnail($order_id, $product_id);
				    if( $thumb )
				        printf('<img src="%s" />', $thumb);

				}

				break;

			case "status":
				$terms = get_the_terms( $post->ID, 'label-status' );
				if ( $terms ) {
					foreach ( $terms as $term ) {
						echo sprintf( '<a href="%s" title="View all %s in %s">%s</a><br />', admin_url( 'edit.php?post_type=' . $this->type . '&' . $term->taxonomy . '=' . $term->slug ), $this->plural, $term->name, $term->name );
					}
				} else {
					echo '<em style="color: #CCC;">Unknown</em>';
				}
				break;

		}
	}

	function template_redirect() {
		global $wp;
		global $wp_query;

		if ( ! $wp_query->is_feed ) {

			//print_r($wp->query_vars);
			if ( $wp->query_vars["post_type"] == $this->type && is_single() ) {
				include( TEMPLATEPATH . "/product-single.php" );
				die();
			} else if ( $wp->query_vars["product-location"] || $wp->query_vars["product-type"] || $wp->query_vars["product-land"] ) {
				include( TEMPLATEPATH . "/product-category.php" );
				die();
			} else if ( $wp->query_vars["post_type"] == $this->type ) {
				include( TEMPLATEPATH . "/product-all.php" );
				//include(TEMPLATEPATH . "/product-all.php");
				die();
			}

		}
	}

	public static function get_thumbnail( $order_id, $product_id )
    {
	    // Check if a PNG exists for this
	    $upload_dir = wp_upload_dir();
	    $labels_path = $upload_dir['basedir'] . '/wineshop-labels';
	    $label_file = sprintf("%s/wineshop-labels/%s-%s.jpeg", $upload_dir['basedir'], $order_id, $product_id);

	    if ( file_exists( $label_file ) )
	    {
	        $label_url = sprintf("%s/uploads/wineshop-labels/%s-%s.jpeg", content_url(), $order_id, $product_id);
		    return $label_url;
	    }

	    return null;
    }

	function admin_scripts() {

	}

}
new Wineshop_Label_Post_Type();