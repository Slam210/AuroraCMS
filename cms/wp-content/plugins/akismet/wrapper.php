<?php

global $wpcom_api_key, $akismet_api_host, $akismet_api_port;

$wpcom_api_key    = defined( 'WPCOM_API_KEY' ) ? constant( 'WPCOM_API_KEY' ) : '';
$akismet_api_host = Akismet::get_api_key() . '.rest.akismet.com';
$akismet_api_port = 80;

/**
 * Determine whether Akismet is running in test mode.
 *
 * @return bool `true` if Akismet is in test mode, `false` otherwise.
 */
function akismet_test_mode() {
	return Akismet::is_test_mode();
}

/**
 * Send an HTTP POST to the Akismet API after normalizing the request path.
 *
 * The function removes any '/1.1/' segment from the provided path and forwards
 * the request to Akismet::http_post.
 *
 * @param string $request The raw request body to send.
 * @param string $host The API host provided by caller (kept for compatibility; not used by this wrapper).
 * @param string $path The API path to call; any '/1.1/' segment will be removed.
 * @param int    $port TCP port to use for the request (default 80).
 * @param string|null $ip Optional IP address to include with the request.
 * @return mixed The HTTP response from Akismet::http_post.
 */
function akismet_http_post( $request, $host, $path, $port = 80, $ip = null ) {
	$path = str_replace( '/1.1/', '', $path );

	return Akismet::http_post( $request, $path, $ip );
}

/**
 * Get the current time with microsecond precision.
 *
 * @return float Current time in seconds including microseconds (fractional seconds).
 */
function akismet_microtime() {
	return Akismet::_get_microtime();
}

/**
 * Deletes stored comment records that are considered old by Akismet's retention policy.
 *
 * @return mixed The result of the deletion operation (implementation-specific; for example, number of deleted items or a boolean status).
 */
function akismet_delete_old() {
	return Akismet::delete_old_comments();
}

/**
 * Delete old Akismet-related comment metadata.
 *
 * @return mixed The result of the deletion operation (may be a count of deleted metadata entries or `false` on failure).
 */
function akismet_delete_old_metadata() {
	return Akismet::delete_old_comments_meta();
}

/**
 * Rechecks a stored comment by its database ID using the given recheck reason.
 *
 * @param int    $id             The comment ID to recheck.
 * @param string $recheck_reason A brief reason for rechecking the comment (default 'recheck_queue').
 * @return bool `true` if the recheck was performed successfully, `false` otherwise.
 */
function akismet_check_db_comment( $id, $recheck_reason = 'recheck_queue' ) {
	return Akismet::check_db_comment( $id, $recheck_reason );
}

/**
 * Retrieve Akismet "right now" dashboard statistics.
 *
 * @return array|false Array of dashboard statistics when available, `false` if the Akismet_Admin class is not present.
 */
function akismet_rightnow() {
	if ( ! class_exists( 'Akismet_Admin' ) ) {
		return false;
	}

	return Akismet_Admin::rightnow_stats();
}

/**
 * Deprecated initialization shim for Akismet admin that triggers a deprecation notice.
 *
 * @deprecated 3.0 This function is deprecated and retained only to emit a deprecation notice.
 */
function akismet_admin_init() {
	_deprecated_function( __FUNCTION__, '3.0' );
}
/**
 * Emit a deprecation notice for the Akismet version warning.
 *
 * Marks this wrapper as deprecated as of 3.0 and informs developers that it should no longer be used.
 *
 * @deprecated 3.0
 */
function akismet_version_warning() {
	_deprecated_function( __FUNCTION__, '3.0' );
}
/**
 * Deprecated placeholder for loading Akismet JavaScript and CSS.
 *
 * @deprecated 3.0 This function is deprecated and no longer performs any action.
 */
function akismet_load_js_and_css() {
	_deprecated_function( __FUNCTION__, '3.0' );
}
/**
 * Output a WordPress nonce hidden field for the specified action.
 *
 * @param string|int $action Action name to protect with the nonce; use -1 to use the default action.
 */
function akismet_nonce_field( $action = -1 ) {
	return wp_nonce_field( $action );
}
/**
 * Modify the Akismet plugin action links displayed on the Plugins page.
 *
 * @param array  $links Current action links for the plugin.
 * @param string $file  Path to the plugin file being rendered.
 * @return array The modified list of action links. 
 */
function akismet_plugin_action_links( $links, $file ) {
	return Akismet_Admin::plugin_action_links( $links, $file );
}
/**
 * Deprecated placeholder retained for backward compatibility.
 *
 * Triggers a deprecation notice and no longer performs any configuration.
 *
 * @deprecated 3.0 This function is deprecated and has no effect.
 */
function akismet_conf() {
	_deprecated_function( __FUNCTION__, '3.0' );
}
/**
 * Display Akismet statistics in the admin dashboard (deprecated).
 *
 * Triggers a deprecation notice and performs no action.
 *
 * @deprecated 3.0 This function is deprecated and no longer performs any behavior.
 */
function akismet_stats_display() {
	_deprecated_function( __FUNCTION__, '3.0' );
}
/**
 * Retrieve Akismet dashboard statistics.
 *
 * @return array The dashboard statistics as an associative array keyed by metric name.
 */
function akismet_stats() {
	return Akismet_Admin::dashboard_stats();
}
/**
 * Deprecated: previously displayed Akismet admin warnings in the WordPress dashboard.
 *
 * @deprecated 3.0 This function is deprecated and no longer performs any action.
 */
function akismet_admin_warnings() {
	_deprecated_function( __FUNCTION__, '3.0' );
}
/**
 * Modify the comment row actions to include Akismet-related links.
 *
 * @param array               $a       The existing row action links.
 * @param WP_Comment|int|object $comment The comment being processed (object or comment ID).
 * @return array The row action links, potentially augmented with Akismet actions.
 */
function akismet_comment_row_action( $a, $comment ) {
	return Akismet_Admin::comment_row_actions( $a, $comment );
}
/ **
 * Render or return the Akismet comment status meta box for a given comment.
 *
 * @param WP_Comment|int $comment The comment object or comment ID to render the meta box for.
 * @return mixed The result of the comment status meta box renderer (HTML output or renderer-specific value).
 */
function akismet_comment_status_meta_box( $comment ) {
	return Akismet_Admin::comment_status_meta_box( $comment );
}
/**
 * Deprecated shim for the comments columns filter; returns the columns unchanged.
 *
 * @param array $columns The existing comment columns.
 * @return array The same `$columns` array that was passed in.
 * @deprecated 3.0 This function is deprecated and preserved only for backward compatibility.
 */
function akismet_comments_columns( $columns ) {
	_deprecated_function( __FUNCTION__, '3.0' );

	return $columns;
}
/**
 * Deprecated shim for legacy comment column rendering.
 *
 * This function is deprecated as of 3.0 and retained only to trigger a deprecation notice when called.
 *
 * @param string $column     The comment column name.
 * @param int    $comment_id The comment ID.
 * @deprecated 3.0
 */
function akismet_comment_column_row( $column, $comment_id ) {
	_deprecated_function( __FUNCTION__, '3.0' );
}
/**
 * Produces an HTML link replacement for a regex match inside comment text.
 *
 * @param array $m The preg_replace_callback match array.
 * @return string The replacement string (HTML anchor) for the matched text.
 */
function akismet_text_add_link_callback( $m ) {
	return Akismet_Admin::text_add_link_callback( $m );
}
/**
 * Add a CSS class to links found in a comment's text.
 *
 * @param string $comment_text The comment content (HTML/text) to modify.
 * @return string The comment content with classes added to anchor (`<a>`) elements where applicable.
 */
function akismet_text_add_link_class( $comment_text ) {
	return Akismet_Admin::text_add_link_class( $comment_text );
}
/**
 * Produce the "Check for Spam" admin button for a given comment status.
 *
 * @param string $comment_status The current status of the comment.
 * @return string HTML markup for the "Check for Spam" button.
 */
function akismet_check_for_spam_button( $comment_status ) {
	return Akismet_Admin::check_for_spam_button( $comment_status );
}
/**
 * Submit a comment to Akismet as not spam.
 *
 * @param int $comment_id The ID of the comment to mark as non-spam.
 * @return mixed The result of the submission operation. */
function akismet_submit_nonspam_comment( $comment_id ) {
	return Akismet::submit_nonspam_comment( $comment_id );
}
/**
 * Marks the specified comment as spam and submits it to Akismet.
 *
 * @param int $comment_id The ID of the comment to submit as spam.
 * @return bool `true` if the spam submission succeeded, `false` otherwise.
 */
function akismet_submit_spam_comment( $comment_id ) {
	return Akismet::submit_spam_comment( $comment_id );
}
/**
 * Handles a comment status change for Akismet processing.
 *
 * @param string $new_status The new comment status (e.g., 'approved', 'spam', 'hold').
 * @param string $old_status The previous comment status.
 * @param mixed  $comment    The comment being transitioned; accepted formats include a WP_Comment object, comment array, or comment ID.
 * @return mixed The result of processing the status transition.
function akismet_transition_comment_status( $new_status, $old_status, $comment ) {
	return Akismet::transition_comment_status( $new_status, $old_status, $comment );
}
/**
 * Retrieve the current spam count, optionally filtered by type.
 *
 * @param mixed $type Optional type filter; pass `false` (default) to get the total spam count.
 * @return int The number of spam items matching the filter.
 */
function akismet_spam_count( $type = false ) {
	return Akismet_Admin::get_spam_count( $type );
}
/**
 * Triggers a recheck of comments in the recheck queue.
 *
 * @return mixed The result returned by Akismet_Admin::recheck_queue().
 */
function akismet_recheck_queue() {
	return Akismet_Admin::recheck_queue();
}
function akismet_remove_comment_author_url() {
	return Akismet_Admin::remove_comment_author_url();
}
/**
 * Appends the comment author's URL to the displayed comment author output.
 *
 * @return string The (possibly modified) comment author output including the author's URL.
 */
function akismet_add_comment_author_url() {
	return Akismet_Admin::add_comment_author_url();
}
/**
 * Performs a connectivity check to the Akismet service.
 *
 * @return mixed Connectivity status and diagnostic information from the check. 
 */
function akismet_check_server_connectivity() {
	return Akismet_Admin::check_server_connectivity();
}
/**
 * Retrieve cached Akismet server connectivity information.
 *
 * @param int $cache_timeout Number of seconds to cache the connectivity result.
 * @return array An associative array containing server connectivity information.
 */
function akismet_get_server_connectivity( $cache_timeout = 86400 ) {
	return Akismet_Admin::get_server_connectivity( $cache_timeout );
}
/**
 * Indicates whether Akismet server connectivity is considered OK.
 *
 * This function is deprecated as of 3.0. It remains for backward compatibility and always returns `true`.
 *
 * @deprecated 3.0
 * @return bool `true` (this wrapper always returns `true` for backward compatibility).
 */
function akismet_server_connectivity_ok() {
	_deprecated_function( __FUNCTION__, '3.0' );

	return true;
}
/**
 * Add Akismet pages to the WordPress admin menu.
 *
 * @return mixed The return value from the Akismet admin menu handler.
 */
function akismet_admin_menu() {
	return Akismet_Admin::admin_menu();
}
/**
 * Load the Akismet admin menu.
 *
 * @return string|void Admin menu HTML when available; otherwise nothing. 
 */
function akismet_load_menu() {
	return Akismet_Admin::load_menu();
}
/**
 * Deprecated placeholder retained for backward compatibility.
 *
 * This function exists only to signal that `akismet_init` is deprecated and should no longer be used.
 *
 * @deprecated 3.0 No replacement — function kept to preserve backward-compatible calls. 
 */
function akismet_init() {
	_deprecated_function( __FUNCTION__, '3.0' );
}
/**
 * Retrieve the current Akismet API key.
 *
 * @return string The Akismet API key, or an empty string if no key is configured.
 */
function akismet_get_key() {
	return Akismet::get_api_key();
}
/**
 * Checks the validity and status of an Akismet API key.
 *
 * @param string $key The Akismet API key to verify.
 * @param string|null $ip Optional IP address to include with the verification request.
 * @return mixed The response from Akismet describing the key status and any related metadata.
 */
function akismet_check_key_status( $key, $ip = null ) {
	return Akismet::check_key_status( $key, $ip );
}
/**
 * Update the stored Akismet alert state using a response from the Akismet service.
 *
 * @param mixed $response The response received from the Akismet service (API response payload).
 * @return mixed The result returned by the alert update operation.
 */
function akismet_update_alert( $response ) {
	return Akismet::update_alert( $response );
}
/**
 * Verify an Akismet API key, optionally using an IP address for verification context.
 *
 * @param string $key The API key to verify.
 * @param string|null $ip Optional IP address to include with the verification request.
 * @return bool `true` if the API key is valid, `false` otherwise.
 */
function akismet_verify_key( $key, $ip = null ) {
	return Akismet::verify_key( $key, $ip );
}
/**
 * Retrieve the roles assigned to a user.
 *
 * @param int $user_id The ID of the user.
 * @return string[] An array of role identifiers assigned to the user; empty array if none. 
 */
function akismet_get_user_roles( $user_id ) {
	return Akismet::get_user_roles( $user_id );
}
/**
 * Determine whether a comment approval status represents spam.
 *
 * @param mixed $approved The comment's approval status (value stored in `comment_approved`).
 * @return bool `true` if the status indicates spam, `false` otherwise.
 */
function akismet_result_spam( $approved ) {
	return Akismet::comment_is_spam( $approved );
}
/**
 * Determine whether a comment should be held for moderation.
 *
 * @param mixed $approved The comment's approval value (e.g., '1', '0', 'spam', or other approval indicator).
 * @return bool `true` if the comment should be held for moderation, `false` otherwise.
 */
function akismet_result_hold( $approved ) {
	return Akismet::comment_needs_moderation( $approved );
}
/**
 * Retrieve the number of approved comments that match a user's identity.
 *
 * @param int    $user_id              User ID to match (0 if not applicable).
 * @param string $comment_author_email Email address used to match the comment author.
 * @param string $comment_author       Comment author name used to match comments.
 * @param string $comment_author_url   Comment author URL used to match comments.
 * @return int The count of approved comments matching the provided user identity.
 */
function akismet_get_user_comments_approved( $user_id, $comment_author_email, $comment_author, $comment_author_url ) {
	return Akismet::get_user_comments_approved( $user_id, $comment_author_email, $comment_author, $comment_author_url );
}
/**
 * Append a message to a comment's Akismet history.
 *
 * @param int $comment_id The comment ID whose history will be updated.
 * @param string $message A descriptive message to record in the comment history.
 * @param string|null $event Optional event name or category for the history entry.
 * @return bool True on success, false on failure.
 */
function akismet_update_comment_history( $comment_id, $message, $event = null ) {
	return Akismet::update_comment_history( $comment_id, $message, $event );
}
/**
 * Retrieves the stored Akismet moderation history for a comment.
 *
 * @param int $comment_id The comment ID whose history to retrieve.
 * @return array An array of history entries for the comment; empty array if no history exists.
 */
function akismet_get_comment_history( $comment_id ) {
	return Akismet::get_comment_history( $comment_id );
}
/**
 * Compare two time values for ordering.
 *
 * @param mixed $a A time value or structure containing a time to compare.
 * @param mixed $b A time value or structure containing a time to compare.
 * @return int Negative if $a is earlier than $b, zero if they are equal, positive if $a is later than $b.
 */
function akismet_cmp_time( $a, $b ) {
	return Akismet::_cmp_time( $a, $b );
}
/**
 * Updates a comment's Akismet-related metadata after performing an automatic spam check.
 *
 * @param int $id The comment ID.
 * @param array|object $comment The comment data used for the auto-check.
 * @return mixed The result of the metadata update operation.
 */
function akismet_auto_check_update_meta( $id, $comment ) {
	return Akismet::auto_check_update_meta( $id, $comment );
}
/**
 * Checks a comment with Akismet to determine its spam status.
 *
 * @param array $commentdata Comment fields and metadata to be evaluated by Akismet.
 * @return mixed Result of the spam check: typically `true` if the comment is identified as spam, `false` if not, or an array with detailed check information. 
 */
function akismet_auto_check_comment( $commentdata ) {
	return Akismet::auto_check_comment( $commentdata );
}
/**
 * Retrieve the client's IP address for spam checking.
 *
 * @return string|null The client's IP address as a string, or `null` if no IP could be determined.
 */
function akismet_get_ip_address() {
	return Akismet::get_ip_address();
}
/**
 * Runs the scheduled Akismet recheck process for comments queued for re-evaluation.
 *
 * @return mixed The result of the recheck operation.
function akismet_cron_recheck() {
	return Akismet::cron_recheck();
}
/**
 * Adds a comment nonce field associated with the given post.
 *
 * @param int $post_id The ID of the post to associate the nonce with.
 */
function akismet_add_comment_nonce( $post_id ) {
	return Akismet::add_comment_nonce( $post_id );
}
/**
 * Attempts to repair scheduled recheck records for comments.
 *
 * @return mixed Result of the scheduled recheck repair operation.
function akismet_fix_scheduled_recheck() {
	return Akismet::fix_scheduled_recheck();
}
/**
 * Deprecated — kept for backward compatibility; no spam comments are provided.
 *
 * @deprecated 3.0
 * @return array An empty array.
 */
function akismet_spam_comments() {
	_deprecated_function( __FUNCTION__, '3.0' );

	return array();
}
/**
 * Backward-compatible placeholder for spam totals (deprecated).
 *
 * This function is deprecated since 3.0 and always returns an empty array.
 *
 * @deprecated 3.0
 * @return array An empty array representing spam totals.
 */
function akismet_spam_totals() {
	_deprecated_function( __FUNCTION__, '3.0' );

	return array();
}
/**
 * Deprecated wrapper for the Akismet plugin management page.
 *
 * This function is kept for backward compatibility and no longer performs any management actions.
 *
 * @deprecated 3.0
 */
function akismet_manage_page() {
	_deprecated_function( __FUNCTION__, '3.0' );
}
/**
 * Deprecated wrapper retained for backward compatibility.
 *
 * Calling this function triggers a deprecation notice (deprecated since 3.0).
 *
 * @deprecated 3.0
 */
function akismet_caught() {
	_deprecated_function( __FUNCTION__, '3.0' );
}
/**
 * Deprecated shim for legacy Akismet URL redirects.
 *
 * This function is deprecated as of 3.0 and no longer performs any action.
 *
 * @deprecated 3.0
 */
function redirect_old_akismet_urls() {
	_deprecated_function( __FUNCTION__, '3.0' );
}
/**
 * Deprecated shim that disables the Akismet proxy check.
 *
 * The provided $option parameter is ignored; the function always returns 0.
 *
 * @param mixed $option The option value passed to the proxy check (ignored).
 * @return int Always returns 0.
 * @deprecated 3.0
 */
function akismet_kill_proxy_check( $option ) {
	_deprecated_function( __FUNCTION__, '3.0' );

	return 0;
}
/**
 * No-op shim for pingback forwarded-for handling; functionality moved to WordPress core.
 *
 * This wrapper no longer performs any forwarding or header modification and always
 * returns false to indicate no forwarded-for value is provided.
 *
 * @param mixed $r Request/response context (unused).
 * @param string $url The pingback target URL (unused).
 * @return false Always `false`.
 */
function akismet_pingback_forwarded_for( $r, $url ) {
	// This functionality is now in core.
	return false;
}
/**
 * Performs a pre-check on the given pingback method to determine whether it should be processed.
 *
 * @param string $method The pingback method to check.
 * @return bool `true` if the pingback method passes the pre-check, `false` otherwise.
 */
function akismet_pre_check_pingback( $method ) {
	return Akismet::pre_check_pingback( $method );
}