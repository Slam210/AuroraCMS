<?php

class Akismet_REST_API {
	/**
	 * Register Akismet REST API routes under the akismet/v1 namespace.
	 *
	 * Registers endpoints for API key management, settings, stats, alerts, and webhooks
	 * with appropriate permission callbacks, argument definitions, and sanitizers.
	 *
	 * @return false|void False when the REST API registration function is unavailable; otherwise no return value.
	 */
	public static function init() {
		if ( ! function_exists( 'register_rest_route' ) ) {
			// The REST API wasn't integrated into core until 4.4, and we support 4.0+ (for now).
			return false;
		}

		register_rest_route(
			'akismet/v1',
			'/key',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'permission_callback' => array( 'Akismet_REST_API', 'privileged_permission_callback' ),
					'callback'            => array( 'Akismet_REST_API', 'get_key' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'permission_callback' => array( 'Akismet_REST_API', 'privileged_permission_callback' ),
					'callback'            => array( 'Akismet_REST_API', 'set_key' ),
					'args'                => array(
						'key' => array(
							'required'          => true,
							'type'              => 'string',
							'sanitize_callback' => array( 'Akismet_REST_API', 'sanitize_key' ),
							'description'       => __( 'A 12-character Akismet API key. Available at akismet.com/get/', 'akismet' ),
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'permission_callback' => array( 'Akismet_REST_API', 'privileged_permission_callback' ),
					'callback'            => array( 'Akismet_REST_API', 'delete_key' ),
				),
			)
		);

		register_rest_route(
			'akismet/v1',
			'/settings/',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'permission_callback' => array( 'Akismet_REST_API', 'privileged_permission_callback' ),
					'callback'            => array( 'Akismet_REST_API', 'get_settings' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'permission_callback' => array( 'Akismet_REST_API', 'privileged_permission_callback' ),
					'callback'            => array( 'Akismet_REST_API', 'set_boolean_settings' ),
					'args'                => array(
						'akismet_strictness' => array(
							'required'    => false,
							'type'        => 'boolean',
							'description' => __( 'If true, Akismet will automatically discard the worst spam automatically rather than putting it in the spam folder.', 'akismet' ),
						),
						'akismet_show_user_comments_approved' => array(
							'required'    => false,
							'type'        => 'boolean',
							'description' => __( 'If true, show the number of approved comments beside each comment author in the comments list page.', 'akismet' ),
						),
					),
				),
			)
		);

		register_rest_route(
			'akismet/v1',
			'/stats',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'permission_callback' => array( 'Akismet_REST_API', 'privileged_permission_callback' ),
				'callback'            => array( 'Akismet_REST_API', 'get_stats' ),
				'args'                => array(
					'interval' => array(
						'required'          => false,
						'type'              => 'string',
						'sanitize_callback' => array( 'Akismet_REST_API', 'sanitize_interval' ),
						'description'       => __( 'The time period for which to retrieve stats. Options: 60-days, 6-months, all', 'akismet' ),
						'default'           => 'all',
					),
				),
			)
		);

		register_rest_route(
			'akismet/v1',
			'/stats/(?P<interval>[\w+])',
			array(
				'args' => array(
					'interval' => array(
						'description' => __( 'The time period for which to retrieve stats. Options: 60-days, 6-months, all', 'akismet' ),
						'type'        => 'string',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'permission_callback' => array( 'Akismet_REST_API', 'privileged_permission_callback' ),
					'callback'            => array( 'Akismet_REST_API', 'get_stats' ),
				),
			)
		);

		register_rest_route(
			'akismet/v1',
			'/alert',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'permission_callback' => array( 'Akismet_REST_API', 'remote_call_permission_callback' ),
					'callback'            => array( 'Akismet_REST_API', 'get_alert' ),
					'args'                => array(
						'key' => array(
							'required'          => false,
							'type'              => 'string',
							'sanitize_callback' => array( 'Akismet_REST_API', 'sanitize_key' ),
							'description'       => __( 'A 12-character Akismet API key. Available at akismet.com/get/', 'akismet' ),
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'permission_callback' => array( 'Akismet_REST_API', 'remote_call_permission_callback' ),
					'callback'            => array( 'Akismet_REST_API', 'set_alert' ),
					'args'                => array(
						'key' => array(
							'required'          => false,
							'type'              => 'string',
							'sanitize_callback' => array( 'Akismet_REST_API', 'sanitize_key' ),
							'description'       => __( 'A 12-character Akismet API key. Available at akismet.com/get/', 'akismet' ),
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'permission_callback' => array( 'Akismet_REST_API', 'remote_call_permission_callback' ),
					'callback'            => array( 'Akismet_REST_API', 'delete_alert' ),
					'args'                => array(
						'key' => array(
							'required'          => false,
							'type'              => 'string',
							'sanitize_callback' => array( 'Akismet_REST_API', 'sanitize_key' ),
							'description'       => __( 'A 12-character Akismet API key. Available at akismet.com/get/', 'akismet' ),
						),
					),
				),
			)
		);

		register_rest_route(
			'akismet/v1',
			'/webhook',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( 'Akismet_REST_API', 'receive_webhook' ),
				'permission_callback' => array( 'Akismet_REST_API', 'remote_call_permission_callback' ),
			)
		);
	}

	/**
	 * Retrieve the current Akismet API key.
	 *
	 * @return WP_REST_Response The current API key string, or null if no key is configured.
	 */
	public static function get_key( $request = null ) {
		return rest_ensure_response( Akismet::get_api_key() );
	}

	/**
	 * Set the Akismet API key from the request when permitted.
	 *
	 * Attempts to update the stored API key using the request's `key` parameter.
	 * If the site has a hardcoded key (WPCOM_API_KEY), the request is rejected.
	 * If the provided key is not a valid registered key, the request is rejected.
	 *
	 * @param WP_REST_Request $request Request containing the `key` parameter (string) to store as the API key.
	 * @return WP_Error|WP_REST_Response WP_Error with status 409 when the key is hardcoded; WP_Error with status 400 when the provided key is invalid; otherwise a WP_REST_Response containing the current API key.
	 */
	public static function set_key( $request ) {
		if ( defined( 'WPCOM_API_KEY' ) ) {
			return rest_ensure_response( new WP_Error( 'hardcoded_key', __( 'This site\'s API key is hardcoded and cannot be changed via the API.', 'akismet' ), array( 'status' => 409 ) ) );
		}

		$new_api_key = $request->get_param( 'key' );

		if ( ! self::key_is_valid( $new_api_key ) ) {
			return rest_ensure_response( new WP_Error( 'invalid_key', __( 'The value provided is not a valid and registered API key.', 'akismet' ), array( 'status' => 400 ) ) );
		}

		update_option( 'wordpress_api_key', $new_api_key );

		return self::get_key();
	}

	/**
	 * Delete the stored Akismet API key unless the site uses a hardcoded key.
	 *
	 * @param WP_REST_Request $request The REST request (not used).
	 * @return WP_Error|WP_REST_Response WP_Error with code `hardcoded_key` and HTTP status 409 if the API key is hardcoded; WP_REST_Response containing `true` otherwise.
	 */
	public static function delete_key( $request ) {
		if ( defined( 'WPCOM_API_KEY' ) ) {
			return rest_ensure_response( new WP_Error( 'hardcoded_key', __( 'This site\'s API key is hardcoded and cannot be deleted.', 'akismet' ), array( 'status' => 409 ) ) );
		}

		delete_option( 'wordpress_api_key' );

		return rest_ensure_response( true );
	}

	/**
	 * Retrieve the current Akismet boolean settings.
	 *
	 * Returns an array with:
	 * - `akismet_strictness`: `true` if strict filtering is enabled, `false` otherwise.
	 * - `akismet_show_user_comments_approved`: `true` if approved user comments are shown, `false` otherwise.
	 *
	 * @return WP_REST_Response A REST response containing the settings array.
	 */
	public static function get_settings( $request = null ) {
		return rest_ensure_response(
			array(
				'akismet_strictness'                  => ( get_option( 'akismet_strictness', '1' ) === '1' ),
				'akismet_show_user_comments_approved' => ( get_option( 'akismet_show_user_comments_approved', '1' ) === '1' ),
			)
		);
	}

	/**
	 * Update Akismet boolean settings from request parameters.
	 *
	 * Reads `akismet_strictness` and `akismet_show_user_comments_approved` from the request and updates the stored options when provided.
	 *
	 * @param WP_REST_Request $request Request containing optional boolean parameters for the settings.
	 * @return WP_REST_Response The current Akismet settings as returned by `get_settings()`.
	 */
	public static function set_boolean_settings( $request ) {
		foreach ( array(
			'akismet_strictness',
			'akismet_show_user_comments_approved',
		) as $setting_key ) {

			$setting_value = $request->get_param( $setting_key );
			if ( is_null( $setting_value ) ) {
				// This setting was not specified.
				continue;
			}

			// From 4.7+, WP core will ensure that these are always boolean
			// values because they are registered with 'type' => 'boolean',
			// but we need to do this ourselves for prior versions.
			$setting_value = self::parse_boolean( $setting_value );

			update_option( $setting_key, $setting_value ? '1' : '0' );
		}

		return self::get_settings();
	}

	/**
	 * Convert various boolean-like values to a boolean.
	 *
	 * @param mixed $value Value to convert (bool, numeric, or string representations).
	 * @return bool `true` if the value represents truth, `false` otherwise.
	 */
	public static function parse_boolean( $value ) {
		switch ( $value ) {
			case true:
			case 'true':
			case '1':
			case 1:
				return true;

			case false:
			case 'false':
			case '0':
			case 0:
				return false;

			default:
				return (bool) $value;
		}
	}

	/**
	 * Retrieve Akismet statistics for a specified interval.
	 *
	 * Possible `interval` values: `all`, `60-days`, `6-months`.
	 *
	 * @param WP_REST_Request $request The REST request; may include the `interval` parameter.
	 * @return WP_Error|WP_REST_Response A WP_Error on failure, or a WP_REST_Response containing an associative array where the requested interval string maps to the decoded stats object.
	 */
	public static function get_stats( $request ) {
		$api_key = Akismet::get_api_key();

		$interval = $request->get_param( 'interval' );

		$stat_totals = array();

		$request_args = array(
			'blog' => get_option( 'home' ),
			'key'  => $api_key,
			'from' => $interval,
		);

		$request_args = apply_filters( 'akismet_request_args', $request_args, 'get-stats' );

		$response = Akismet::http_post( Akismet::build_query( $request_args ), 'get-stats' );

		if ( ! empty( $response[1] ) ) {
			$stat_totals[ $interval ] = json_decode( $response[1] );
		}

		return rest_ensure_response( $stat_totals );
	}

	/**
	 * Retrieve the current Akismet alert code and message.
	 *
	 * @param WP_REST_Request|null $request Optional REST request (unused).
	 * @return WP_REST_Response Array with 'code' (alert code) and 'message' (alert message).
	 */
	public static function get_alert( $request ) {
		return rest_ensure_response(
			array(
				'code'    => get_option( 'akismet_alert_code' ),
				'message' => get_option( 'akismet_alert_msg' ),
			)
		);
	}

	/**
	 * Refreshes the stored alert code and message by requesting the latest alert from the Akismet server.
	 *
	 * Deletes any locally stored alert state, triggers a server verification to repopulate alert values, and returns the current alert.
	 *
	 * @param WP_REST_Request $request Request object (passed to get_alert when forming the response).
	 * @return WP_Error|WP_REST_Response `WP_Error` on failure, or a `WP_REST_Response` containing the current alert with keys `code` and `message`.
	 */
	public static function set_alert( $request ) {
		delete_option( 'akismet_alert_code' );
		delete_option( 'akismet_alert_msg' );

		// Make a request so the most recent alert code and message are retrieved.
		Akismet::verify_key( Akismet::get_api_key() );

		return self::get_alert( $request );
	}

	/**
	 * Clear stored Akismet alert code and message and return the current alert state.
	 *
	 * @param WP_REST_Request $request The request object (not used).
	 * @return WP_Error|WP_REST_Response The current alert data with keys `code` and `message`.
	 */
	public static function delete_alert( $request ) {
		delete_option( 'akismet_alert_code' );
		delete_option( 'akismet_alert_msg' );

		return self::get_alert( $request );
	}

	/**
	 * Check whether the given Akismet API key is valid.
	 *
	 * @param string $key The API key to validate.
	 * @return bool `true` if the key is valid, `false` otherwise.
	 */
	private static function key_is_valid( $key ) {
		$request_args = array(
			'key'  => $key,
			'blog' => get_option( 'home' ),
		);

		$request_args = apply_filters( 'akismet_request_args', $request_args, 'verify-key' );

		$response = Akismet::http_post( Akismet::build_query( $request_args ), 'verify-key' );

		if ( $response[1] == 'valid' ) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the current user has privileged permissions for Akismet operations.
	 *
	 * @return bool `true` if the current user has the 'manage_options' capability, `false` otherwise.
	 */
	public static function privileged_permission_callback() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Authorizes remote calls from Akismet by validating the request's API key.
	 *
	 * Compares the incoming request's `key` parameter against the locally configured Akismet API key (case-insensitive).
	 *
	 * @param WP_REST_Request $request The incoming REST request.
	 * @return bool `true` if the request key matches the configured API key, `false` otherwise.
	 */
	public static function remote_call_permission_callback( $request ) {
		$local_key = Akismet::get_api_key();

		return $local_key && ( strtolower( $request->get_param( 'key' ) ) === strtolower( $local_key ) );
	}

	/**
	 * Sanitize and normalize a stats interval parameter.
	 *
	 * Trims the provided value and ensures it is one of '60-days', '6-months', or 'all'.
	 * Returns 'all' when the input is not one of the allowed values.
	 *
	 * @param string           $interval Interval value to sanitize.
	 * @param WP_REST_Request  $request  Request object (unused).
	 * @param string           $param    Parameter name (unused).
	 * @return string The sanitized interval: '60-days', '6-months', or 'all'.
	 */
	public static function sanitize_interval( $interval, $request, $param ) {
		$interval = trim( $interval );

		$valid_intervals = array( '60-days', '6-months', 'all' );

		if ( ! in_array( $interval, $valid_intervals ) ) {
			$interval = 'all';
		}

		return $interval;
	}

	/**
	 * Trim leading and trailing whitespace from an API key value.
	 *
	 * @param string $key The API key value to sanitize.
	 * @return string The trimmed API key.
	 */
	public static function sanitize_key( $key, $request, $param ) {
		return trim( $key );
	}

	/**
	 * Handle webhook requests sent by Akismet servers and update comment moderation state accordingly.
	 *
	 * Processes webhook payloads for endpoints such as `comment-check`, `submit-ham`, and `submit-spam`,
	 * applies any required comment status changes, records per-comment results, and triggers the
	 * akismet_webhook_received action after processing.
	 *
	 * @param WP_REST_Request $request The incoming REST request containing `endpoint` and optional `comments`.
	 * @return WP_Error|WP_REST_Response A WP_Error for malformed requests (for example, when `comments` is not an array),
	 *                                  otherwise a WP_REST_Response with an array containing per-comment statuses under the
	 *                                  `comments` key (each entry keyed by comment GUID with `status` and optional `message`).
	 */
	public static function receive_webhook( $request ) {
		Akismet::log( array( 'Webhook request received', $request->get_body() ) );

		/**
		 * The request body should look like this:
		 * array(
		 *     'key' => '1234567890abcd',
		 *     'endpoint' => '[comment-check|submit-ham|submit-spam]',
		 *     'comments' => array(
		 *         array(
		 *             'guid' => '[...]',
		 *             'result' => '[true|false]',
		 *             'comment_author' => '[...]',
		 *             [...]
		 *         ),
		 *         array(
		 *             'guid' => '[...]',
		 *             [...],
		 *         ),
		 *         [...]
		 *     )
		 * )
		 *
		 * Multiple comments can be included in each request, and the only truly required
		 * field for each is the guid, although it would be friendly to include also
		 * comment_post_ID, comment_parent, and comment_author_email, if possible to make
		 * searching easier.
		 */

		// The response will include statuses for the result of each comment that was supplied.
		$response = array(
			'comments' => array(),
		);

		$endpoint = $request->get_param( 'endpoint' );

		switch ( $endpoint ) {
			case 'comment-check':
				$webhook_comments = $request->get_param( 'comments' );

				if ( ! is_array( $webhook_comments ) ) {
					return rest_ensure_response( new WP_Error( 'malformed_request', __( 'The \'comments\' parameter must be an array.', 'akismet' ), array( 'status' => 400 ) ) );
				}

				foreach ( $webhook_comments as $webhook_comment ) {
					$guid = $webhook_comment['guid'];

					if ( ! $guid ) {
						// Without the GUID, we can't be sure that we're matching the right comment.
						// We'll make it a rule that any comment without a GUID is ignored intentionally.
						continue;
					}

					// Search on the fields that are indexed in the comments table, plus the GUID.
					// The GUID is the only thing we really need to search on, but comment_meta
					// is not indexed in a useful way if there are many many comments. This
					// should help narrow it down first.
					$queryable_fields = array(
						'comment_post_ID'      => 'post_id',
						'comment_parent'       => 'parent',
						'comment_author_email' => 'author_email',
					);

					$query_args               = array();
					$query_args['status']     = 'any';
					$query_args['meta_key']   = 'akismet_guid';
					$query_args['meta_value'] = $guid;

					foreach ( $queryable_fields as $queryable_field => $wp_comment_query_field ) {
						if ( isset( $webhook_comment[ $queryable_field ] ) ) {
							$query_args[ $wp_comment_query_field ] = $webhook_comment[ $queryable_field ];
						}
					}

					$comments_query = new WP_Comment_Query( $query_args );
					$comments       = $comments_query->comments;

					if ( ! $comments ) {
						// Unexpected, although the comment could have been deleted since being submitted.
						Akismet::log( 'Webhook failed: no matching comment found.' );

						$response['comments'][ $guid ] = array(
							'status'  => 'error',
							'message' => __( 'Could not find matching comment.', 'akismet' ),
						);

						continue;
					} if ( count( $comments ) > 1 ) {
						// Two comments shouldn't be able to match the same GUID.
						Akismet::log( 'Webhook failed: multiple matching comments found.', $comments );

						$response['comments'][ $guid ] = array(
							'status'  => 'error',
							'message' => __( 'Multiple comments matched request.', 'akismet' ),
						);

						continue;
					} else {
						// We have one single match, as hoped for.
						Akismet::log( 'Found matching comment.', $comments );

						$comment = $comments[0];

						$current_status = wp_get_comment_status( $comment );

						$result = $webhook_comment['result'];

						if ( 'true' == $result ) {
							Akismet::log( 'Comment should be spam' );

							// The comment should be classified as spam.
							if ( 'spam' != $current_status ) {
								// The comment is not classified as spam. If Akismet was the one to act on it, move it to spam.
								if ( Akismet::last_comment_status_change_came_from_akismet( $comment->comment_ID ) ) {
									Akismet::log( 'Comment is not spam; marking as spam.' );

									wp_spam_comment( $comment );
									Akismet::update_comment_history( $comment->comment_ID, '', 'webhook-spam' );
								} else {
									Akismet::log( 'Comment is not spam, but it has already been manually handled by some other process.' );
									Akismet::update_comment_history( $comment->comment_ID, '', 'webhook-spam-noaction' );
								}
							}
						} elseif ( 'false' == $result ) {
							Akismet::log( 'Comment should be ham' );

							// The comment should be classified as ham.
							if ( 'spam' == $current_status ) {
								Akismet::log( 'Comment is spam.' );

								// The comment is classified as spam. If Akismet was the one to label it as spam, unspam it.
								if ( Akismet::last_comment_status_change_came_from_akismet( $comment->comment_ID ) ) {
									Akismet::log( 'Akismet marked it as spam; unspamming.' );

									wp_unspam_comment( $comment );

									akismet::update_comment_history( $comment->comment_ID, '', 'webhook-ham' );
								} else {
									Akismet::log( 'Comment is not spam, but it has already been manually handled by some other process.' );
									Akismet::update_comment_history( $comment->comment_ID, '', 'webhook-ham-noaction' );
								}
							} else if ( 'unapproved' == $current_status ) {
								Akismet::log( 'Comment is pending.' );

								// The comment is in Pending. If Akismet was the one to put it there, approve it (but only if the site
								// settings dictate that).
								if ( Akismet::last_comment_status_change_came_from_akismet( $comment->comment_ID ) ) {
									Akismet::log( 'Akismet marked it as Pending; approving.' );

									if ( check_comment( $comment->comment_author, $comment->comment_author_email, $comment->comment_author_url, $comment->comment_content, $comment->comment_author_IP, $comment->comment_agent, $comment->comment_type ) ) {
										wp_set_comment_status( $comment->comment_ID, 1 );
									}

									akismet::update_comment_history( $comment->comment_ID, '', 'webhook-ham' );
								} else {
									Akismet::log( 'Comment is not spam, but it has already been manually handled by some other process.' );
									Akismet::update_comment_history( $comment->comment_ID, '', 'webhook-ham-noaction' );
								}
							}

							$moderation_email_was_delayed = get_comment_meta( $comment->comment_ID, 'akismet_delayed_moderation_email', true );

							if ( $moderation_email_was_delayed ) {
								Akismet::log( 'Moderation email was delayed for comment #' . $comment->comment_ID . '; sending now.' );

								delete_comment_meta( $comment->comment_ID, 'akismet_delayed_moderation_email' );
								wp_new_comment_notify_moderator( $comment->comment_ID );
								wp_new_comment_notify_postauthor( $comment->comment_ID );
							}

							delete_comment_meta( $comment->comment_ID, 'akismet_delay_moderation_email' );
						}

						$response['comments'][ $guid ] = array( 'status' => 'success' );
					}
				}

				break;
			case 'submit-ham':
			case 'submit-spam':
				// Nothing to do for submit-ham or submit-spam.
				break;
			default:
				// Unsupported endpoint.
				break;
		}

		/**
		 * Allow plugins to do things with a successfully processed webhook request, like logging.
		 *
		 * @since 5.3.2
		 *
		 * @param WP_REST_Request $request The REST request object.
		 */
		do_action( 'akismet_webhook_received', $request );

		Akismet::log( 'Done processing webhook.' );

		return rest_ensure_response( $response );
	}
}