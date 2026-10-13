<?php
/**
 * HTTP client for the FastAPI ledger microservice.
 *
 * @package Unmer_Ledger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Thin wrapper around wp_remote_get / wp_remote_post.
 */
class Unmer_Ledger_API {

	/**
	 * Base URL of the ledger microservice (localhost only).
	 *
	 * @var string
	 */
	const BASE_URL = 'http://127.0.0.1:8000';

	/**
	 * Request timeout in seconds.
	 *
	 * @var int
	 */
	const TIMEOUT = 10;

	/**
	 * HTTP status code of the most recent request.
	 *
	 * @var int|null
	 */
	public $last_status = null;

	/**
	 * Fetch a single ledger record by reference id.
	 *
	 * @param string $ref_id Reference id.
	 * @return array|WP_Error Decoded record on success, WP_Error otherwise.
	 */
	public function get_dokumen( $ref_id ) {
		$url = self::BASE_URL . '/api/dokumen/' . rawurlencode( $ref_id );

		$response = wp_remote_get(
			$url,
			array(
				'timeout' => self::TIMEOUT,
				'headers' => array( 'Accept' => 'application/json' ),
			)
		);

		return $this->parse_response( $response );
	}

	/**
	 * Create a new ledger block.
	 *
	 * @param array $payload Document metadata.
	 * @return array|WP_Error Decoded response on success, WP_Error otherwise.
	 */
	public function post_dokumen( $payload ) {
		$url = self::BASE_URL . '/api/dokumen';

		$response = wp_remote_post(
			$url,
			array(
				'timeout' => self::TIMEOUT,
				'headers' => array(
					'Content-Type' => 'application/json',
					'Accept'       => 'application/json',
				),
				'body'    => wp_json_encode( $payload ),
			)
		);

		return $this->parse_response( $response );
	}

	/**
	 * Fetch all active ledger records.
	 *
	 * @return array|WP_Error Array of records on success, WP_Error otherwise.
	 */
	public function get_list_dokumen() {
		$url = self::BASE_URL . '/api/dokumen';

		$response = wp_remote_get(
			$url,
			array(
				'timeout' => self::TIMEOUT,
				'headers' => array( 'Accept' => 'application/json' ),
			)
		);

		return $this->parse_response( $response );
	}

	/**
	 * Soft-delete a ledger record by reference id.
	 *
	 * @param string $ref_id Reference id.
	 * @return array|WP_Error Decoded response on success, WP_Error otherwise.
	 */
	public function delete_dokumen( $ref_id ) {
		$url = self::BASE_URL . '/api/dokumen/' . rawurlencode( $ref_id );

		$response = wp_remote_request(
			$url,
			array(
				'method'  => 'DELETE',
				'timeout' => self::TIMEOUT,
				'headers' => array( 'Accept' => 'application/json' ),
			)
		);

		return $this->parse_response( $response );
	}

	/**
	 * Validate and decode a wp_remote response.
	 *
	 * @param array|WP_Error $response Response from wp_remote_*.
	 * @return array|WP_Error
	 */
	private function parse_response( $response ) {
		if ( is_wp_error( $response ) ) {
			$this->last_status = 0;
			return new WP_Error(
				'unmer_ledger_unreachable',
				__( 'Layanan ledger tidak dapat dihubungi. Pastikan microservice FastAPI berjalan.', 'unmer-ledger' )
			);
		}

		$this->last_status = (int) wp_remote_retrieve_response_code( $response );
		$body              = wp_remote_retrieve_body( $response );
		$decoded           = json_decode( $body, true );

		if ( 404 === $this->last_status ) {
			return new WP_Error(
				'unmer_ledger_not_found',
				__( 'Dokumen tidak ditemukan pada ledger.', 'unmer-ledger' )
			);
		}

		if ( $this->last_status < 200 || $this->last_status >= 300 ) {
			$detail = __( 'Terjadi kesalahan pada layanan ledger.', 'unmer-ledger' );
			if ( is_array( $decoded ) && isset( $decoded['detail'] ) ) {
				$detail = $this->flatten_detail( $decoded['detail'], $detail );
			}
			return new WP_Error( 'unmer_ledger_error', $detail );
		}

		if ( ! is_array( $decoded ) ) {
			return new WP_Error(
				'unmer_ledger_invalid_response',
				__( 'Respons layanan ledger tidak valid.', 'unmer-ledger' )
			);
		}

		return $decoded;
	}

	/**
	 * Normalize an API error detail into a displayable string.
	 *
	 * FastAPI returns a plain string for HTTPException (e.g. 400/404) but a
	 * list of error objects for request validation failures (e.g. 422).
	 *
	 * @param mixed  $detail   Raw `detail` value from the decoded body.
	 * @param string $fallback Fallback message when nothing usable is found.
	 * @return string
	 */
	private function flatten_detail( $detail, $fallback ) {
		if ( is_string( $detail ) && '' !== $detail ) {
			return $detail;
		}
		if ( is_array( $detail ) ) {
			$messages = array();
			foreach ( $detail as $item ) {
				if ( is_string( $item ) && '' !== $item ) {
					$messages[] = $item;
				} elseif ( is_array( $item ) && isset( $item['msg'] ) && is_string( $item['msg'] ) ) {
					$field      = isset( $item['loc'] ) && is_array( $item['loc'] )
						? implode( '.', array_map( 'strval', $item['loc'] ) )
						: '';
					$messages[] = '' !== $field ? $field . ': ' . $item['msg'] : $item['msg'];
				}
			}
			if ( ! empty( $messages ) ) {
				return implode( ' ', array_slice( $messages, 0, 3 ) );
			}
		}
		return $fallback;
	}
}
