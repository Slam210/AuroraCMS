<?php
/**
 * Handles compatibility checks for Akismet with other plugins.
 *
 * @package Akismet
 * @since 5.4.0
 */

declare( strict_types = 1 );

// Following existing Akismet convention for file naming.
// phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase

/**
 * Class for managing compatibility checks for Akismet with other plugins.
 *
 * This class includes methods for determining whether specific plugins are
 * installed and active relative to the ability to work with Akismet.
 */
class Akismet_Compatible_Plugins {
	/**
	 * The endpoint for the compatible plugins API.
	 *
	 * @var string
	 */
	protected const COMPATIBLE_PLUGIN_ENDPOINT = 'https://rest.akismet.com/1.2/compatible-plugins';

	/**
	 * The error key for the compatible plugins API error.
	 *
	 * @var string
	 */
	protected const COMPATIBLE_PLUGIN_API_ERROR = 'akismet_compatible_plugins_api_error';

	/**
	 * The valid fields for a compatible plugin object.
	 *
	 * @var array
	 */
	protected const COMPATIBLE_PLUGIN_FIELDS = array(
		'slug',
		'name',
		'logo',
		'help_url',
		'path',
	);

	/**
	 * The cache key for the compatible plugins.
	 *
	 * @var string
	 */
	protected const CACHE_KEY = 'akismet_compatible_plugin_list';

	/**
	 * The cache group for things cached in this class.
	 *
	 * @var string
	 */
	protected const CACHE_GROUP = 'akismet_compatible_plugins';

	/**
	 * How many plugins should be visible by default?
	 *
	 * @var int
	 */
	public const DEFAULT_VISIBLE_PLUGIN_COUNT = 2;

	/**
	 * Retrieve the compatible plugins that are installed and currently active on the site or network.
	 *
	 * @return WP_Error|array WP_Error with code self::COMPATIBLE_PLUGIN_API_ERROR on failure; otherwise an associative array keyed by plugin slug containing metadata:
	 *     @type array $<slug> {
	 *         @type string $name     The display name of the plugin.
	 *         @type string $help_url URL to the plugin's help documentation.
	 *         @type string $logo     URL or path to the plugin's logo.
	 *         @type string $path     Filesystem plugin path (e.g., "plugin-dir/plugin-file.php").
	 *     }
	 */
	public static function get_installed_compatible_plugins() {
		// Retrieve and validate the full compatible plugins list.
		$compatible_plugins = static::get_compatible_plugins();

		if ( empty( $compatible_plugins ) ) {
			return new WP_Error(
				self::COMPATIBLE_PLUGIN_API_ERROR,
				__( 'Error getting compatible plugins.', 'akismet' )
			);
		}

		// Retrieve all installed plugins once.
		$all_plugins = get_plugins();

		// Build list of compatible plugins that are both installed and active.
		$active_compatible_plugins = array();

		foreach ( $compatible_plugins as $slug => $data ) {
			$path = $data['path'];
			// Skip if not installed.
			if ( ! isset( $all_plugins[ $path ] ) ) {
				continue;
			}
			// Check activation: per-site or network-wide (multisite).
			$site_active    = is_plugin_active( $path );
			$network_active = is_multisite() && is_plugin_active_for_network( $path );
			if ( $site_active || $network_active ) {
				$active_compatible_plugins[ $slug ] = $data;
			}
		}

		return $active_compatible_plugins;
	}

	/**
	 * Registers activation and deactivation hooks that respond to plugin list changes.
	 */
	public static function init(): void {
		add_action( 'activated_plugin', array( static::class, 'handle_plugin_change' ), true );
		add_action( 'deactivated_plugin', array( static::class, 'handle_plugin_change' ), true );
	}

	/**
		 * Invalidate the cached compatible-plugins list when a plugin is activated or deactivated.
		 *
		 * Checks the cached compatible plugins and purges the cache if the changed plugin's
		 * main file path (relative to the plugins directory) appears in that cached list.
		 *
		 * @param string $plugin Path to the plugin's main file relative to the plugins directory (e.g. "plugin-folder/plugin-file.php").
		 */
	public static function handle_plugin_change( string $plugin ): void {
		$cached_plugins = static::get_cached_plugins();

		/**
		 * Terminate if nothing's cached.
		 */
		if ( false === $cached_plugins ) {
			return;
		}

		$plugin_change_should_invalidate_cache = in_array( $plugin, array_column( $cached_plugins, 'path' ) );

		/**
		 * Purge the cache if the plugin is activated or deactivated.
		 */
		if ( $plugin_change_should_invalidate_cache ) {
			static::purge_cache();
		}
	}

	/**
	 * Fetches the list of Akismet-compatible plugins and returns them indexed by slug.
	 *
	 * Retrieves cached data when available; otherwise requests the remote compatible-plugins
	 * endpoint, validates and sanitizes the response, stores the result in cache, and returns it.
	 *
	 * @return array<string, array> Associative array keyed by plugin slug. Each value is a plugin
	 *                             data array containing the keys `slug`, `name`, `logo`, `help_url`,
	 *                             and `path`. Returns an empty array if no compatible plugins are
	 *                             available or if retrieval/validation fails.
	 */
	private static function get_compatible_plugins(): array {
		// Return cached result if present (false => cache miss; empty array is valid).
		$cached_plugins = static::get_cached_plugins();

		if ( $cached_plugins ) {
			return $cached_plugins;
		}

		$response = wp_remote_get(
			self::COMPATIBLE_PLUGIN_ENDPOINT
		);

		$sanitized = static::validate_compatible_plugin_response( $response );

		if ( false === $sanitized ) {
			return array();
		}

		/**
		 * Sets local static associative array of plugin data keyed by plugin slug.
		 */
		$compatible_plugins = array();

		foreach ( $sanitized as $plugin ) {
			$compatible_plugins[ $plugin['slug'] ] = $plugin;
		}

		static::set_cached_plugins( $compatible_plugins );

		return $compatible_plugins;
	}

	/**
	 * Validate and sanitize a response from the Compatible Plugins API.
	 *
	 * @param array|WP_Error $response The raw response returned by wp_remote_get() (array with headers and body) or a WP_Error on failure.
	 * @return array|false Array of sanitized compatible plugin entries on success, `false` if the response is invalid or represents an error.
	 */
	private static function validate_compatible_plugin_response( $response ) {
		/**
		 * Terminates the function if the response is a WP_Error object.
		 */
		if ( is_wp_error( $response ) ) {
			return false;
		}

		/**
		 * The response returned is an array of header + body string data.
		 * This pops off the body string for processing.
		 */
		$response_body = wp_remote_retrieve_body( $response );

		if ( empty( $response_body ) ) {
			return false;
		}

		$plugins = json_decode( $response_body, true );

		if ( false === is_array( $plugins ) ) {
			return false;
		}

		foreach ( $plugins as $plugin ) {
			if ( ! is_array( $plugin ) ) {
				/**
				 * Skips to the next iteration if for some reason the plugin is not an array.
				 */
				continue;
			}

			// Ensure that the plugin config read in from the API has all the required fields.
			$plugin_key_count = count(
				array_intersect_key( $plugin, array_flip( static::COMPATIBLE_PLUGIN_FIELDS ) )
			);

			$does_not_have_all_required_fields = ! (
				$plugin_key_count === count( static::COMPATIBLE_PLUGIN_FIELDS )
			);

			if ( $does_not_have_all_required_fields ) {
				return false;
			}

			if ( false === static::has_valid_plugin_path( $plugin['path'] ) ) {
				return false;
			}
		}

		return static::sanitize_compatible_plugin_response( $plugins );
	}

	/**
	 * Determine whether a plugin path matches the expected 'plugin-folder/plugin-file.php' pattern.
	 *
	 * @param string $path The plugin path to validate.
	 * @return bool `true` if the path matches the pattern (folder/file.php) allowing letters, digits, dots, underscores, and dashes in the folder and underscores/dashes/digits/letters in the file name; `false` otherwise.
	 */
	private static function has_valid_plugin_path( string $path ): bool {
		return preg_match( '/^[a-zA-Z0-9._-]+\/[a-zA-Z0-9_-]+\.php$/', $path ) === 1;
	}

	/**
	 * Sanitizes plugin entries returned by the Compatible Plugins API.
	 *
	 * Applies text sanitization to each field and URL sanitization specifically to
	 * the `help_url` and `logo` fields.
	 *
	 * @param array $plugins Raw plugin entries (typically keyed by slug).
	 * @return array The sanitized plugin entries.
	 */
	private static function sanitize_compatible_plugin_response( array $plugins = array() ): array {
		foreach ( $plugins as $key => $plugin ) {
			$plugins[ $key ]             = array_map( 'sanitize_text_field', $plugin );
			$plugins[ $key ]['help_url'] = sanitize_url( $plugins[ $key ]['help_url'] );
			$plugins[ $key ]['logo']     = sanitize_url( $plugins[ $key ]['logo'] );
		}

		return $plugins;
	}

	/**
	 * Store the provided compatible plugins in the current blog's object cache.
	 *
	 * The expected structure is an array indexed by plugin slug, where each value
	 * is an associative array containing the fields: `slug`, `name`, `logo`,
	 * `help_url`, and `path`.
	 *
	 * @param array $plugins Compatible plugins indexed by slug with required fields.
	 * @return bool `true` on successful cache set, `false` on failure.
	 */
	private static function set_cached_plugins( array $plugins ): bool {
		$_blog_id = (int) get_current_blog_id();

		return wp_cache_set(
			static::CACHE_KEY . "_$_blog_id",
			$plugins,
			static::CACHE_GROUP . "_$_blog_id",
			DAY_IN_SECONDS
		);
	}

	/**
	 * Retrieve the per-blog cached compatible plugins list.
	 *
	 * @return array|false The cached plugins indexed by slug, or `false` if no cache exists.
	 */
	private static function get_cached_plugins() {
		$_blog_id = (int) get_current_blog_id();

		return wp_cache_get(
			static::CACHE_KEY . "_$_blog_id",
			static::CACHE_GROUP . "_$_blog_id"
		);
	}

	/**
	 * Purges the cached compatible plugins for the current blog.
	 *
	 * @return bool `true` if the cache entry was successfully deleted, `false` otherwise.
	 */
	private static function purge_cache(): bool {
		$_blog_id = (int) get_current_blog_id();

		return wp_cache_delete(
			static::CACHE_KEY . "_$_blog_id",
			static::CACHE_GROUP . "_$_blog_id"
		);
	}
}