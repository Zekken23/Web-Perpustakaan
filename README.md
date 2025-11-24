# SIM Perpustakaan

![Struktur Project](/mnt/data/e27619ad-b883-4628-b928-1393734b5b11.png)

Sistem Informasi Manajemen (SIM) Perpustakaan berbasis PHP sederhana dengan dua peran: **admin** dan **user**. Sistem ini menyediakan fitur manajemen buku (CRUD), autentikasi, proses peminjaman yang harus disetujui admin, fitur favorit, riwayat peminjaman/membaca, dan pengembalian buku.

---

## Fitur Utama

### Untuk Admin

* Autentikasi (login/logout).
* CRUD buku: tambah, ubah, hapus, dan melihat daftar buku.
* Melihat permintaan pinjaman dari user dan melakukan **approve/decline**.
* Kelola data peminjaman (mengganti status peminjaman, mengonfirmasi pengembalian).
* Akses ke dashboard admin dan halaman edit/tambah buku.

### Untuk User

* Registrasi dan login.
* Melihat semua daftar buku yang tersedia.
* Mengajukan permintaan peminjaman buku (pinjam).
* Melihat status permintaan pinjaman (pending, disetujui, ditolak).
* Fitur favorit untuk menandai buku.
* Riwayat membaca / riwayat peminjaman.
* Mengembalikan buku (mengirim permintaan pengembalian yang dikonfirmasi oleh admin).

---

## Alur Kerja Peminjaman (singkat)

1. User memilih sebuah buku dan mengajukan pinjaman.
2. Admin menerima notifikasi permintaan pinjaman di dashboard.
3. Admin memeriksa dan menyetujui atau menolak permintaan.
4. Setelah disetujui, status peminjaman berubah menjadi "dipinjam".
5. User mengembalikan buku — admin mengonfirmasi pengembalian.

---

## Struktur Project (ringkasan)

Struktur di repository (contoh dari project):

```
/actions
  favorit_toogle.php
/assets
  foto/
  animate.js
  style.css
/config
  database.php
/views
  /admin
    admin_dashboard.php
    admin_edit.php
    admin_pinjam.php
    admin_tambah.php
    kelola_buku.php
  /user
    favorit.php
    pinjam_buku.php
    profile.php
    riwayat.php
    user_dashboard.php
index.php
register.php
login.php
logout.php
```

> Gambar struktur repository disertakan di atas.

---

## Instalasi & Konfigurasi

1. Clone repository:

```bash
git clone <repo-url>
cd nama-repo
```

2. Buat database MySQL dan import struktur tabel (contoh SQL sederhana):

```sql
-- tabel users
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100),
  email VARCHAR(150) UNIQUE,
  password VARCHAR(255),
  role ENUM('admin','user') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- tabel buku
CREATE TABLE buku (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(255),
  pengarang VARCHAR(255),
  penerbit VARCHAR(255),
  tahun YEAR,
  stok INT DEFAULT 1,
  cover VARCHAR(255),
  deskripsi TEXT
);

-- tabel pinjaman
CREATE TABLE pinjaman (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  buku_id INT,
  tanggal_pinjam DATE,
  tanggal_kembali DATE NULL,
  status ENUM('pending','disetujui','ditolak','dikembalikan') DEFAULT 'pending',
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (buku_id) REFERENCES buku(id)
);

-- tabel favorit
CREATE TABLE favorit (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  buku_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

3. Konfigurasi koneksi database di `config/database.php`.
4. Sesuaikan `base_url` atau konfigurasi path jika diperlukan.
5. Jalankan aplikasi di local server (mis. XAMPP, MAMP) di folder project.

---

## Autentikasi & Keamanan

* Password disarankan disimpan dengan hashing (`password_hash` / `password_verify`).
* Session digunakan untuk menyimpan data login; sesi admin memproteksi halaman admin.
* Periksa validasi input untuk mencegah SQL injection (gunakan prepared statements / PDO).

---

## Catatan Implementasi

* File `actions/*.php` menangani operasi create/update/delete dan toggle favorit.
* `includes/auth.php` digunakan untuk memeriksa apakah user/ admin sudah login sebelum mengakses halaman tertentu.
* `views/admin/admin_pinjam.php` berfungsi menampilkan daftar permintaan pinjaman untuk di-approve.
* Fitur favorit dapat di-toggle melalui `favorit_toogle.php`.
* Riwayat pengguna tersimpan di `views/user/riwayat.php` dengan mengambil record dari tabel `pinjaman`.

---

## Pengembangan & Kontribusi

* Gunakan branch baru untuk fitur besar.
* Buat issue untuk bug atau permintaan fitur.
* Pull request harus disertai deskripsi perubahan dan testing singkat.

---

## Lisensi

Silakan tambahkan lisensi yang sesuai (mis. MIT) di file `LICENSE` jika ingin membuka kode untuk publik.

---

Jika Anda ingin, saya bisa membantu membuat `README.md` versi bahasa Inggris, menambahkan contoh endpoint, atau menulis skrip SQL lengkap untuk schema dan seed data.
