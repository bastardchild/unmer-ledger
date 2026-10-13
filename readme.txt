=== UNMER Digital Ledger Dokumen ===
Contributors: unmer
Tags: document verification, ledger, hash-chain, qr code, blockchain
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.2
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Pencatatan dan verifikasi dokumen resmi berbasis hash-chain untuk Universitas Merdeka Malang.

== Description ==

Plugin ini mencatat metadata dokumen resmi (Surat Keterangan, Surat Keputusan/SK,
Sertifikat/Piagam, Surat Tugas) ke dalam sebuah *append-only hash-chain* (blockchain-style
ledger) yang dikelola oleh microservice FastAPI lokal, lalu menampilkan halaman verifikasi
resmi mobile-friendly saat QR code pada dokumen fisik dipindai.

= Fitur =

* Form pencatatan dokumen di wp-admin dengan sanitasi & escaping lengkap.
* Generate QR code (PNG / Base64 data URI) yang mengarah ke halaman verifikasi publik.
* Halaman verifikasi responsif dengan badge **DOKUMEN RESMI TERVERIFIKASI**.
* Accordion audit teknis (Block Index, Block Hash SHA-256, Previous Block Hash).
* Deteksi duplikat `reference_id` (HTTP 400) dan penanganan 404 yang aman (fail-safe).
* Database ledger (SQLite) terpisah total dari database MySQL WordPress.

= Catatan Arsitektur =

Plugin ini **bergantung** pada microservice FastAPI yang berjalan di
`http://127.0.0.1:8000`. Plugin tidak menyimpan data ledger di MySQL; seluruh hash-chain
disimpan di SQLite oleh service tersebut dan diakses melalui HTTP localhost.

== Installation ==

1. Copy folder `unmer-ledger/` ke `wp-content/plugins/`.
2. Aktifkan plugin melalui menu **Plugins** di wp-admin (rewrite rules otomatis di-flush).
3. Buka **Settings → Permalinks**, pilih **Post name**, lalu simpan (wajib agar routing berfungsi).
4. Pastikan microservice FastAPI berjalan: `curl http://127.0.0.1:8000/api/ledger/audit`.

== Frequently Asked Questions ==

= Apakah plugin ini butuh WordPress versi tertentu? =

Ya. Plugin ini memerlukan **WordPress 6.0+** dan **PHP 8.2+**.

= Apakah data ledger disimpan di database WordPress? =

Tidak. Ledger disimpan di SQLite (`ledger.db`) oleh microservice FastAPI dan hanya diakses
melalui HTTP localhost. Database MySQL WordPress dan database ledger terpisah sepenuhnya.

= Kenapa halaman verifikasi menampilkan 404? =

Kemungkinan penyebab: (1) permalink belum diatur ke "Post name", (2) microservice FastAPI
sedang tidak berjalan, atau (3) `reference_id` memang tidak terdaftar pada ledger.

= Bagaimana cara mengubah domain pada QR code? =

Set environment variable `VERIFY_BASE_URL` pada microservice FastAPI sebelum mencatat dokumen.
Nilai default adalah `https://unmer.ac.id`.

== Screenshots ==

1. Form Pencatatan Dokumen di wp-admin.
2. Tampilan QR code dan tombol unduh setelah pencatatan berhasil.
3. Halaman verifikasi publik (kartu dokumen resmi).

== Changelog ==

= 1.0.0 =
* Rilis awal.
* Pencatatan dokumen ke hash-chain, generate QR, halaman verifikasi publik.
* Memerlukan WordPress 7.1+ dan PHP 8.2+.

== Upgrade Notice ==

= 1.0.0 =
Rilis awal plugin UNMER Digital Ledger Dokumen.
