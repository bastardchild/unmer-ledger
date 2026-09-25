# UNMER Digital Ledger Dokumen

Plugin WordPress untuk **pencatatan dan verifikasi dokumen resmi** Universitas Merdeka Malang
berbasis *hash-chain* (blockchain-style ledger). Dokumen fisik (Surat, SK, Sertifikat, Surat Tugas)
diberi QR code; saat QR dipindai, halaman verifikasi resmi akan menampilkan data dokumen dan
membuktikan bahwa metadata dokumen tidak pernah diubah.

Plugin ini **tidak** menyimpan data ledger di database MySQL WordPress. Seluruh catatan
hash-chain disimpan di microservice **FastAPI** (SQLite) yang berjalan lokal di
`http://127.0.0.1:8000`.

---

## Arsitektur

```
Pemindai QR ──HTTPS──▶ WordPress ──HTTP 127.0.0.1:8000──▶ FastAPI ──▶ ledger.db (SQLite)
                      /dokumen/tampilkan/{id}/            (uvicorn, systemd)
```

- **WordPress plugin** (folder ini) — form admin, tampilan QR, halaman verifikasi publik, routing.
- **FastAPI microservice** — penyimpanan append-only hash-chain, hashing SHA-256, generate QR, audit.

> Database WordPress (MySQL) dan database ledger (SQLite) **dipisah sepenuhnya**.
> Komunikasi hanya melalui HTTP localhost (`wp_remote_get` / `wp_remote_post`).

---

## Persyaratan

| Komponen | Versi |
|----------|-------|
| WordPress | 5.8+ |
| PHP | 7.4+ |
| Microservice FastAPI | harus berjalan di `127.0.0.1:8000` |
| Permalink WordPress | **Post name** (pretty permalinks) — wajib agar rewrite berfungsi |

> URL membutuhkan *pretty permalinks*. Bila diatur "Plain", halaman verifikasi tidak akan ditemukan.

---

## Instalasi Plugin

1. Copy folder `unmer-ledger/` ke direktori plugin WordPress:

   ```bash
   sudo cp -r unmer-ledger /var/www/<wp>/wp-content/plugins/
   sudo chown -R www-data:www-data /var/www/<wp>/wp-content/plugins/unmer-ledger
   ```

2. Masuk ke **wp-admin → Plugins**, lalu aktifkan **UNMER Digital Ledger Dokumen**.
   Aktivasi otomatis melakukan *flush rewrite rules*.

3. Buka **Settings → Permalinks**, pilih **Post name**, lalu **Save Changes**
   (untuk memastikan rewrite rule `^dokumen/tampilkan/([^/]+)/?$` aktif).

4. Pastikan microservice FastAPI sudah berjalan dan dapat diakses:
   ```bash
   curl -s http://127.0.0.1:8000/api/ledger/audit
   # {"integrity":true,"total_blocks":0}
   ```

---

## Cara Pakai

### Mencatat dokumen

1. wp-admin → **Pencatatan Dokumen** (butuh kapabilitas `manage_options`).
2. Isi form:
   - **ID Referensi** — default `YYYYMMDD` + 3 digit acak, dapat diubah.
   - **Jenis Dokumen** — Surat Keterangan / SK / Sertifikat / Surat Tugas / Lainnya.
   - **Nomor Dokumen** — contoh `042/BAA/UNMER/III/2026`.
   - **Judul / Perihal** — contoh `Keterangan Aktif Kuliah`.
   - **Tanggal Surat** — tanggal pada dokumen fisik.
   - **Pejabat Penandatangan** — contoh `Rektor - Prof. Dr. Ir. Budi, M.T.`.
3. Klik **Catat Dokumen ke Ledger**.
4. Jika berhasil, akan tampil:
   - Nota sukses WordPress + nomor block.
   - Gambar **QR code**.
   - Tombol **Download QR Code (PNG)** untuk ditempel di dokumen Word/PDF.
   - **Target URL** dan **Block Hash (SHA-256)**.

### Verifikasi dokumen

QR code mengarah ke:

```
https://unmer.ac.id/dokumen/tampilkan/{reference_id}/
```

Contoh: `https://unmer.ac.id/dokumen/tampilkan/202603131/`

- Jika ID terdaftar → tampil kartu verifikasi resmi dengan badge hijau
  **DOKUMEN RESMI TERVERIFIKASI**.
- Jika ID tidak ada / terjadi error → halaman status **404**
  "Dokumen Tidak Ditemukan atau Tidak Sah".

---

## Struktur File

```
unmer-ledger/
├── unmer-ledger.php              # Bootstrap plugin, routing, admin menu, helper
├── includes/
│   └── class-ledger-api.php      # HTTP client ke FastAPI (wp_remote_get/post)
├── views/
│   ├── admin-form.php            # Form pencatatan dokumen + tampilan QR
│   └── verifikasi-template.php   # Halaman verifikasi publik (mobile-first)
├── .gitignore
└── README.md
```

---

## Integrasi Microservice

Base URL dan timeout diatur di `includes/class-ledger-api.php`:

```php
const BASE_URL = 'http://127.0.0.1:8000';
const TIMEOUT  = 10;
```

Endpoint yang digunakan:

| Method | Endpoint | Kegunaan |
|--------|----------|----------|
| `GET`  | `/api/dokumen/{ref_id}` | Ambil data dokumen untuk halaman verifikasi |
| `POST` | `/api/dokumen` | Catat dokumen baru ke ledger |

Bila service tidak dapat dihubungi, plugin menampilkan error yang informatif dan halaman
verifikasi menampilkan status 404 (fail-safe).

---

## Keamanan

- Guard `if ( ! defined( 'ABSPATH' ) ) exit;` di setiap file PHP.
- Semua input disanitasi dengan `sanitize_text_field()` + `wp_unslash()`.
- Semua output di-escape dengan `esc_html()`, `esc_attr()`, `esc_url()`.
- Form admin dilindungi **nonce** (`check_admin_referer`) dan cek kapabilitas `manage_options`.
- `reference_id` di-`rawurlencode()` sebelum dipakai membangun URL ke FastAPI.
- Ledger tidak pernah disimpan di MySQL; FastAPI hanya dapat diakses dari localhost.

---

## Kustomisasi Tampilan

- **Judul halaman**: `Dokumen - Universitas Merdeka Malang` (di `views/verifikasi-template.php`).
- **Font**: Inter dari Google Fonts.
- **Badge verifikasi**: indikator lingkaran hijau berdenyut (`@keyframes pulse`),
  mendukung `prefers-reduced-motion`.
- **Nama bulan**: format Indonesia via `unmer_ledger_bulan()` di `unmer-ledger.php`.
- **Logo & background**: URL gambar UNMER dapat diganti di `views/verifikasi-template.php`.

---

## Troubleshooting

| Gejala | Kemungkinan Penyebab | Solusi |
|--------|----------------------|--------|
| Halaman verifikasi 404 untuk semua ID | Permalink "Plain" / rewrite belum di-flush | Set Permalinks ke "Post name"; deaktivasi lalu aktifkan ulang plugin |
| "Layanan ledger tidak dapat dihubungi" | FastAPI mati / port beda | Jalankan service; cek `curl http://127.0.0.1:8000/api/ledger/audit` |
| Gagal mencatat (ID sudah terdaftar) | `reference_id` duplikat | Gunakan ID Referensi lain |
| QR salah domain | `VERIFY_BASE_URL` berbeda | Set env `VERIFY_BASE_URL` di service sebelum generate QR |

---

## Lisensi

GPL-2.0-or-later — lihat <https://www.gnu.org/licenses/gpl-2.0.html>.
