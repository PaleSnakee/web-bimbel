---
description: Project Rules
---

# Project Rules — Aplikasi Manajemen Bimbingan Belajar

## 1. Identitas Project

- Nama project: Aplikasi Manajemen Bimbingan Belajar Berbasis Web.
- Framework: Laravel. Pertahankan versi Laravel yang sudah digunakan project.
- Backend: PHP.
- Database: MySQL.
- Frontend: Blade, HTML, CSS, JavaScript, dan teknologi frontend yang sudah tersedia.
- Metode pengembangan: Waterfall.
- Bahasa komunikasi: Bahasa Indonesia.
- Tujuan: Menghasilkan aplikasi KP yang stabil, terstruktur, aman, dan mudah dipelihara.

## 2. Aturan Utama Sebelum Coding

- Baca dan pahami kode yang relevan sebelum melakukan perubahan.
- Periksa struktur folder, routes, controllers, models, migrations, views, middleware, dan konfigurasi yang berkaitan.
- Ikuti pola arsitektur dan konvensi yang sudah digunakan dalam project.
- Jangan mengasumsikan nama tabel, kolom, model, route, atau role. Periksa implementasi yang sudah ada.
- Jangan mengubah versi dependency atau framework tanpa alasan yang jelas.
- Jangan membuat ulang fitur yang sebenarnya sudah tersedia.
- Jika terdapat informasi penting yang belum jelas, tanyakan sebelum membuat perubahan berisiko.
- Prioritaskan perubahan kecil, terarah, dan mudah diuji.

## 3. Fitur Utama Aplikasi

### A. Autentikasi dan Hak Akses

- Dukung role yang memang sudah ditentukan dalam project.
- Setiap role hanya boleh mengakses fitur sesuai kewenangannya.
- Periksa autentikasi dan otorisasi pada backend, bukan hanya menyembunyikan tombol di frontend.
- Validasi akses pada route, controller, dan operasi data yang relevan.
- Jangan mengasumsikan nama atau jumlah role sebelum memeriksa implementasi.
- Jangan mengubah sistem login yang sudah berjalan tanpa kebutuhan yang jelas.

### B. Manajemen Murid

- Mendukung pengelolaan data murid.
- Sediakan operasi tambah, lihat, ubah, dan hapus sesuai kebutuhan sistem.
- Kelola informasi kelas atau jenjang PAUD hingga SMP jika struktur database mendukungnya.
- Validasi data sebelum disimpan.
- Gunakan relasi database yang konsisten.
- Pastikan penghapusan data tidak merusak data terkait.

### C. Manajemen Absensi

- Mendukung pencatatan dan pengelolaan kehadiran murid.
- Hubungkan absensi dengan murid dan kelas atau jadwal yang relevan.
- Cegah pencatatan absensi ganda pada sesi yang sama jika aturan bisnis mengharuskannya.
- Validasi tanggal, status kehadiran, dan hak akses.
- Sediakan rekap absensi apabila sesuai dengan kebutuhan fitur.

### D. Try Out Online

- Mendukung pengelolaan paket atau sesi try out.
- Mendukung soal pilihan ganda dan esai.
- Gunakan relasi yang konsisten antara paket, soal, peserta, dan jawaban.
- Terapkan batas waktu pengerjaan jika fitur tersebut sudah ditentukan.
- Validasi jawaban dan status pengerjaan pada backend.
- Jangan mempercayai timer atau nilai yang dikirim hanya dari browser.
- Cegah pengiriman jawaban berulang yang tidak sesuai aturan.
- Tentukan penilaian otomatis untuk pilihan ganda dan penilaian esai sesuai kebutuhan sistem.
- Jangan mengubah mekanisme penilaian yang sudah ada tanpa memeriksa dampaknya.

### E. Dashboard Orang Tua

- Tampilkan informasi hanya untuk anak yang terhubung dengan akun orang tua tersebut.
- Sediakan akses ke nilai akhir per mata pelajaran.
- Tampilkan catatan perkembangan murid.
- Tampilkan saran dari tutor jika tersedia.
- Pastikan orang tua tidak dapat melihat data murid lain.
- Terapkan otorisasi pada setiap endpoint dan query data.

## 4. Aturan Laravel dan Backend

- Gunakan pola MVC Laravel.
- Tempatkan business logic pada komponen yang sesuai dengan arsitektur project.
- Gunakan Form Request untuk validasi kompleks jika sesuai dengan pola project.
- Gunakan Eloquent ORM dan relasi model bila memungkinkan.
- Hindari query berulang yang tidak diperlukan.
- Gunakan eager loading ketika dibutuhkan untuk mencegah masalah N+1.
- Gunakan route name yang konsisten.
- Gunakan middleware atau policy untuk pembatasan akses.
- Gunakan database transaction untuk operasi yang harus berhasil atau gagal secara bersamaan.
- Gunakan mass assignment secara aman melalui $fillable atau $guarded.
- Tangani error dengan tepat; jangan menampilkan stack trace atau informasi sensitif kepada pengguna.
- Jangan menaruh business logic penting hanya di Blade atau JavaScript.

## 5. Aturan Database

- Periksa migration dan skema database sebelum mengubah struktur.
- Gunakan foreign key jika sesuai dengan relasi dan kebutuhan sistem.
- Gunakan tipe data yang sesuai untuk setiap kolom.
- Tambahkan index jika query dan kebutuhan data membutuhkannya.
- Jangan mengubah atau menghapus migration lama secara sembarangan.
- Buat migration baru untuk perubahan skema yang diperlukan.
- Jangan menjalankan migrate:fresh, db:wipe, atau perintah yang menghapus data tanpa persetujuan eksplisit.
- Jangan menghapus data nyata untuk keperluan testing.
- Pastikan perubahan skema tetap kompatibel dengan kode yang menggunakannya.

## 6. Aturan Frontend dan UI/UX

- Gunakan komponen, layout, dan sidebar global yang sudah ada.
- Pertahankan konsistensi tampilan antarhalaman.
- Utamakan desain profesional, sederhana, bersih, dan mudah digunakan.
- Gunakan latar putih dan warna utama hitam jika sesuai dengan desain project yang sudah disepakati.
- Hindari penggunaan warna ungu sebagai gaya visual default.
- Gunakan tipografi yang konsisten dan keterbacaan yang baik.
- Pastikan tampilan responsif pada desktop, tablet, dan ponsel.
- Gunakan label form yang jelas, validasi yang informatif, dan feedback setelah aksi pengguna.
- Sediakan loading, empty state, error state, dan konfirmasi penghapusan jika relevan.
- Jangan mengubah seluruh desain aplikasi ketika hanya satu komponen yang perlu diperbaiki.
- Gunakan library UI yang sudah terpasang; jangan menambahkan dependency hanya untuk perubahan kecil.
- Pertahankan aksesibilitas dasar, termasuk label input dan navigasi keyboard.

## 7. Aturan Keamanan

- Validasi semua input pada backend.
- Gunakan proteksi CSRF sesuai mekanisme Laravel.
- Cegah akses IDOR dengan memeriksa kepemilikan dan otorisasi data.
- Jangan mempercayai role, user ID, nilai, atau status yang dikirim dari frontend tanpa verifikasi.
- Jangan menyimpan password dalam bentuk plaintext.
- Jangan menampilkan credential, APP_KEY, token, atau informasi sensitif.
- Jangan menonaktifkan middleware keamanan hanya untuk mengatasi error.
- Gunakan query builder atau Eloquent dengan parameter binding.
- Terapkan pembatasan akses sesuai prinsip least privilege.

## 8. Aturan Perubahan Kode

- Ubah hanya file yang berkaitan dengan permintaan.
- Hindari refactor besar tanpa kebutuhan.
- Jangan menghapus fungsi, route, validasi, atau komponen yang sudah berjalan tanpa alasan yang jelas.
- Jangan mengganti nama class, method, route, atau kolom tanpa memeriksa semua dependensinya.
- Jangan membuat file duplikat dengan fungsi yang sama.
- Jangan menghasilkan kode placeholder, pseudocode, atau TODO untuk fitur yang diminta selesai.
- Jika membuat atau mengubah fitur, pastikan seluruh alur terkait ikut diperiksa: route, controller, model, database, view, dan hak akses.
- Jangan mengklaim fitur selesai sebelum melakukan pemeriksaan yang relevan.

## 9. Aturan Debugging

- Identifikasi penyebab error sebelum mengubah kode.
- Baca pesan error dan periksa file serta baris yang terkait.
- Periksa log Laravel di storage/logs jika diperlukan.
- Berikan penjelasan singkat mengenai penyebab dan solusi.
- Hindari perubahan acak yang tidak berkaitan dengan penyebab masalah.
- Setelah memperbaiki error, periksa kemungkinan dampak pada fitur lain.
- Jangan menyembunyikan error dengan mematikan exception handling atau validasi.

## 10. Testing dan Verifikasi

- Periksa route menggunakan php artisan route:list jika relevan.
- Jalankan test yang sesuai jika test tersedia.
- Verifikasi validasi, hak akses, relasi data, dan alur utama fitur.
- Periksa syntax dan konsistensi kode yang diubah.
- Gunakan perintah testing yang kompatibel dengan versi Laravel project.
- Jangan mengklaim test berhasil jika belum dijalankan.
- Jika test tidak dapat dijalankan, jelaskan alasannya dan berikan langkah verifikasi manual.

## 11. Workflow Pengerjaan

Untuk setiap permintaan coding:

1. Pahami kebutuhan dan kriteria keberhasilan.
2. Periksa implementasi yang sudah ada.
3. Identifikasi file yang perlu diubah.
4. Jelaskan rencana singkat sebelum perubahan besar.
5. Implementasikan solusi secara bertahap.
6. Periksa kompatibilitas dengan fitur yang sudah ada.
7. Jalankan pemeriksaan atau test yang relevan.
8. Laporkan hasil, file yang diubah, dan langkah pengujian.

Kerjakan satu fitur atau satu kelompok perubahan terkait dalam satu tahap.
Jangan langsung membangun ulang seluruh aplikasi ketika diminta mengerjakan satu fitur.

## 12. Format Jawaban AI

Saat membantu coding, gunakan Bahasa Indonesia yang jelas.

Sebelum implementasi, jelaskan:

- Tujuan perubahan.
- File yang akan diperiksa atau diubah.
- Pendekatan yang digunakan.

Setelah implementasi, jelaskan:

- Perubahan yang dilakukan.
- File yang berubah dan fungsinya.
- Cara menjalankan atau menguji perubahan.
- Potensi dampak atau pekerjaan lanjutan yang masih diperlukan.

Jika memberikan kode, berikan kode lengkap untuk bagian yang diminta dan pastikan nama class, method, route, serta variabel konsisten dengan project.

Jika pengguna meminta seluruh kode sebuah file, berikan isi file secara lengkap, bukan potongan yang mengharuskan pengguna menebak bagian lainnya.

## 13. Prinsip Prioritas

Urutan prioritas:

1. Kebenaran fungsi dan keamanan.
2. Kompatibilitas dengan kode yang sudah ada.
3. Konsistensi database dan business logic.
4. Kemudahan penggunaan dan responsivitas UI.
5. Maintainability dan kualitas kode.
6. Optimasi tambahan jika memang diperlukan.

Jangan mengorbankan kestabilan aplikasi hanya demi membuat kode lebih singkat.
