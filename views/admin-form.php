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

	<?php if ( ! empty( $delete_success ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo esc_html( $delete_success ); ?></p>
		</div>
	<?php endif; ?>

	<div class="card" style="max-width:900px;margin-bottom:30px;padding:20px 24px;">
		<h2 style="margin-top:0;"><?php echo esc_html__( 'Input Dokumen Baru', 'unmer-ledger' ); ?></h2>
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

	<div class="card" style="max-width:100%;padding:20px 24px;">
		<h2 style="margin-top:0;display:flex;justify-content:space-between;align-items:center;">
			<span><?php echo esc_html__( 'Daftar Dokumen Tercatat (Ledger Aktif)', 'unmer-ledger' ); ?></span>
			<span style="font-size:13px;font-weight:normal;color:#666;">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %d: count */
						__( 'Total: %d dokumen aktif', 'unmer-ledger' ),
						count( $dokumen_list )
					)
				);
				?>
			</span>
		</h2>

		<table class="wp-list-table widefat fixed striped" style="margin-top:12px;">
			<thead>
				<tr>
					<th scope="col" style="width:65px;"><?php echo esc_html__( 'Block #', 'unmer-ledger' ); ?></th>
					<th scope="col" style="width:110px;"><?php echo esc_html__( 'ID Referensi', 'unmer-ledger' ); ?></th>
					<th scope="col" style="width:160px;"><?php echo esc_html__( 'No. Dokumen', 'unmer-ledger' ); ?></th>
					<th scope="col"><?php echo esc_html__( 'Judul / Perihal', 'unmer-ledger' ); ?></th>
					<th scope="col" style="width:130px;"><?php echo esc_html__( 'Jenis', 'unmer-ledger' ); ?></th>
					<th scope="col" style="width:105px;"><?php echo esc_html__( 'Tgl. Surat', 'unmer-ledger' ); ?></th>
					<th scope="col" style="width:150px;"><?php echo esc_html__( 'Penandatangan', 'unmer-ledger' ); ?></th>
					<th scope="col" style="width:160px;text-align:center;"><?php echo esc_html__( 'Aksi', 'unmer-ledger' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $dokumen_list ) ) : ?>
					<tr>
						<td colspan="8" style="text-align:center;padding:24px;color:#777;">
							<?php echo esc_html__( 'Belum ada dokumen yang tercatat pada ledger.', 'unmer-ledger' ); ?>
						</td>
					</tr>
				<?php else : ?>
					<?php foreach ( $dokumen_list as $item ) : ?>
						<?php
						$ref     = isset( $item['reference_id'] ) ? $item['reference_id'] : '';
						$url     = isset( $item['target_url'] ) ? $item['target_url'] : home_url( '/dokumen/tampilkan/' . $ref . '/' );
						$tgl_fmt = isset( $item['tanggal_surat'] ) ? unmer_ledger_format_tanggal( $item['tanggal_surat'] ) : '-';
						?>
						<tr>
							<td><strong>#<?php echo esc_html( (string) $item['block_index'] ); ?></strong></td>
							<td><code><?php echo esc_html( $ref ); ?></code></td>
							<td><?php echo esc_html( $item['nomor_dokumen'] ); ?></td>
							<td><strong><?php echo esc_html( $item['judul_dokumen'] ); ?></strong></td>
							<td><?php echo esc_html( $item['jenis_dokumen'] ); ?></td>
							<td><?php echo esc_html( $tgl_fmt ); ?></td>
							<td><?php echo esc_html( $item['penandatangan'] ); ?></td>
							<td style="text-align:center;white-space:nowrap;">
								<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"
									class="button button-small" title="<?php echo esc_attr__( 'Buka Link Verifikasi Publik', 'unmer-ledger' ); ?>">
									🔗 <?php echo esc_html__( 'Lihat', 'unmer-ledger' ); ?>
								</a>
								<form method="post" action="" style="display:inline-block;margin:0 0 0 4px;"
									onsubmit="return confirm('<?php echo esc_js( sprintf( __( 'Apakah Anda yakin ingin menghapus dokumen ID %s?\n\nPERINGATAN: Tautan verifikasi publik tidak akan bisa diakses lagi!', 'unmer-ledger' ), $ref ) ); ?>');">
									<?php wp_nonce_field( 'unmer_ledger_delete_' . $ref, 'unmer_delete_nonce' ); ?>
									<input type="hidden" name="delete_ref_id" value="<?php echo esc_attr( $ref ); ?>">
									<button type="submit" name="unmer_ledger_delete_action"
										class="button button-small button-link-delete"
										style="color:#b32d2e;border-color:#dcdcde;"
										title="<?php echo esc_attr__( 'Hapus dari tampilan dan nonaktifkan link permalink', 'unmer-ledger' ); ?>">
										🗑️ <?php echo esc_html__( 'Hapus', 'unmer-ledger' ); ?>
									</button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
