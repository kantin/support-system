<?php

class Incsub_Support_Admin_FAQ_Menu extends Incsub_Support_Admin_Menu {
	public function __construct( $slug, $network = false ) {
		parent::__construct( $slug, $network );
	}


	public function add_menu() {		
		parent::add_submenu_page(
			'ticket-manager-b',
			__( 'FAQ', INCSUB_SUPPORT_LANG_DOMAIN ),
			__( 'Frequently Asked Questions', INCSUB_SUPPORT_LANG_DOMAIN ), 
			'manage_options'
		);

		add_action( 'load-' . $this->page_id, array( $this, 'set_filters' ) );

	}

	public function set_filters() {
		add_action( 'wp_ajax_vote_faq_question', array( &$this, 'vote_faq_question' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
	}

	public function enqueue_scripts( $hook ) {
		wp_enqueue_script( 'mu-support-faq-js', INCSUB_SUPPORT_PLUGIN_URL . '/admin/assets/js/faq.js', array(), '20130402' );
	}

	/**
	 * Votes a question via AJAX
	 * 
	 * @since 1.8
	 */
	public function vote_faq_question() {
		if ( isset( $_POST['faq_id'] ) && isset( $_POST['vote'] ) && in_array( $_POST['vote'], array( 'yes', 'no' ) ) ) {

			$faq_id = absint( $_POST['faq_id'] );

			$vote = 'yes' == $_POST['vote'] ? true : false;

			incsub_support_vote_faq( $faq_id );
		}
		die();
	}

	public function render_inner_page() {
		$faq_categories = incsub_support_get_faq_categories();

		if ( isset( $_POST['submit-faq-search'] ) && check_admin_referer( 'faq_search' ) ) {
			$new_faq_categories = array();
			foreach ( $faq_categories as $key => $item ) {
				$answers = incsub_support_get_faqs( array( 's' => $_POST['faq-s'], 'per_page' => -1 ) );
				if ( count( $answers ) > 0 ) {
					$the_faq = $item;
	            	$the_faq->answers = $answers;
	            	$the_faq->faqs = count( $answers );
	            	$new_faq_categories[] = $the_faq;
	            }
	        }

	        $index = 0;
	        $faq_categories = $new_faq_categories;
		}
		else {
	    	foreach ( $faq_categories as $key => $item ) {
	            $faq_categories[ $key ]->faqs = incsub_support_count_faqs_on_category( $item->cat_id );
	            $faq_categories[ $key ]->answers = incsub_support_get_faqs( array( 'category' => $item->cat_id ) );
	        }
	    }		    

        $half_of_array = ceil( count( $faq_categories ) / 2 );

        include_once( 'views/admin-faq.php' );

	}

	public function embed_media( $match ) {
		require_once( ABSPATH . WPINC . '/class-oembed.php' );
		$wp_oembed = _wp_oembed_get_object();

		$embed_code = $wp_oembed->get_html( $match[1] );
		return $embed_code;
	}
}