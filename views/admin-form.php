<?php
/**
 * Admin registration form view.
 *
 * Expected variables (set by unmer-ledger.php):
 *   $result array|null  Successful API response.
 *   $error  string|null Error message.
 *
 * @package Unmer_Ledger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$default_ref = unmer_ledger_default_reference_id();
$jenis_opts  = array(
	'Surat Keterangan'        => 'Surat Keterangan',
	'Surat Keputusan (SK)'    => 'Surat Keputusan (SK)',
	'Sertifikat / Piagam'     => 'Sertifikat / Piagam',
	'Surat Tugas'             => 'Surat Tugas',
	'Lainnya'                 => 'Lainnya',
);
?>
<div class="wrap">
	<h1><?php echo esc_html__( 'Pencatatan Dokumen', 'unmer-ledger' ); ?></h1>
	<p class="description">
		<?php echo esc_html__( 'Catat metadata dokumen ke dalam ledger hash-chain. Cetak QR code pada dokumen fisik.', 'unmer-ledger' ); ?>
	</p>

	<?php if ( ! empty( $error ) ) : ?>
		<div class="notice notice-error is-dismissible">
			<p><?php echo esc_html( $error ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $result ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p>
				<?php
				echo esc_html(
					sprintf(
						/* translators: %d: block index */
						__( 'Dokumen berhasil dicatat pada block #%d.', 'unmer-ledger' ),
						(int) $result['block_index']
					)
				);
				?>
			</p>
		</div>

		<div class="card" style="max-width:680px;padding:16px 20px;">
			<h2><?php echo esc_html__( 'QR Code Dokumen', 'unmer-ledger' ); ?></h2>
			<p>
				<img
					id="unmer-qr-image"
					src="<?php echo esc_attr( $result['qr_base64'] ); ?>"
					alt="<?php echo esc_attr__( 'QR Code verifikasi dokumen', 'unmer-ledger' ); ?>"
					style="max-width:220px;height:auto;border:1px solid #dcdcde;padding:8px;background:#fff;"
				/>
			</p>
			<p>
				<button
					type="button"
					class="button button-primary"
					id="unmer-qr-download"
					data-filename="qr-<?php echo esc_attr( isset( $result['reference_id'] ) ? $result['reference_id'] : $result['block_hash'] ); ?>.png"
				>
					<?php echo esc_html__( 'Download QR Code (PNG)', 'unmer-ledger' ); ?>
				</button>
			</p>
			<p>
				<strong><?php echo esc_html__( 'Target URL:', 'unmer-ledger' ); ?></strong><br>
				<a href="<?php echo esc_url( $result['target_url'] ); ?>" target="_blank" rel="noopener noreferrer">
					<?php echo esc_html( $result['target_url'] ); ?>
				</a>
			</p>
			<p>
				<strong><?php echo esc_html__( 'Block Hash (SHA-256):', 'unmer-ledger' ); ?></strong><br>
				<code style="word-break:break-all;"><?php echo esc_html( $result['block_hash'] ); ?></code>
			</p>
		</div>

		<script>
		( function () {
			var btn = document.getElementById( 'unmer-qr-download' );
			if ( ! btn ) { return; }
			btn.addEventListener( 'click', function () {
				var img = document.getElementById( 'unmer-qr-image' );
				if ( ! img ) { return; }
				var link = document.createElement( 'a' );
				link.href = img.src;
				link.download = btn.getAttribute( 'data-filename' ) || 'qr-code.png';
				document.body.appendChild( link );
				link.click();
				document.body.removeChild( link );
			} );
		} )();
		</script>
	<?php endif; ?>

	<form method="post" action="">
		<?php wp_nonce_field( 'unmer_ledger_create', 'unmer_ledger_nonce' ); ?>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row">
						<label for="reference_id"><?php echo esc_html__( 'ID Referensi', 'unmer-ledger' ); ?></label>
					</th>
					<td>
						<input name="reference_id" id="reference_id" type="text"
							value="<?php echo esc_attr( $default_ref ); ?>"
							class="regular-text" required>
						<p class="description"><?php echo esc_html__( 'Format default: YYYYMMDD + 3 digit acak. Dapat diubah bila perlu.', 'unmer-ledger' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="jenis_dokumen"><?php echo esc_html__( 'Jenis Dokumen', 'unmer-ledger' ); ?></label>
					</th>
					<td>
						<select name="jenis_dokumen" id="jenis_dokumen" required>
							<?php foreach ( $jenis_opts as $value => $label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="nomor_dokumen"><?php echo esc_html__( 'Nomor Dokumen', 'unmer-ledger' ); ?></label>
					</th>
					<td>
						<input name="nomor_dokumen" id="nomor_dokumen" type="text"
							value="" class="regular-text" required
							placeholder="042/BAA/UNMER/III/2026">
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="judul_dokumen"><?php echo esc_html__( 'Judul / Perihal / Nama Kegiatan', 'unmer-ledger' ); ?></label>
					</th>
					<td>
						<input name="judul_dokumen" id="judul_dokumen" type="text"
							value="" class="regular-text" required
							placeholder="<?php echo esc_attr__( 'Keterangan Aktif Kuliah', 'unmer-ledger' ); ?>">
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="tanggal_surat"><?php echo esc_html__( 'Tanggal Surat', 'unmer-ledger' ); ?></label>
					</th>
					<td>
						<input name="tanggal_surat" id="tanggal_surat" type="date"
							value="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>" class="regular-text" required>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="penandatangan"><?php echo esc_html__( 'Pejabat Penandatangan', 'unmer-ledger' ); ?></label>
					</th>
					<td>
						<input name="penandatangan" id="penandatangan" type="text"
							value="" class="regular-text" required
							placeholder="Rektor - Prof. Dr. Ir. Budi, M.T.">
					</td>
				</tr>
			</tbody>
		</table>
		<?php submit_button( __( 'Catat Dokumen ke Ledger', 'unmer-ledger' ), 'primary', 'unmer_ledger_submit' ); ?>
	</form>
</div>
