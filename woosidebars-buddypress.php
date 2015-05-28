<?php

/**
 * The WooSidebars for BuddyPress Plugin
 * 
 * @package WooSidebars for BuddyPress
 * @subpackage Main
 *
 * @todo Only apply plugin for the root (BP) blog when in MS
 */

/**
 * Plugin Name:       WooSidebars for BuddyPress
 * Description:       Enable WooSidebars for BuddyPress pages
 * Plugin URI:        https://github.com/lmoffereins/woosidebars-buddypress/
 * Version:           1.0.0
 * Author:            Laurens Offereins
 * Author URI:        https://github.com/lmoffereins/
 * Text Domain:       woosidebars-buddypress
 * Domain Path:       /languages/
 * GitHub Plugin URI: lmoffereins/woosidebars-buddypress
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooSidebars_BuddyPress' ) ) :
/**
 * The main plugin class
 *
 * @since 1.0.0
 */
final class WooSidebars_BuddyPress {

	/**
	 * Setup and return the singleton pattern
	 *
	 * @since 1.0.0
	 *
	 * @uses WooSidebars_BuddyPress::setup_globals()
	 * @uses WooSidebars_BuddyPress::setup_actions()
	 * @return The single WooSidebars_BuddyPress
	 */
	public static function instance() {

		// Store instance locally
		static $instance = null;

		if ( null === $instance ) {
			$instance = new WooSidebars_BuddyPress;
			$instance->setup_globals();
			$instance->setup_actions();
		}

		return $instance;
	}

	/**
	 * Prevent the plugin class from being loaded more than once
	 */
	private function __construct() { /* Nothing to do */ }

	/** Private methods *************************************************/

	/**
	 * Setup default class globals
	 *
	 * @since 1.0.0
	 */
	private function setup_globals() {

		/** Versions **********************************************************/

		$this->version      = '1.0.0';

		/** Paths *************************************************************/

		// Setup some base path and URL information
		$this->file         = __FILE__;
		$this->basename     = plugin_basename( $this->file );
		$this->plugin_dir   = plugin_dir_path( $this->file );
		$this->plugin_url   = plugin_dir_url ( $this->file );

		// Includes
		$this->includes_dir = trailingslashit( $this->plugin_dir . 'includes' );
		$this->includes_url = trailingslashit( $this->plugin_url . 'includes' );

		// Languages
		$this->lang_dir     = trailingslashit( $this->plugin_dir . 'languages' );

		/** Misc **************************************************************/

		$this->extend       = new stdClass();
		$this->domain       = 'woosidebars-buddypress';
	}

	/**
	 * Setup default actions and filters
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {

		// Bail when WooSidebars is not active
		if ( ! class_exists( 'Woo_Sidebars' ) )
			return;

		// Load textdomain
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		// Conditions
		add_filter( 'woo_conditions_headings',       array( $this, 'filter_conditions_headings' ) );
		add_filter( 'woo_conditions_reference',      array( $this, 'filter_conditions_options'  ) );
		add_filter( 'woo_conditions_tab_buddypress', array( $this, 'filter_conditions_tab'      ) );
		add_filter( 'woo_conditions',                array( $this, 'filter_page_conditions'     ) );

		// Sidebars are not prioritized. The first matching sidebar is selected,
		// sorted by latest date creation (ID, DESC). Try to solve this for our conditions.
	}

	/** Plugin **********************************************************/

	/**
	 * Load the translation file for current language. Checks the languages
	 * folder inside the plugin first, and then the default WordPress
	 * languages folder.
	 *
	 * Note that custom translation files inside the plugin folder will be
	 * removed on plugin updates. If you're creating custom translation
	 * files, please use the global language folder.
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'plugin_locale' with {@link get_locale()} value
	 * @uses load_textdomain() To load the textdomain
	 * @uses load_plugin_textdomain() To load the textdomain
	 */
	public function load_textdomain() {

		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale', get_locale(), $this->domain );
		$mofile        = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );

		// Setup paths to current locale file
		$mofile_local  = $this->lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/woosidebars-buddypress/' . $mofile;

		// Look in global /wp-content/languages/woosidebars-buddypress folder
		load_textdomain( $this->domain, $mofile_global );

		// Look in local /wp-content/plugins/woosidebars-buddypress/languages/ folder
		load_textdomain( $this->domain, $mofile_local );

		// Look in global /wp-content/languages/plugins/
		load_plugin_textdomain( $this->domain );
	}

	/** Public methods **************************************************/

	/**
	 * Append the headings for our condition groups
	 *
	 * @since 1.0.0
	 * 
	 * @param array $headings Condition headings
	 * @return array Headings
	 */
	public function filter_conditions_headings( $headings ) {

		// Add BuddyPress heading
		$headings['buddypress'] = __( 'BuddyPress', 'woosidebars-buddypress' );

		// Add Members heading
		$headings['bp-members'] = __( 'Members', 'woosidebars-buddypress' );

		// Add Activity heading
		$headings['bp-activity'] = __( 'Activity', 'woosidebars-buddypress' );

		// Add Groups heading
		$headings['bp-groups'] = __( 'Groups', 'woosidebars-buddypress' );

		// Add Messages heading
		$headings['bp-messages'] = __( 'Messages', 'woosidebars-buddypress' );

		// Add My Pages heading
		$headings['bp-loggedin'] = __( 'My Pages', 'woosidebars-buddypress' );

		return $headings;
	}

	/**
	 * Append to the available selectable conditions for a sidebar
	 *
	 * @since 1.0.0
	 * 
	 * @param array $conditions Available conditions
	 * @return array Conditions
	 */
	public function filter_conditions_options( $conditions ) {

		// Provide empty condition set. We'll override
		// the conditions tab contents manually.
		$conditions['buddypress'] = array();

		return $conditions;
	}

	/**
	 * Return our specific conditions
	 *
	 * All available conditions are derived from `bp-core-template.php` and
	 * `bp_get_the_body_class()` logic. See also ::filter_page_conditions().
	 *
	 * @since 1.0.0
	 *
	 * @see bp_get_the_body_class()
	 *
	 * @uses bp_get_member_types()
	 * @uses bp_is_active()
	 * @uses groups_get_groups()
	 * @return array Conditions
	 */
	public function get_conditions() {

		// Define conditions per component
		$conditions = array(

			// BP Core
			'buddypress' => array(

				// Any BP page
				'buddypress' => array(
					'label'       => __( 'BuddyPress pages', 'woosidebars-buddypress' ),
					'description' => __( 'Applies to all pages within BuddyPress.', 'woosidebars-buddypress' )
				),

				// Not a BP page
				'not-buddypress' => array(
					'label'       => __( 'Not-BuddyPress pages', 'woosidebars-buddypress' ),
					'description' => __( 'Applies to all pages except those of BuddyPress.', 'woosidebars-buddypress' )
				),

				// Registration page
				'bp-registration' => array(
					'label'       => __( 'Registration page', 'woosidebars-buddypress' ),
					'description' => __( 'The member registration page.', 'woosidebars-buddypress' )
				),

				// Activation page
				'bp-activation' => array(
					'label'       => __( 'Activation page', 'woosidebars-buddypress' ),
					'description' => __( 'The member activation page.', 'woosidebars-buddypress' )
				),

				// Directories
				'bp-directory' => array(
					'label'       => __( 'Directories', 'woosidebars-buddypress' ),
					'description' => __( 'Applies to all list pages.', 'woosidebars-buddypress' )
				),

				// Single items
				'bp-single-item' => array(
					'label'       => __( 'Single items', 'woosidebars-buddypress' ),
					'description' => __( 'Applies to all pages of a single item (member/group)', 'woosidebars-buddypress' )
				),
			),

			// BP Members
			'bp-members' => array(

				// Members Directory
				'bp-members' => array(
					'label'       => __( 'Members Directory', 'woosidebars-buddypress' ),
					'description' => __( 'The members list page.', 'woosidebars-buddypress' )
				),

				// Single member
				'bp-user' => array(
					'label'       => __( 'Single member', 'woosidebars-buddypress' ),
					'description' => __( 'The pages of a single member.', 'woosidebars-buddypress' )
				),
			)
		);

		// Define conditions for the logged-in user
		$loggedin = array(

			// My Account
			'bp-my-account' => array(
				'label'       => __( 'My Account', 'woosidebars-buddypress' ),
				'description' => __( "The logged-in member's account pages.", 'woosidebars-buddypress' )
			)
		);

		// Member types. Since BP 2.2
		if ( function_exists( 'bp_get_member_types' ) ) {
			$types = bp_get_member_types( array(), 'objects' );

			if ( ! empty( $types ) ) {
				$member_types = array();
				foreach ( $types as $type ) {
					$member_types["bp-member-type_{$type->name}"] = array(
						'label'       => sprintf( __( 'Member Type: %s', 'woosidebars-buddypress' ), $type->labels['singular_name'] ),
						'description' => sprintf( __( 'The displayed member is a %s.', 'woosidebars-buddypress' ), $type->labels['singular_name'] )
					);
				}

				// No member type
				$member_types['bp-member-type-none'] = array(
					'label'       => __( 'No member type', 'woosidebars-buddypress' ),
					'description' => __( 'The displayed member has no member type.', 'woosidebars-buddypress' )
				);

				// Append to members component conditions
				$conditions['bp-members'] += $member_types;
			}
		}

		/** Activity **********************************************************/

		// Activity component
		if ( bp_is_active( 'activity' ) ) {
			$activity_conditions = array(

				// Activity component
				'bp-activity-component' => array(
					'label'       => __( 'Activity pages', 'woosidebars-buddypress' ),
					'description' => __( 'Applies to all pages of the activity component.', 'woosidebars-buddypress' )
				),

				// Activity Directory
				'bp-activity' => array(
					'label'       => __( 'Activity Directory', 'woosidebars-buddypress' ),
					'description' => __( 'The activity list page.', 'woosidebars-buddypress' )
				),

				// Single activity
				'bp-activity-permalink' => array(
					'label'       => __( 'Single Activity', 'woosidebars-buddypress' ),
					'description' => __( 'The single activity page.', 'woosidebars-buddypress' )
				),
			);

			// Groups activity
			if ( bp_is_active( 'groups' ) ) {
				$activity_conditions['bp-groups-activity'] = array(
					'label'       => __( 'Group Activity', 'woosidebars-buddypress' ),
					'description' => __( 'The activity page for a group', 'woosidebars-buddypress' )
				);
			}

			// Friends activity
			if ( bp_is_active( 'friends' ) ) {
				$activity_conditions['bp-friends-activity'] = array(
					'label'       => __( 'Friends Activity', 'woosidebars-buddypress' ),
					'description' => __( "The activity page for a member's friends", 'woosidebars-buddypress' )
				);
			}

			$conditions['bp-activity'] = $activity_conditions;

			// My Activity
			$loggedin['bp-my-activity'] = array(
				'label'       => __( 'My Activity', 'woosidebars-buddypress' ),
				'description' => __( "The logged-in member's activity page.", 'woosidebars-buddypress' )
			);
		}

		/** Groups ************************************************************/

		// Groups component
		if ( bp_is_active( 'groups' ) ) {
			$groups_conditions = array(

				// Groups Directory
				'bp-groups' => array(
					'label'       => __( 'Groups Directory', 'woosidebars-buddypress' ),
					'description' => __( 'The groups list page.', 'woosidebars-buddypress' )
				),

				// Group Creation
				'bp-group-create' => array(
					'label'       => __( 'Group Creation', 'woosidebars-buddypress' ),
					'description' => __( 'The groups creation pages.', 'woosidebars-buddypress' )
				),

				// Leave Group
				'bp-leave-group' => array(
					'label'       => __( 'Leave Group', 'woosidebars-buddypress' ),
					'description' => __( 'The leave group page.', 'woosidebars-buddypress' )
				),

				// Group Invites
				'bp-group-invites' => array(
					'label'       => __( 'Group Invites', 'woosidebars-buddypress' ),
					'description' => __( 'The group invites page.', 'woosidebars-buddypress' )
				),

				// Group Members
				'bp-group-members' => array(
					'label'       => __( 'Group Members', 'woosidebars-buddypress' ),
					'description' => __( 'The group members page.', 'woosidebars-buddypress' )
				),

				// Group Administration
				'bp-group-admin' => array(
					'label'       => __( 'Group Administration', 'woosidebars-buddypress' ),
					'description' => __( 'The group administration pages.', 'woosidebars-buddypress' )
				),

				// Group Home
				'bp-group-home' => array(
					'label'       => __( 'Group Home', 'woosidebars-buddypress' ),
					'description' => __( 'The group home page.', 'woosidebars-buddypress' )
				),
			);

			// Group forums
			if ( bp_is_active( 'forums' ) ) {

				// Group Forum Topic
				$groups_conditions['bp-group-forum-topic'] = array(
					'label'       => __( 'Group Forum Topic', 'woosidebars-buddypress' ),
					'description' => __( 'The group forum topic page.', 'woosidebars-buddypress' )
				);

				// Group Forum Topic edit
				$groups_conditions['bp-group-forum-topic-edit'] = array(
					'label'       => __( 'Group Forum Topic Edit', 'woosidebars-buddypress' ),
					'description' => __( 'The group forum topic edit page.', 'woosidebars-buddypress' )
				);

				// Group Forum
				$groups_conditions['bp-group-forum'] = array(
					'label'       => __( 'Group Forum', 'woosidebars-buddypress' ),
					'description' => __( 'The group forum page.', 'woosidebars-buddypress' )
				);
			}

			// List available groups
			$groups = groups_get_groups( array( 'per_page' => -1, 'type' => 'alphabetical' ) );
			foreach ( $groups['groups'] as $group ) {
				$groups_conditions["bp-group-{$group->id}"] = array(
					'label'       => sprintf( __( 'Group: %s', 'woosidebars-buddypress' ), $group->name ),
					'description' => sprintf( __( 'The displayed group is %s.', 'woosidebars-buddypress' ), $group->name )
				);
			}

			$conditions['bp-groups'] = $groups_conditions;

			// My Groups
			$loggedin['bp-my-groups'] = array(
				'label'       => __( 'My Groups', 'woosidebars-buddypress' ),
				'description' => __( "The logged-in member's groups page.", 'woosidebars-buddypress' )
			);
		}

		/** XProfile **********************************************************/

		// XProfile component
		if ( bp_is_active( 'profile' ) ) {
			$profile_conditions = array(

				// Profile
				'bp-xprofile' => array(
					'label'       => __( 'Member Profile', 'woosidebars-buddypress' ),
					'description' => __( 'The member profile page.', 'woosidebars-buddypress' )
				),

				// Profile Edit
				'bp-profile-edit' => array(
					'label'       => __( 'Profile Edit', 'woosidebars-buddypress' ),
					'description' => __( 'The member profile edit page.', 'woosidebars-buddypress' )
				),

				// Change Avatar
				'bp-change-avatar' => array(
					'label'       => __( 'Change Avatar', 'woosidebars-buddypress' ),
					'description' => __( 'The member change avatar page.', 'woosidebars-buddypress' )
				),

			);

			// Append to Members conditions
			$conditions['bp-members'] += $profile_conditions;

			// My Profile
			$loggedin['bp-my-profile'] = array(
				'label'       => __( 'My Profile', 'woosidebars-buddypress' ),
				'description' => __( "The logged-in member's profile pages.", 'woosidebars-buddypress' )
			);
		}

		/** Friends ***********************************************************/

		// Friends component
		if ( bp_is_active( 'friends' ) ) {
			$friends_conditions = array(

				// Friends
				'bp-friends' => array(
					'label'       => __( 'Friends pages', 'woosidebars-buddypress' ),
					'description' => __( 'The members friends page.', 'woosidebars-buddypress' )
				),

				// Friend Requests
				'bp-friend-requests' => array(
					'label'       => __( 'Friend Requests', 'woosidebars-buddypress' ),
					'description' => __( 'The members friends requests page.', 'woosidebars-buddypress' )
				),

			);

			// Append to Members conditions
			$conditions['bp-members'] += $friends_conditions;

			// My Friends
			$loggedin['bp-my-friends'] = array(
				'label'       => __( 'My Friends', 'woosidebars-buddypress' ),
				'description' => __( "The logged-in member's friends page.", 'woosidebars-buddypress' )
			);
		}

		/** Messages **********************************************************/

		// Messages component
		if ( bp_is_active( 'messages' ) ) {
			$messages_conditions = array(

				// Messages
				'bp-messages' => array(
					'label'       => __( 'Messages pages', 'woosidebars-buddypress' ),
					'description' => __( 'The members messages pages.', 'woosidebars-buddypress' )
				),

				// Messages inbox
				'bp-inbox' => array(
					'label'       => __( 'Inbox', 'woosidebars-buddypress' ),
					'description' => __( 'The messages inbox page.', 'woosidebars-buddypress' )
				),

				// Messages sentbox
				'bp-sentbox' => array(
					'label'       => __( 'Sentbox', 'woosidebars-buddypress' ),
					'description' => __( 'The messages sentbox page.', 'woosidebars-buddypress' )
				),

				// Messages compose
				'bp-compose' => array(
					'label'       => __( 'Compose', 'woosidebars-buddypress' ),
					'description' => __( 'The messages compose page.', 'woosidebars-buddypress' )
				),

				// Messages notices
				'bp-notices' => array(
					'label'       => __( 'Notices', 'woosidebars-buddypress' ),
					'description' => __( 'The messages notices page.', 'woosidebars-buddypress' )
				)
			);

			// Create Messages conditions
			$conditions['bp-messages'] = $messages_conditions;

			// My Messages
			$loggedin['bp-my-messages'] = array(
				'label'       => __( 'My Messages', 'woosidebars-buddypress' ),
				'description' => __( "The logged-in member's messages page.", 'woosidebars-buddypress' )
			);
		}

		/** Notifications *****************************************************/

		// Notifications component
		if ( bp_is_active( 'notifications' ) ) {
			$notifications_conditions = array(

				// Notifications
				'bp-notifications' => array(
					'label'       => __( 'Notifications', 'woosidebars-buddypress' ),
					'description' => __( 'The members notifications page.', 'woosidebars-buddypress' )
				)
			);

			// Append to logged-in conditions
			$loggedin += $notifications_conditions;
		}

		/** Settings **********************************************************/

		// Settings component
		if ( bp_is_active( 'setings' ) ) {
			$setings_conditions = array(

				// Settings
				'bp-setings' => array(
					'label'       => __( 'Settings', 'woosidebars-buddypress' ),
					'description' => __( 'The members setings pages.', 'woosidebars-buddypress' )
				)
			);

			// Append to logged-in conditions
			$loggedin += $setings_conditions;
		}

		/** Blogs *************************************************************/

		// Blogs component
		if ( bp_is_active( 'blogs' ) ) {
			$blogs_conditions = array(

				// Blogs
				'bp-blogs' => array(
					'label'       => __( 'Blogs', 'woosidebars-buddypress' ),
					'description' => __( 'The members blogs pages.', 'woosidebars-buddypress' )
				),

				// Create blogs
				'bp-create-blog' => array(
					'label'       => __( 'Create blogs', 'woosidebars-buddypress' ),
					'description' => __( 'The blogs creation page.', 'woosidebars-buddypress' )
				),

				// Recent comments
				'bp-recent-comments' => array(
					'label'       => __( 'Recent Comments', 'woosidebars-buddypress' ),
					'description' => __( 'The recent blog comments page.', 'woosidebars-buddypress' )
				),
				// Recent posts
				'bp-recent-posts' => array(
					'label'       => __( 'Recent Posts', 'woosidebars-buddypress' ),
					'description' => __( 'The recent blog posts page.', 'woosidebars-buddypress' )
				),
			);

			// Append to Members conditions
			$conditions['bp-members'] += $blogs_conditions;

			// My blogs
			$loggedin['bp-my-blogs'] = array(
				'label'       => __( 'My Blogs', 'woosidebars-buddypress' ),
				'description' => __( "The logged-in member's blogs page.", 'woosidebars-buddypress' )
			);
		}

		/** Loggedin **********************************************************/

		// Append Loggedin conditions
		if ( ! empty( $loggedin ) ) {
			$conditions['bp-loggedin'] = $loggedin;
		}

		return $conditions;
	}

	/**
	 * Filter the contents of our tab in the conditions metabox
	 *
	 * @since 1.0.0
	 *
	 * @uses WooSidebars_BuddyPress::get_conditions()
	 * @uses get_post_meta()
	 * 
	 * @param string $tab Tab markup
	 * @return string Tab
	 */
	public function filter_conditions_tab( $tab ) {
		global $woosidebars;

		// Get our and the selected conditions
		$headings   = $woosidebars->conditions->conditions_headings;
		$conditions = $this->get_conditions();
		if ( ! $selected = get_post_meta( get_the_ID(), '_condition', false ) ) {
			$selected = array();
		}

		ob_start(); ?>

		<div id="tab-buddypress" class="condition-tab">

		<?php foreach ( $conditions as $component => $c ) : ?>
			<ul class="alignleft conditions-column"><?php

			// Display condition section heading
			if ( isset( $headings[ $component ] ) ) {
				?><li><h4><?php echo esc_html( $headings[ $component ] ); ?></h4></li><?php
			} ?>

				<?php foreach ( $c as $condition => $args ) : ?>

				<li>
					<label class="selectit" title="<?php echo esc_attr( $args['description'] ); ?>">
						<input type="checkbox" name="conditions[]" value="<?php echo $condition; ?>" id="checkbox-<?php echo $condition; ?>" <?php checked( in_array( $condition, $selected ) ); ?>/>
						<?php echo esc_html( $args['label'] ); ?>
					</label>
				</li>

				<?php endforeach; ?>
			</ul>
		<?php endforeach; ?>

		<?php

		// Override tab conents
		$tab = ob_get_clean();

		return $tab;
	}

	/**
	 * Register our conditions for the current page
	 *
	 * @since 1.0.0
	 *
	 * @uses is_buddypress()
	 * @uses bp_get_the_body_class()
	 * @uses bp_is_user()
	 * @uses bp_get_member_type()
	 * @uses bp_displayed_user_id()
	 * @uses bp_is_group()
	 * @uses groups_get_current_group()
	 * 
	 * @param array $conditions Condition keys
	 * @return array Conditions
	 */
	public function filter_page_conditions( $conditions ) {

		// Bail when BP does not apply
		if ( ! is_buddypress() ) {
			$conditions[] = 'not-buddypress';
			return $conditions;
		}

		// Get conditions from the body class
		$bp_classes = bp_get_the_body_class();

		// Force 'bp-' prefix for all classes and collect
		foreach ( $bp_classes as $class ) {
			if ( false === strpos( $class, 'bp-' ) && 'buddypress' !== $class ) {
				$class = "bp-{$class}";
			}

			$conditions[] = $class;
		}

		// Members: single user
		if ( bp_is_user() ) {

			// Member types
			if ( function_exists( 'bp_get_member_type' ) ) {
				$types = bp_get_member_type( bp_displayed_user_id(), false );
				if ( $types ) {
					foreach ( $types as $type ) {
						$conditions[] = "bp-member-type-{$type}";
					}
				} else {
					$conditions[] = 'bp-member-type-none';
				}
			}
		}

		// Groups: single group 
		if ( bp_is_group() ) {

			// Define by id
			$conditions[] = 'bp-group-' . groups_get_current_group()->id;
		}

		return $conditions;
	}
}

/**
 * Return single instance of this main plugin class
 *
 * @since 1.0.0
 * 
 * @return WooSidebars_BuddyPress
 */
function woosidebars_buddypress() {
	return WooSidebars_BuddyPress::instance();
}

// Initiate on 'bp_init'
add_action( 'bp_init', 'woosidebars_buddypress' );

endif; // class_exists
