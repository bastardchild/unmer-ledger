<?php
/**
 * Plugin Name:       UNMER Digital Ledger Dokumen
 * Plugin URI:        https://unmer.ac.id/
 * Description:       Pencatatan dan verifikasi dokumen resmi berbasis hash-chain. Menghubungkan WordPress dengan microservice FastAPI pada localhost.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Universitas Merdeka (UNMER)
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       unmer-ledger
 *
 * @package Unmer_Ledger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'UNMER_LEDGER_VERSION', '1.0.0' );
define( 'UNMER_LEDGER_FILE', __FILE__ );
define( 'UNMER_LEDGER_DIR', plugin_dir_path( __FILE__ ) );
define( 'UNMER_LEDGER_URL', plugin_dir_url( __FILE__ ) );

require_once UNMER_LEDGER_DIR . 'includes/class-ledger-api.php';

/**
 * Register the verification rewrite rule.
 *
 * @return void
 */
function unmer_ledger_register_rewrite() {
	add_rewrite_rule(
		'^dokumen/tampilkan/([^/]+)/?$',
		'index.php?unmer_ref_id=$matches[1]',
		'top'
	);
}
add_action( 'init', 'unmer_ledger_register_rewrite' );

/**
 * Register the custom query var used by the verification route.
 *
 * @param array $vars Existing query vars.
 * @return array
 */
function unmer_ledger_query_vars( $vars ) {
	$vars[] = 'unmer_ref_id';
	return $vars;
}
add_filter( 'query_vars', 'unmer_ledger_query_vars' );

/**
 * Flush rewrite rules on activation.
 *
 * @return void
 */
function unmer_ledger_activate() {
	unmer_ledger_register_rewrite();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'unmer_ledger_activate' );

/**
 * Flush rewrite rules on deactivation.
 *
 * @return void
 */
function unmer_ledger_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'unmer_ledger_deactivate' );

/**
 * Render the institutional "not found" page for unverifiable documents.
 *
 * @return void
 */
function unmer_ledger_render_not_found() {
	status_header( 404 );
	nocache_headers();
	include UNMER_LEDGER_DIR . 'views/verifikasi-template.php';
	exit;
}

/**
 * Intercept verification requests and render the appropriate page.
 *
 * @return void
 */
function unmer_ledger_template_redirect() {
	$ref_id = get_query_var( 'unmer_ref_id' );
	if ( empty( $ref_id ) ) {
		return;
	}

	$ref_id = sanitize_text_field( wp_unslash( $ref_id ) );
	$api    = new Unmer_Ledger_API();
	$record = $api->get_dokumen( $ref_id );

	if ( is_wp_error( $record ) || 404 === (int) $api->last_status ) {
		unmer_ledger_render_not_found();
	}

	$GLOBALS['unmer_ledger_record'] = $record;
	include UNMER_LEDGER_DIR . 'views/verifikasi-template.php';
	exit;
}
add_action( 'template_redirect', 'unmer_ledger_template_redirect' );

/**
 * Register the admin menu entry for document registration.
 *
 * @return void
 */
function unmer_ledger_admin_menu() {
	add_menu_page(
		__( 'Pencatatan Dokumen', 'unmer-ledger' ),
		__( 'Pencatatan Dokumen', 'unmer-ledger' ),
		'manage_options',
		'unmer-ledger',
		'unmer_ledger_render_admin_page',
		'dashicons-media-document',
		26
	);
}
add_action( 'admin_menu', 'unmer_ledger_admin_menu' );

/**
 * Render the admin registration page.
 *
 * @return void
 */
function unmer_ledger_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Anda tidak memiliki izin untuk mengakses halaman ini.', 'unmer-ledger' ) );
	}

	$result = null;
	$error  = null;

	if ( isset( $_POST['unmer_ledger_submit'] ) ) {
		check_admin_referer( 'unmer_ledger_create', 'unmer_ledger_nonce' );

		$payload = array(
			'reference_id'   => isset( $_POST['reference_id'] ) ? sanitize_text_field( wp_unslash( $_POST['reference_id'] ) ) : '',
			'jenis_dokumen'  => isset( $_POST['jenis_dokumen'] ) ? sanitize_text_field( wp_unslash( $_POST['jenis_dokumen'] ) ) : '',
			'nomor_dokumen'  => isset( $_POST['nomor_dokumen'] ) ? sanitize_text_field( wp_unslash( $_POST['nomor_dokumen'] ) ) : '',
			'judul_dokumen'  => isset( $_POST['judul_dokumen'] ) ? sanitize_text_field( wp_unslash( $_POST['judul_dokumen'] ) ) : '',
			'tanggal_surat'  => isset( $_POST['tanggal_surat'] ) ? sanitize_text_field( wp_unslash( $_POST['tanggal_surat'] ) ) : '',
			'penandatangan'  => isset( $_POST['penandatangan'] ) ? sanitize_text_field( wp_unslash( $_POST['penandatangan'] ) ) : '',
		);

		$api       = new Unmer_Ledger_API();
		$response  = $api->post_dokumen( $payload );

		if ( is_wp_error( $response ) ) {
			$error = $response->get_error_message();
		} elseif ( ! empty( $response['status'] ) && 'success' === $response['status'] ) {
			$result = $response;
		} else {
			$error = __( 'Gagal mencatat dokumen. Periksa kembali data atau ID Referensi (mungkin sudah terdaftar).', 'unmer-ledger' );
		}
	}

	$view = UNMER_LEDGER_DIR . 'views/admin-form.php';
	if ( file_exists( $view ) ) {
		include $view;
	}
}

/**
 * Generate a default reference id: YYYYMMDD + 3 random digits.
 *
 * @return string
 */
function unmer_ledger_default_reference_id() {
	return wp_date( 'Ymd' ) . wp_rand( 100, 999 );
}

/**
 * Indonesian month names indexed 1-12.
 *
 * @return array
 */
function unmer_ledger_bulan() {
	return array(
		1  => 'Januari',
		2  => 'Februari',
		3  => 'Maret',
		4  => 'April',
		5  => 'Mei',
		6  => 'Juni',
		7  => 'Juli',
		8  => 'Agustus',
		9  => 'September',
		10 => 'Oktober',
		11 => 'November',
		12 => 'Desember',
	);
}

/**
 * Convert a UTC ISO-8601 timestamp to WIB (UTC+7) for display.
 *
 * @param string $iso UTC timestamp.
 * @return string
 */
function unmer_ledger_format_wib( $iso ) {
	$timestamp = strtotime( $iso );
	if ( false === $timestamp ) {
		return $iso;
	}
	$wib   = $timestamp + ( 7 * HOUR_IN_SECONDS );
	$bulan = unmer_ledger_bulan();
	return gmdate( 'd', $wib ) . ' ' . $bulan[ (int) gmdate( 'n', $wib ) ] . ' ' . gmdate( 'Y, H:i:s', $wib ) . ' WIB';
}

/**
 * Format a document date (YYYY-MM-DD) for display.
 *
 * @param string $iso_date Document date.
 * @return string
 */
function unmer_ledger_format_tanggal( $iso_date ) {
	$timestamp = strtotime( $iso_date );
	if ( false === $timestamp ) {
		return $iso_date;
	}
	$bulan = unmer_ledger_bulan();
	return gmdate( 'd', $timestamp ) . ' ' . $bulan[ (int) gmdate( 'n', $timestamp ) ] . ' ' . gmdate( 'Y', $timestamp );
}
