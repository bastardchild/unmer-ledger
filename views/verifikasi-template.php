<?php
/**
 * Public verification card / institutional not-found page.
 *
 * Receives $GLOBALS['unmer_ledger_record'] when a document is found.
 * When absent, renders the "not found / invalid" state.
 *
 * @package Unmer_Ledger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$unmer_record = isset( $GLOBALS['unmer_ledger_record'] ) && is_array( $GLOBALS['unmer_ledger_record'] )
	? $GLOBALS['unmer_ledger_record']
	: null;

$unmer_is_valid = ( null !== $unmer_record );

$unmer_doc_number = $unmer_is_valid ? $unmer_record['nomor_dokumen'] : '';
$unmer_title      = $unmer_is_valid
	? 'Dokumen - Universitas Merdeka Malang'
	: 'Verifikasi Dokumen - Universitas Merdeka Malang';
$unmer_logo       = 'https://unmer.ac.id/wp-content/uploads/2024/10/cropped-unmer-favicon-192x192.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<title><?php echo esc_html( $unmer_title ); ?></title>
<style>
	*{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',Arial,Helvetica,sans-serif;}

	body{
		min-height:100vh;
		display:flex;
		align-items:center;
		justify-content:center;
		padding:20px;
	}

	.background{
		position:fixed;
		top:0;
		left:0;
		width:100%;
		height:100%;
		background-image:url("https://unmer.ac.id/wp-content/uploads/2025/08/gedung-unmer22.webp");
		background-size:cover;
		background-position:center;
		z-index:-1;
	}

	.card{
		width:100%;
		max-width:500px;
		background:#fff;
		padding:35px 40px;
		border-radius:10px;
		box-shadow:0 8px 25px rgba(0,0,0,0.25);
		text-align:center;
	}

	.logo{
		width:64px;
		margin-bottom:12px;
	}

	.card h2{
		font-size:18px;
		font-weight:500;
		margin-bottom:12px;
		color:#333;
	}

	.badge{
		display:inline-block;
		margin-bottom:20px;
		padding:8px 16px;
		border-radius:999px;
		font-weight:700;
		font-size:12.5px;
		letter-spacing:.02em;
	}

	.badge.ok{background:#e7f6ec;color:#1f7a3d;border:1px solid #bfe6cc;}
	.badge.bad{background:#fdecea;color:#b3261e;border:1px solid #f5c6c2;}

	.badge .dot{
		display:inline-block;
		width:9px;
		height:9px;
		border-radius:50%;
		background:#22c55e;
		margin-right:7px;
		vertical-align:middle;
		position:relative;
		animation:pulse 1.8s ease-out infinite;
	}

	@keyframes pulse{
		0%{box-shadow:0 0 0 0 rgba(34,197,94,.6);}
		70%{box-shadow:0 0 0 8px rgba(34,197,94,0);}
		100%{box-shadow:0 0 0 0 rgba(34,197,94,0);}
	}

	@media (prefers-reduced-motion: reduce){
		.badge .dot{animation:none;}
	}

	.info{
		width:100%;
		font-size:14px;
		text-align:left;
		border-collapse:collapse;
	}

	.info td{
		padding:6px 0;
		vertical-align:top;
	}

	.info td:first-child{
		width:150px;
		font-weight:600;
		color:#333;
	}

	/* Audit accordion */
	details{
		margin-top:20px;
		border-top:1px solid #e2e6ea;
		padding-top:14px;
		text-align:left;
	}

	summary{
		cursor:pointer;
		font-weight:600;
		font-size:12.5px;
		color:#5b6672;
	}

	.tech{margin-top:10px;font-size:12.5px;}

	.tech .k{color:#5b6672;text-transform:uppercase;font-size:11px;letter-spacing:.05em;}

	.tech .v{
		font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;
		word-break:break-all;
		background:#f7f9fb;
		border:1px solid #e2e6ea;
		border-radius:6px;
		padding:6px 8px;
		margin:3px 0 10px;
	}

	.notfound .icon{font-size:2.6rem;margin-bottom:8px;}
	.notfound p{color:#5b6672;font-size:13.5px;margin:6px 0;}

	.foot{text-align:center;color:#8a939c;font-size:11.5px;margin-top:20px;}

	/* Tablet */
	@media (max-width:768px){
		.card{padding:30px;}
		.card h2{font-size:17px;}
		.logo{width:50px;}
	}

	/* Mobile */
	@media (max-width:480px){
		.card{padding:25px 20px;}
		.card h2{font-size:16px;}
		.logo{width:45px;}
		.info{font-size:13px;}
		.info td:first-child{width:120px;}
	}
</style>
</head>

<body>

<div class="background"></div>

<div class="card">

	<img src="<?php echo esc_url( $unmer_logo ); ?>" class="logo" alt="UNMER">

	<h2><?php echo esc_html( $unmer_title ); ?></h2>

	<?php if ( $unmer_is_valid ) : ?>
		<span class="badge ok"><span class="dot"></span>DOKUMEN RESMI TERVERIFIKASI</span>

		<table class="info">
			<tr>
				<td>No. Surat</td>
				<td>: <?php echo esc_html( $unmer_record['nomor_dokumen'] ); ?></td>
			</tr>
			<tr>
				<td>Tanggal Surat</td>
				<td>: <?php echo esc_html( unmer_ledger_format_tanggal( $unmer_record['tanggal_surat'] ) ); ?></td>
			</tr>
			<tr>
				<td>Penandatangan</td>
				<td>: <?php echo esc_html( $unmer_record['penandatangan'] ); ?></td>
			</tr>
			<tr>
				<td>Perihal</td>
				<td>: <?php echo esc_html( $unmer_record['judul_dokumen'] ); ?></td>
			</tr>
		</table>

		<details>
			<summary>Detail Teknis &amp; Audit Blockchain</summary>
			<div class="tech">
				<div class="k">Waktu Penerbitan</div>
				<div class="v"><?php echo esc_html( unmer_ledger_format_wib( $unmer_record['created_at'] ) ); ?></div>
				<div class="k">Block Index</div>
				<div class="v"><?php echo esc_html( (string) $unmer_record['block_index'] ); ?></div>
				<div class="k">Block Hash (SHA-256)</div>
				<div class="v"><?php echo esc_html( $unmer_record['block_hash'] ); ?></div>
				<div class="k">Previous Block Hash</div>
				<div class="v"><?php echo esc_html( $unmer_record['previous_hash'] ); ?></div>
				<div class="k">ID Referensi</div>
				<div class="v"><?php echo esc_html( $unmer_record['reference_id'] ); ?></div>
			</div>
		</details>
	<?php else : ?>
		<span class="badge bad">&#10060; DOKUMEN TIDAK DITEMUKAN ATAU TIDAK SAH</span>

		<div class="notfound">
			<div class="icon">&#128683;</div>
			<h3>Dokumen Tidak Ditemukan atau Tidak Sah</h3>
			<p>ID referensi pada QR code tidak terdaftar pada ledger resmi UNMER, atau dokumen telah dimodifikasi.</p>
			<p>Silakan hubungi Bagian Administrasi Akademik untuk konfirmasi lebih lanjut.</p>
		</div>
	<?php endif; ?>

	<div class="foot">
		&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> Universitas Merdeka Malang.<br>Halaman ini dihasilkan otomatis oleh Sistem Ledger Dokumen.
	</div>

</div>

</body>
</html>
