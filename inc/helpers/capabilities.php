<?php

function incsub_support_current_user_can( $cap = '' ) {
	$user_id = get_current_user_id();

	return incsub_support_user_can( $user_id, $cap );

}

function incsub_support_user_can( $user_id, $cap = '' ) {

	$settings = incsub_support_get_settings();

	$user_can = false;
	if ( ( is_multisite() && is_super_admin( $user_id ) ) || ( ! is_multisite() && user_can( $user_id, 'manage_options' ) ) ) {
		$user_can = true;
	}
	else {
		$cache_key = 'user_role_' . $user_id;
		$user_role = wp_cache_get( $cache_key, 'support_system_user_role' );

		if ( $user_role === false ) {
			$user = get_userdata( $user_id );
			if ( ! $user ) {
				$user_role = 'support-guest';
			}
			else {
				$user_role = isset( $user->roles[0] ) ? $user->roles[0] : '';
				if ( is_multisite() ) {
					// The user has not enough role in the Support blog.
					// Let's see if he has enough roles in other sites
					// This needs to be improved though
					$user_blogs = get_blogs_of_user( $user_id );
					$current_blog_id = get_current_blog_id();
					foreach ( $user_blogs as $blog ) {
						switch_to_blog( $blog->userblog_id );
						$user = get_userdata( $user_id );
						if ( isset( $user->roles[0] ) ) {
							$user_role = $user->roles[0];
							break;
						}
					}

					switch_to_blog( $current_blog_id );
				}

			}

			wp_cache_set( $cache_key, $user_role, 'support_system_user_role' );
			
		}

		switch ( $cap ) {
			case 'insert_ticket':
			case 'read_ticket':
			case 'insert_reply': { 
				if ( in_array( $user_role, $settings['incsub_support_tickets_role'] ) )
					$user_can = true;
				break; 
			}
			
			case 'update_reply': { $user_can = false; break; }

			case 'insert_ticket_category': 
			case 'update_ticket_category': 
			case 'delete_ticket_category': { 
				$user_can = false;
				break;
			}

			case 'read_faq': { 
				if ( in_array( $user_role, $settings['incsub_support_faqs_role'] ) )
					$user_can = true;
				break; 
			}

			case 'open_ticket': 
			case 'close_ticket': 
			case 'delete_ticket': 
			case 'update_ticket': { 
				$user_can = false;
				break;
			}

			case 'manage_options': { $user_can = false; break;}

			default: { $user_can = false; break; }
		}
	}

	/**
	 * Filters the permissions for a user
	 * 
	 * @param Boolean $user_can If the user is allowed to do something for a given capability
	 * @param Integer $user_id User ID
	 * @param String $cap Capability
	 */
	return apply_filters( 'support_system_user_can', $user_can, $user_id, $cap );

}

function incsub_support_get_capabilities() {
	return array(
		'insert_ticket',
		'delete_ticket',
		'update_ticket',
		'open_ticket',
		'close_ticket',
		'read_ticket',

		'insert_reply',
		'update_reply',
		'delete_reply',

		'insert_ticket_category',
		'update_ticket_category',
		'delete_ticket_category',

		'manage_options',

		'insert_faq',
		'delete_faq',
		'update_faq',
		'read_faq'
	);
}