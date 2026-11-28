<?php
/**
 * Manual update screen for GitHub-hosted theme and plugin releases.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the Updates screen.
 */
function woodshop_updates_admin_menu() {
	add_submenu_page(
		'woodshop-studio',
		__( 'Updates', 'woodshop' ),
		__( 'Updates', 'woodshop' ),
		'manage_options',
		'woodshop-updates',
		'woodshop_updates_page'
	);
}
add_action( 'admin_menu', 'woodshop_updates_admin_menu', 21 );

/**
 * Current theme release status.
 *
 * @return array<string, mixed>
 */
function woodshop_updates_theme_status() {
	$theme   = wp_get_theme( WOODSHOP_GITHUB_SLUG );
	$release = woodshop_github_release();
	$info    = woodshop_github_release_info( $release );
	$current = (string) $theme->get( 'Version' );

	return array(
		'current' => $current,
		'latest'  => $info['version'],
		'package' => $info['package'],
		'url'     => $info['url'],
		'update'  => $info['version'] && $info['package'] && version_compare( $info['version'], $current, '>' ),
	);
}

/**
 * Current standalone plugin release status, when the plugin manages its own updates.
 *
 * @return array<string, mixed>|null
 */
function woodshop_updates_plugin_status() {
	if ( ! function_exists( 'woodshop_studio_github_release' ) || ! defined( 'WOODSHOP_STUDIO_BASENAME' ) ) {
		return null;
	}

	require_once ABSPATH . 'wp-admin/includes/plugin.php';

	if ( ! is_plugin_active( WOODSHOP_STUDIO_BASENAME ) ) {
		return null;
	}

	$release = woodshop_studio_github_release();
	$info    = woodshop_studio_github_release_info( $release );
	$current = defined( 'WOODSHOP_STUDIO_VERSION' ) ? WOODSHOP_STUDIO_VERSION : '';

	return array(
		'current' => $current,
		'latest'  => $info['version'],
		'package' => $info['package'],
		'url'     => $info['url'],
		'update'  => $info['version'] && $info['package'] && version_compare( $info['version'], $current, '>' ),
	);
}

/**
 * Store a notice for the next Updates screen request.
 *
 * @param string[] $messages Messages.
 * @param string   $type     Notice type.
 */
function woodshop_updates_redirect( $messages, $type = 'success' ) {
	set_transient(
		'woodshop_updates_notice',
		array(
			'type'     => $type,
			'messages' => $messages,
		),
		MINUTE_IN_SECONDS
	);

	wp_safe_redirect( admin_url( 'admin.php?page=woodshop-updates' ) );
	exit;
}

/**
 * Check both GitHub releases and refresh WordPress update data.
 */
function woodshop_updates_handle_check() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to do that.', 'woodshop' ) );
	}

	check_admin_referer( 'woodshop_updates_check', 'woodshop_updates_nonce' );

	$messages = array();

	delete_transient( 'woodshop_github_release_' . md5( WOODSHOP_GITHUB_REPO ) );
	$theme_info = woodshop_github_release_info( woodshop_github_release( true ) );

	if ( $theme_info['version'] ) {
		$theme = wp_get_theme( WOODSHOP_GITHUB_SLUG );
		$messages[] = version_compare( $theme_info['version'], (string) $theme->get( 'Version' ), '>' )
			? sprintf( __( 'Theme update available: %s.', 'woodshop' ), $theme_info['version'] )
			: sprintf( __( 'Theme is up to date (%s).', 'woodshop' ), $theme->get( 'Version' ) );
	} else {
		$messages[] = __( 'Could not read the latest theme release from GitHub. Check outbound HTTP requests or the GitHub token.', 'woodshop' );
	}

	if ( function_exists( 'woodshop_studio_github_release' ) ) {
		delete_transient( 'woodshop_studio_github_release_' . md5( WOODSHOP_STUDIO_GITHUB_REPO ) );
		$plugin_info = woodshop_studio_github_release_info( woodshop_studio_github_release( true ) );
		if ( $plugin_info['version'] ) {
			$messages[] = version_compare( $plugin_info['version'], WOODSHOP_STUDIO_VERSION, '>' )
				? sprintf( __( 'Plugin update available: %s.', 'woodshop' ), $plugin_info['version'] )
				: sprintf( __( 'Plugin is up to date (%s).', 'woodshop' ), WOODSHOP_STUDIO_VERSION );
		}
	}

	delete_site_transient( 'update_themes' );
	delete_site_transient( 'update_plugins' );
	wp_update_themes();
	wp_update_plugins();

	woodshop_updates_redirect( $messages );
}
add_action( 'admin_post_woodshop_updates_check', 'woodshop_updates_handle_check' );

/**
 * Install the latest theme release directly from GitHub.
 */
function woodshop_updates_handle_theme() {
	if ( ! current_user_can( 'update_themes' ) ) {
		wp_die( esc_html__( 'You do not have permission to update themes.', 'woodshop' ) );
	}

	check_admin_referer( 'woodshop_updates_theme', 'woodshop_updates_nonce' );

	$status = woodshop_updates_theme_status();

	if ( ! $status['update'] ) {
		woodshop_updates_redirect( array( __( 'The theme is already up to date.', 'woodshop' ) ) );
	}

	$transient = get_site_transient( 'update_themes' );
	if ( ! is_object( $transient ) ) {
		$transient = new stdClass();
	}
	if ( empty( $transient->response ) || ! is_array( $transient->response ) ) {
		$transient->response = array();
	}

	$theme = wp_get_theme( WOODSHOP_GITHUB_SLUG );
	$transient->response[ WOODSHOP_GITHUB_SLUG ] = array(
		'theme'       => WOODSHOP_GITHUB_SLUG,
		'new_version' => $status['latest'],
		'url'         => $status['url'],
		'package'     => $status['package'],
	);
	set_site_transient( 'update_themes', $transient );

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/misc.php';
	require_once ABSPATH . 'wp-admin/includes/update.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

	$upgrader = new Theme_Upgrader( new Automatic_Upgrader_Skin() );
	$result   = $upgrader->upgrade( WOODSHOP_GITHUB_SLUG );

	delete_site_transient( 'update_themes' );
	delete_transient( 'woodshop_github_release_' . md5( WOODSHOP_GITHUB_REPO ) );

	if ( is_wp_error( $result ) ) {
		woodshop_updates_redirect( array( $result->get_error_message() ), 'error' );
	}

	if ( ! $result ) {
		woodshop_updates_redirect( array( __( 'The theme update could not be installed. Check file permissions and try again.', 'woodshop' ) ), 'error' );
	}

	woodshop_updates_redirect( array( sprintf( __( 'Theme updated to %s.', 'woodshop' ), $status['latest'] ) ) );
}
add_action( 'admin_post_woodshop_updates_theme', 'woodshop_updates_handle_theme' );

/**
 * Install the latest standalone plugin release directly from GitHub.
 */
function woodshop_updates_handle_plugin() {
	if ( ! current_user_can( 'update_plugins' ) ) {
		wp_die( esc_html__( 'You do not have permission to update plugins.', 'woodshop' ) );
	}

	check_admin_referer( 'woodshop_updates_plugin', 'woodshop_updates_nonce' );

	$status = woodshop_updates_plugin_status();

	if ( ! $status ) {
		woodshop_updates_redirect( array( __( 'The standalone plugin is not active; it updates together with the theme.', 'woodshop' ) ), 'error' );
	}

	if ( ! $status['update'] ) {
		woodshop_updates_redirect( array( __( 'The plugin is already up to date.', 'woodshop' ) ) );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/misc.php';
	require_once ABSPATH . 'wp-admin/includes/update.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

	/*
	 * Use install() with overwrite instead of upgrade(). The regular plugin
	 * upgrader deactivates active plugins during upgrade and does not turn them
	 * back on in a direct admin-post request.
	 */
	$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
	$result   = $upgrader->install( $status['package'], array( 'overwrite_package' => true ) );

	delete_site_transient( 'update_plugins' );
	delete_transient( 'woodshop_studio_github_release_' . md5( WOODSHOP_STUDIO_GITHUB_REPO ) );

	if ( is_wp_error( $result ) ) {
		woodshop_updates_redirect( array( $result->get_error_message() ), 'error' );
	}

	if ( ! $result ) {
		woodshop_updates_redirect( array( __( 'The plugin update could not be installed. Check file permissions and try again.', 'woodshop' ) ), 'error' );
	}

	woodshop_updates_redirect( array( sprintf( __( 'Plugin updated to %s.', 'woodshop' ), $status['latest'] ) ) );
}
add_action( 'admin_post_woodshop_updates_plugin', 'woodshop_updates_handle_plugin' );

/**
 * Render the manual update screen.
 */
function woodshop_updates_page() {
	$theme  = woodshop_updates_theme_status();
	$plugin = woodshop_updates_plugin_status();
	$notice = get_transient( 'woodshop_updates_notice' );

	if ( is_array( $notice ) ) {
		delete_transient( 'woodshop_updates_notice' );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Wood Shop Updates', 'woodshop' ); ?></h1>

		<?php if ( is_array( $notice ) ) : ?>
			<div class="notice notice-<?php echo esc_attr( 'error' === ( $notice['type'] ?? '' ) ? 'error' : 'success' ); ?>">
				<?php foreach ( (array) ( $notice['messages'] ?? array() ) as $message ) : ?>
					<p><?php echo esc_html( $message ); ?></p>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<p>
			<?php esc_html_e( 'Use this screen when the normal WordPress update check does not find a new GitHub release. It checks GitHub directly and can install the release without waiting for WP-Cron.', 'woodshop' ); ?>
		</p>

		<div class="card" style="max-width:760px;padding:20px;margin-bottom:20px">
			<h2 style="margin-top:0"><?php esc_html_e( 'Theme', 'woodshop' ); ?></h2>
			<p>
				<?php esc_html_e( 'Current version:', 'woodshop' ); ?> <strong><?php echo esc_html( $theme['current'] ); ?></strong><br>
				<?php esc_html_e( 'Latest GitHub release:', 'woodshop' ); ?>
				<strong><?php echo $theme['latest'] ? esc_html( $theme['latest'] ) : esc_html__( 'Could not be checked', 'woodshop' ); ?></strong>
			</p>

			<?php if ( $theme['latest'] && $theme['url'] ) : ?>
				<p><a href="<?php echo esc_url( $theme['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open GitHub release', 'woodshop' ); ?></a></p>
			<?php endif; ?>

			<?php if ( $theme['update'] ) : ?>
				<p><strong><?php esc_html_e( 'A theme update is available.', 'woodshop' ); ?></strong></p>
				<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
					<?php wp_nonce_field( 'woodshop_updates_theme', 'woodshop_updates_nonce' ); ?>
					<input type="hidden" name="action" value="woodshop_updates_theme">
					<?php submit_button( __( 'Update theme now', 'woodshop' ), 'primary', 'submit', false ); ?>
				</form>
			<?php else : ?>
				<p><?php esc_html_e( 'Theme is up to date.', 'woodshop' ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( $plugin ) : ?>
			<div class="card" style="max-width:760px;padding:20px;margin-bottom:20px">
				<h2 style="margin-top:0"><?php esc_html_e( 'Standalone Wood Shop Studio plugin', 'woodshop' ); ?></h2>
				<p>
					<?php esc_html_e( 'Current version:', 'woodshop' ); ?> <strong><?php echo esc_html( $plugin['current'] ); ?></strong><br>
					<?php esc_html_e( 'Latest GitHub release:', 'woodshop' ); ?>
					<strong><?php echo $plugin['latest'] ? esc_html( $plugin['latest'] ) : esc_html__( 'Could not be checked', 'woodshop' ); ?></strong>
				</p>

				<?php if ( $plugin['update'] ) : ?>
					<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
						<?php wp_nonce_field( 'woodshop_updates_plugin', 'woodshop_updates_nonce' ); ?>
						<input type="hidden" name="action" value="woodshop_updates_plugin">
						<?php submit_button( __( 'Update plugin now', 'woodshop' ), 'primary', 'submit', false ); ?>
					</form>
				<?php else : ?>
					<p><?php esc_html_e( 'Plugin is up to date.', 'woodshop' ); ?></p>
				<?php endif; ?>
			</div>
		<?php else : ?>
			<div class="card" style="max-width:760px;padding:20px;margin-bottom:20px">
				<p><?php esc_html_e( 'Wood Shop Studio is bundled with the theme. It is updated together with the theme.', 'woodshop' ); ?></p>
			</div>
		<?php endif; ?>

		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<?php wp_nonce_field( 'woodshop_updates_check', 'woodshop_updates_nonce' ); ?>
			<input type="hidden" name="action" value="woodshop_updates_check">
			<?php submit_button( __( 'Check for updates now', 'woodshop' ), 'secondary', 'submit', false ); ?>
		</form>
	</div>
	<?php
}
