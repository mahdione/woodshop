<?php
/**
 * GitHub release updates for the Wood Shop theme.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

define( 'WOODSHOP_GITHUB_REPO', 'mahdione/woodshop' );
define( 'WOODSHOP_GITHUB_SLUG', 'woodshop' );

/**
 * Fetch the latest GitHub release, with a short cache.
 *
 * Add a token with the `woodshop_github_token` filter or the
 * WOODSHOP_GITHUB_TOKEN constant if the repository is private.
 *
 * @return array<string, mixed>
 */
function woodshop_github_release() {
	$cache_key = 'woodshop_github_release_' . md5( WOODSHOP_GITHUB_REPO );
	$cached    = get_transient( $cache_key );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	$args = array(
		'timeout' => 12,
		'headers' => array(
			'Accept'     => 'application/vnd.github+json',
			'User-Agent' => 'Woodshop-Theme/' . wp_get_theme( WOODSHOP_GITHUB_SLUG )->get( 'Version' ),
		),
	);

	$token = defined( 'WOODSHOP_GITHUB_TOKEN' ) ? WOODSHOP_GITHUB_TOKEN : apply_filters( 'woodshop_github_token', '' );

	if ( $token ) {
		$args['headers']['Authorization'] = 'Bearer ' . $token;
	}

	$response = wp_remote_get(
		'https://api.github.com/repos/' . WOODSHOP_GITHUB_REPO . '/releases/latest',
		$args
	);

	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		set_transient( $cache_key, array(), HOUR_IN_SECONDS );
		return array();
	}

	$release = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( ! is_array( $release ) ) {
		set_transient( $cache_key, array(), HOUR_IN_SECONDS );
		return array();
	}

	set_transient( $cache_key, $release, 6 * HOUR_IN_SECONDS );

	return $release;
}

/**
 * Read the version and package URL from a GitHub release.
 *
 * @param array<string, mixed> $release Release payload.
 * @return array{version: string, package: string, url: string, body: string}
 */
function woodshop_github_release_info( $release ) {
	$tag     = isset( $release['tag_name'] ) ? (string) $release['tag_name'] : '';
	$version = ltrim( $tag, 'vV' );
	$package = '';

	if ( ! empty( $release['assets'] ) && is_array( $release['assets'] ) ) {
		foreach ( $release['assets'] as $asset ) {
			if ( isset( $asset['name'], $asset['browser_download_url'] ) && 'woodshop.zip' === $asset['name'] ) {
				$package = (string) $asset['browser_download_url'];
				break;
			}
		}
	}

	return array(
		'version' => $version,
		'package' => $package,
		'url'     => isset( $release['html_url'] ) ? (string) $release['html_url'] : 'https://github.com/' . WOODSHOP_GITHUB_REPO . '/releases',
		'body'    => isset( $release['body'] ) ? (string) $release['body'] : '',
	);
}

/**
 * Inject the GitHub release into the WordPress theme update transient.
 *
 * @param object $transient Theme update transient.
 * @return object
 */
function woodshop_github_update_check( $transient ) {
	if ( ! is_object( $transient ) || empty( $transient->checked ) ) {
		return $transient;
	}

	$theme = wp_get_theme( WOODSHOP_GITHUB_SLUG );

	if ( ! $theme->exists() ) {
		return $transient;
	}

	$info = woodshop_github_release_info( woodshop_github_release() );

	if ( ! $info['version'] || ! $info['package'] || ! version_compare( $info['version'], (string) $theme->get( 'Version' ), '>' ) ) {
		return $transient;
	}

	if ( ! isset( $transient->response ) || ! is_array( $transient->response ) ) {
		$transient->response = array();
	}

	$transient->response[ WOODSHOP_GITHUB_SLUG ] = array(
		'theme'        => WOODSHOP_GITHUB_SLUG,
		'new_version'  => $info['version'],
		'url'          => $info['url'],
		'package'      => $info['package'],
		'requires'     => (string) $theme->get( 'Requires at least' ),
		'requires_php' => (string) $theme->get( 'Requires PHP' ),
		'tested'       => (string) $theme->get( 'Tested up to' ),
	);

	unset( $transient->no_update[ WOODSHOP_GITHUB_SLUG ] );

	return $transient;
}
add_filter( 'pre_set_site_transient_update_themes', 'woodshop_github_update_check' );

/**
 * Provide release details in the theme information modal.
 *
 * @param object|false $result Theme information result.
 * @param string       $action API action.
 * @param object       $args   Request arguments.
 * @return object|false
 */
function woodshop_github_theme_info( $result, $action, $args ) {
	if ( 'theme_information' !== $action || empty( $args->slug ) || WOODSHOP_GITHUB_SLUG !== $args->slug ) {
		return $result;
	}

	$release = woodshop_github_release();
	$info    = woodshop_github_release_info( $release );

	if ( ! $info['version'] ) {
		return $result;
	}

	$theme = wp_get_theme( WOODSHOP_GITHUB_SLUG );

	return (object) array(
		'name'          => $theme->get( 'Name' ),
		'slug'          => WOODSHOP_GITHUB_SLUG,
		'version'       => $info['version'],
		'author'        => $theme->get( 'Author' ),
		'homepage'      => $info['url'],
		'requires'      => $theme->get( 'Requires at least' ),
		'requires_php'  => $theme->get( 'Requires PHP' ),
		'last_updated'  => isset( $release['published_at'] ) ? (string) $release['published_at'] : '',
		'download_link' => $info['package'],
		'sections'      => array(
			'description' => wpautop( esc_html( $theme->get( 'Description' ) ) ),
			'changelog'   => $info['body'] ? wpautop( esc_html( $info['body'] ) ) : esc_html__( 'See the GitHub release notes for details.', 'woodshop' ),
		),
	);
}
add_filter( 'themes_api', 'woodshop_github_theme_info', 10, 3 );

/**
 * Enable WordPress automatic updates for this theme.
 *
 * @param bool   $update Whether to auto-update.
 * @param object $item   Update item.
 * @return bool
 */
function woodshop_github_auto_update( $update, $item ) {
	if ( isset( $item->theme ) && WOODSHOP_GITHUB_SLUG === $item->theme ) {
		return true;
	}

	return $update;
}
add_filter( 'auto_update_theme', 'woodshop_github_auto_update', 10, 2 );

/**
 * Clear the release cache after a successful theme update.
 *
 * @param WP_Upgrader $upgrader Upgrader instance.
 * @param array       $options  Update options.
 */
function woodshop_github_clear_cache( $upgrader, $options ) {
	if ( isset( $options['type'] ) && 'theme' === $options['type'] ) {
		delete_transient( 'woodshop_github_release_' . md5( WOODSHOP_GITHUB_REPO ) );
	}
}
add_action( 'upgrader_process_complete', 'woodshop_github_clear_cache', 10, 2 );
