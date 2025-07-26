<?php require_once 'config/database.php'; ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebijakan Privasi - Fhinz Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Page Header -->
    <section class="page-header py-5 bg-primary text-white">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php" class="text-white-50">Beranda</a></li>
                            <li class="breadcrumb-item active text-white">Kebijakan Privasi</li>
                        </ol>
                    </nav>
                    <h1 class="display-4 mb-3">Kebijakan Privasi</h1>
                    <p class="lead">Terakhir diperbarui: <?php echo date('d F Y'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Privacy Policy Content -->
    <section class="privacy-content py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="privacy-document">
                        <div class="section mb-5">
                            <h2>1. Pendahuluan</h2>
                            <p>Fhinz Store ("kami", "perusahaan") berkomitmen untuk melindungi privasi Anda. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi pribadi Anda ketika menggunakan layanan kami.</p>
                        </div>

                        <div class="section mb-5">
                            <h2>2. Informasi yang Kami Kumpulkan</h2>
                            <h4>2.1 Informasi Pribadi</h4>
                            <p>Kami mengumpulkan informasi pribadi yang Anda berikan secara langsung, termasuk:</p>
                            <ul>
                                <li>Nama lengkap</li>
                                <li>Alamat email</li>
                                <li>Nomor telepon</li>
                                <li>Username dan password</li>
                                <li>Informasi pembayaran</li>
                            </ul>

                            <h4>2.2 Informasi Otomatis</h4>
                            <p>Kami secara otomatis mengumpulkan informasi tertentu ketika Anda menggunakan layanan kami:</p>
                            <ul>
                                <li>Alamat IP</li>
                                <li>Jenis browser dan perangkat</li>
                                <li>Halaman yang dikunjungi</li>
                                <li>Waktu dan tanggal kunjungan</li>
                                <li>Data cookie dan teknologi serupa</li>
                            </ul>
                        </div>

                        <div class="section mb-5">
                            <h2>3. Bagaimana Kami Menggunakan Informasi Anda</h2>
                            <p>Kami menggunakan informasi yang dikumpulkan untuk:</p>
                            <ul>
                                <li>Menyediakan dan mengelola layanan kami</li>
                                <li>Memproses transaksi dan pesanan</li>
                                <li>Berkomunikasi dengan Anda tentang pesanan dan layanan</li>
                                <li>Memberikan dukungan pelanggan</li>
                                <li>Meningkatkan website dan layanan kami</li>
                                <li>Mengirim informasi promosi (dengan persetujuan Anda)</li>
                                <li>Mematuhi kewajiban hukum</li>
                            </ul>
                        </div>

                        <div class="section mb-5">
                            <h2>4. Berbagi Informasi</h2>
                            <p>Kami tidak menjual, menyewakan, atau memberikan informasi pribadi Anda kepada pihak ketiga, kecuali dalam situasi berikut:</p>
                            <ul>
                                <li>Dengan persetujuan eksplisit dari Anda</li>
                                <li>Untuk memproses pembayaran melalui penyedia layanan pembayaran</li>
                                <li>Ketika diwajibkan oleh hukum atau proses hukum</li>
                                <li>Untuk melindungi hak, properti, atau keamanan kami dan pengguna lain</li>
                                <li>Dalam hal merger, akuisisi, atau penjualan aset perusahaan</li>
                            </ul>
                        </div>

                        <div class="section mb-5">
                            <h2>5. Keamanan Data</h2>
                            <p>Kami menerapkan langkah-langkah keamanan yang tepat untuk melindungi informasi pribadi Anda:</p>
                            <ul>
                                <li>Enkripsi SSL untuk transmisi data</li>
                                <li>Enkripsi password dengan algoritma aman</li>
                                <li>Akses terbatas ke informasi pribadi</li>
                                <li>Pemantauan keamanan secara berkala</li>
                                <li>Backup data reguler</li>
                            </ul>
                        </div>

                        <div class="section mb-5">
                            <h2>6. Cookie dan Teknologi Pelacakan</h2>
                            <p>Kami menggunakan cookie dan teknologi serupa untuk:</p>
                            <ul>
                                <li>Mengingat preferensi Anda</li>
                                <li>Menyediakan fitur keamanan</li>
                                <li>Menganalisis penggunaan website</li>
                                <li>Memberikan konten yang dipersonalisasi</li>
                            </ul>
                            <p>Anda dapat mengatur browser untuk menolak cookie, namun beberapa fitur website mungkin tidak berfungsi dengan baik.</p>
                        </div>

                        <div class="section mb-5">
                            <h2>7. Hak Anda</h2>
                            <p>Anda memiliki hak untuk:</p>
                            <ul>
                                <li>Mengakses informasi pribadi yang kami miliki tentang Anda</li>
                                <li>Memperbarui atau mengoreksi informasi pribadi Anda</li>
                                <li>Menghapus akun dan informasi pribadi Anda</li>
                                <li>Menolak pemrosesan informasi pribadi Anda</li>
                                <li>Memindahkan data Anda ke penyedia layanan lain</li>
                                <li>Mengajukan keluhan kepada otoritas perlindungan data</li>
                            </ul>
                        </div>

                        <div class="section mb-5">
                            <h2>8. Penyimpanan Data</h2>
                            <p>Kami menyimpan informasi pribadi Anda selama:</p>
                            <ul>
                                <li>Akun Anda aktif</li>
                                <li>Diperlukan untuk menyediakan layanan</li>
                                <li>Diwajibkan oleh hukum yang berlaku</li>
                                <li>Diperlukan untuk tujuan bisnis yang sah</li>
                            </ul>
                            <p>Setelah periode penyimpanan berakhir, kami akan menghapus atau menganonimkan informasi pribadi Anda.</p>
                        </div>

                        <div class="section mb-5">
                            <h2>9. Layanan Pihak Ketiga</h2>
                            <p>Website kami mungkin berisi tautan ke website pihak ketiga. Kami tidak bertanggung jawab atas praktik privasi website pihak ketiga. Kami menyarankan Anda untuk membaca kebijakan privasi setiap website yang Anda kunjungi.</p>
                        </div>

                        <div class="section mb-5">
                            <h2>10. Perubahan Kebijakan</h2>
                            <p>Kami dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu. Perubahan akan dinotifikasi melalui:</p>
                            <ul>
                                <li>Pemberitahuan di website</li>
                                <li>Email ke alamat terdaftar Anda</li>
                                <li>Notifikasi dalam aplikasi</li>
                            </ul>
                            <p>Penggunaan layanan kami setelah perubahan kebijakan menandakan persetujuan Anda terhadap kebijakan yang diperbarui.</p>
                        </div>

                        <div class="section mb-5">
                            <h2>11. Kontak Kami</h2>
                            <p>Jika Anda memiliki pertanyaan tentang Kebijakan Privasi ini atau ingin menggunakan hak-hak Anda, hubungi kami:</p>
                            <div class="contact-info">
                                <p><strong>Email:</strong> privacy@fhinzstore.com</p>
                                <p><strong>Telepon:</strong> +62 21-1234-5678</p>
                                <p><strong>Alamat:</strong> Jl. Sudirman No. 123, Pekanbaru, Riau 28117</p>
                                <p><strong>WhatsApp:</strong> +62 812-3456-7890</p>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle me-2"></i>Catatan Penting</h5>
                            <p class="mb-0">Kebijakan Privasi ini merupakan bagian integral dari Syarat dan Ketentuan penggunaan layanan Fhinz Store. Dengan menggunakan layanan kami, Anda menyetujui pengumpulan dan penggunaan informasi sesuai dengan kebijakan ini.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>

    <style>
        .privacy-document h2 {
            color: #007bff;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .privacy-document h4 {
            color: #495057;
            margin-top: 25px;
            margin-bottom: 15px;
        }

        .privacy-document ul {
            margin-bottom: 20px;
        }

        .privacy-document li {
            margin-bottom: 8px;
        }

        .contact-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }

        .section {
            line-height: 1.7;
        }

        @media (max-width: 768px) {
            .privacy-document h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</body>
</html>