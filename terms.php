<?php require_once 'config/database.php'; ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syarat & Ketentuan - Fhinz Store</title>
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
                            <li class="breadcrumb-item active text-white">Syarat & Ketentuan</li>
                        </ol>
                    </nav>
                    <h1 class="display-4 mb-3">Syarat & Ketentuan</h1>
                    <p class="lead">Terakhir diperbarui: <?php echo date('d F Y'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Terms Content -->
    <section class="terms-content py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="terms-document">
                        <div class="section mb-5">
                            <h2>1. Penerimaan Syarat</h2>
                            <p>Dengan mengakses dan menggunakan website Fhinz Store, Anda menyetujui untuk terikat oleh Syarat dan Ketentuan ini. Jika Anda tidak setuju dengan syarat ini, mohon untuk tidak menggunakan layanan kami.</p>
                        </div>

                        <div class="section mb-5">
                            <h2>2. Definisi</h2>
                            <ul>
                                <li><strong>"Kami", "Perusahaan"</strong> mengacu pada Fhinz Store</li>
                                <li><strong>"Anda", "Pengguna"</strong> mengacu pada individu yang menggunakan layanan kami</li>
                                <li><strong>"Layanan"</strong> mengacu pada website dan semua produk yang ditawarkan</li>
                                <li><strong>"Produk"</strong> mengacu pada aplikasi premium yang dijual</li>
                                <li><strong>"Akun"</strong> mengacu pada akun pengguna yang terdaftar</li>
                            </ul>
                        </div>

                        <div class="section mb-5">
                            <h2>3. Persyaratan Akun</h2>
                            <h4>3.1 Pendaftaran</h4>
                            <ul>
                                <li>Anda harus berusia minimal 17 tahun untuk membuat akun</li>
                                <li>Informasi yang diberikan harus akurat dan lengkap</li>
                                <li>Satu orang hanya diperbolehkan memiliki satu akun</li>
                                <li>Anda bertanggung jawab menjaga kerahasiaan password</li>
                            </ul>

                            <h4>3.2 Kewajiban Pengguna</h4>
                            <ul>
                                <li>Menggunakan layanan sesuai dengan hukum yang berlaku</li>
                                <li>Tidak menyalahgunakan atau merusak sistem</li>
                                <li>Tidak berbagi akun dengan orang lain</li>
                                <li>Melaporkan aktivitas mencurigakan</li>
                            </ul>
                        </div>

                        <div class="section mb-5">
                            <h2>4. Produk dan Layanan</h2>
                            <h4>4.1 Jenis Produk</h4>
                            <p>Kami menyediakan akses ke aplikasi premium yang legal dan original, termasuk:</p>
                            <ul>
                                <li>Aplikasi streaming (Netflix, Spotify, YouTube Premium)</li>
                                <li>Aplikasi produktivitas (Canva Pro, Adobe Creative)</li>
                                <li>Aplikasi editing (CapCut Pro, Alight Motion)</li>
                                <li>Layanan digital lainnya</li>
                            </ul>

                            <h4>4.2 Garansi Produk</h4>
                            <ul>
                                <li>Semua produk dijamin original dan berfungsi</li>
                                <li>Garansi penggantian jika produk bermasalah</li>
                                <li>Durasi garansi sesuai dengan yang tertera pada produk</li>
                                <li>Claim garansi harus disertai bukti pembelian</li>
                            </ul>
                        </div>

                        <div class="section mb-5">
                            <h2>5. Pembayaran dan Harga</h2>
                            <h4>5.1 Harga</h4>
                            <ul>
                                <li>Harga yang tercantum adalah dalam Rupiah (IDR)</li>
                                <li>Harga dapat berubah sewaktu-waktu tanpa pemberitahuan</li>
                                <li>Harga yang berlaku adalah pada saat transaksi</li>
                                <li>Tidak ada biaya tersembunyi</li>
                            </ul>

                            <h4>5.2 Metode Pembayaran</h4>
                            <ul>
                                <li>Transfer bank (BCA, BNI, BRI, Mandiri)</li>
                                <li>E-wallet (GoPay, OVO, DANA, ShopeePay)</li>
                                <li>QRIS (semua aplikasi pembayaran)</li>
                                <li>Cryptocurrency (Bitcoin, Ethereum)</li>
                            </ul>

                            <h4>5.3 Kebijakan Refund</h4>
                            <ul>
                                <li>Refund hanya berlaku jika produk tidak dapat digunakan</li>
                                <li>Permintaan refund harus diajukan maksimal 24 jam setelah pembelian</li>
                                <li>Refund akan diproses dalam 3-7 hari kerja</li>
                                <li>Biaya transfer refund ditanggung pembeli</li>
                            </ul>
                        </div>

                        <div class="section mb-5">
                            <h2>6. Pengiriman dan Penyerahan</h2>
                            <ul>
                                <li>Produk dikirim via email yang terdaftar</li>
                                <li>Pengiriman dilakukan maksimal 2 jam setelah pembayaran dikonfirmasi</li>
                                <li>Pastikan email Anda aktif dan dapat menerima pesan</li>
                                <li>Hubungi customer service jika belum menerima produk dalam 4 jam</li>
                            </ul>
                        </div>

                        <div class="section mb-5">
                            <h2>7. Hak Kekayaan Intelektual</h2>
                            <ul>
                                <li>Semua konten website adalah milik Fhinz Store</li>
                                <li>Dilarang menyalin, memodifikasi, atau mendistribusikan konten tanpa izin</li>
                                <li>Logo, merek dagang, dan desain dilindungi hak cipta</li>
                                <li>Produk yang dijual tetap menjadi milik pemilik asli</li>
                            </ul>
                        </div>

                        <div class="section mb-5">
                            <h2>8. Larangan Penggunaan</h2>
                            <p>Anda dilarang:</p>
                            <ul>
                                <li>Menggunakan layanan untuk tujuan ilegal</li>
                                <li>Melakukan reverse engineering pada sistem</li>
                                <li>Mengganggu atau merusak server dan jaringan</li>
                                <li>Menjual kembali produk tanpa izin</li>
                                <li>Menggunakan bot atau script otomatis</li>
                                <li>Melakukan spam atau phishing</li>
                                <li>Mengunggah virus atau malware</li>
                            </ul>
                        </div>

                        <div class="section mb-5">
                            <h2>9. Privasi dan Keamanan</h2>
                            <ul>
                                <li>Kami menghormati privasi pengguna sesuai Kebijakan Privasi</li>
                                <li>Data pribadi dilindungi dengan enkripsi SSL</li>
                                <li>Kami tidak menjual data pribadi ke pihak ketiga</li>
                                <li>Pengguna bertanggung jawab menjaga keamanan akun</li>
                            </ul>
                        </div>

                        <div class="section mb-5">
                            <h2>10. Pembatasan Tanggung Jawab</h2>
                            <ul>
                                <li>Layanan disediakan "sebagaimana adanya"</li>
                                <li>Kami tidak bertanggung jawab atas kerugian tidak langsung</li>
                                <li>Tanggung jawab kami terbatas pada nilai produk yang dibeli</li>
                                <li>Kami tidak menjamin ketersediaan layanan 100%</li>
                                <li>Force majeure membebaskan kami dari kewajiban</li>
                            </ul>
                        </div>

                        <div class="section mb-5">
                            <h2>11. Penangguhan dan Penghentian</h2>
                            <h4>11.1 Penangguhan Akun</h4>
                            <p>Kami dapat menangguhkan akun Anda jika:</p>
                            <ul>
                                <li>Melanggar syarat dan ketentuan</li>
                                <li>Aktivitas mencurigakan terdeteksi</li>
                                <li>Permintaan dari pihak berwajib</li>
                                <li>Tidak membayar tagihan</li>
                            </ul>

                            <h4>11.2 Penghentian Layanan</h4>
                            <ul>
                                <li>Anda dapat menghentikan layanan kapan saja</li>
                                <li>Kami dapat menghentikan layanan dengan pemberitahuan 30 hari</li>
                                <li>Data akan dihapus setelah penghentian layanan</li>
                                <li>Kewajiban pembayaran tetap berlaku</li>
                            </ul>
                        </div>

                        <div class="section mb-5">
                            <h2>12. Perubahan Syarat</h2>
                            <ul>
                                <li>Kami berhak mengubah syarat dan ketentuan</li>
                                <li>Perubahan akan dinotifikasi melalui website atau email</li>
                                <li>Penggunaan layanan setelah perubahan dianggap persetujuan</li>
                                <li>Jika tidak setuju, Anda dapat menghentikan penggunaan layanan</li>
                            </ul>
                        </div>

                        <div class="section mb-5">
                            <h2>13. Penyelesaian Sengketa</h2>
                            <h4>13.1 Mediasi</h4>
                            <ul>
                                <li>Sengketa akan diselesaikan melalui mediasi terlebih dahulu</li>
                                <li>Customer service akan membantu penyelesaian masalah</li>
                                <li>Proses mediasi berlangsung maksimal 14 hari</li>
                            </ul>

                            <h4>13.2 Arbitrase</h4>
                            <ul>
                                <li>Jika mediasi gagal, sengketa akan diselesaikan melalui arbitrase</li>
                                <li>Arbitrase dilakukan di Jakarta, Indonesia</li>
                                <li>Keputusan arbitrase bersifat final dan mengikat</li>
                                <li>Biaya arbitrase ditanggung pihak yang kalah</li>
                            </ul>
                        </div>

                        <div class="section mb-5">
                            <h2>14. Hukum yang Berlaku</h2>
                            <ul>
                                <li>Syarat dan ketentuan ini tunduk pada hukum Indonesia</li>
                                <li>Pengadilan Jakarta memiliki yurisdiksi eksklusif</li>
                                <li>Jika ada ketentuan yang tidak berlaku, ketentuan lain tetap berlaku</li>
                            </ul>
                        </div>

                        <div class="section mb-5">
                            <h2>15. Kontak dan Bantuan</h2>
                            <p>Untuk pertanyaan tentang Syarat dan Ketentuan ini:</p>
                            <div class="contact-info">
                                <p><strong>Customer Service:</strong></p>
                                <p><i class="fas fa-envelope me-2"></i> Email: support@fhinzstore.com</p>
                                <p><i class="fab fa-whatsapp me-2"></i> WhatsApp: +62 812-3456-7890</p>
                                <p><i class="fas fa-phone me-2"></i> Telepon: +62 21-1234-5678</p>
                                <p><i class="fas fa-map-marker-alt me-2"></i> Alamat: Jl. Sudirman No. 123, Pekanbaru, Riau 28117</p>
                                <p><i class="fas fa-clock me-2"></i> Jam Operasional: Senin-Minggu, 08:00-22:00 WIB</p>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-triangle me-2"></i>Penting</h5>
                            <p class="mb-2">Dengan mendaftar dan menggunakan layanan Fhinz Store, Anda menyatakan bahwa:</p>
                            <ul class="mb-0">
                                <li>Telah membaca dan memahami seluruh syarat dan ketentuan</li>
                                <li>Setuju untuk terikat oleh semua ketentuan yang berlaku</li>
                                <li>Akan menggunakan layanan dengan itikad baik</li>
                                <li>Berkomitmen untuk mematuhi semua peraturan yang berlaku</li>
                            </ul>
                        </div>

                        <div class="text-center mt-5">
                            <p class="text-muted">Dokumen ini terakhir diperbarui pada <?php echo date('d F Y'); ?></p>
                            <p class="text-muted">© <?php echo date('Y'); ?> Fhinz Store. Hak Cipta Dilindungi.</p>
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
        .terms-document h2 {
            color: #007bff;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .terms-document h4 {
            color: #495057;
            margin-top: 25px;
            margin-bottom: 15px;
        }

        .terms-document ul {
            margin-bottom: 20px;
        }

        .terms-document li {
            margin-bottom: 8px;
        }

        .contact-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }

        .contact-info p {
            margin-bottom: 10px;
        }

        .contact-info i {
            color: #007bff;
            width: 20px;
        }

        .section {
            line-height: 1.7;
        }

        .alert ul {
            padding-left: 20px;
        }

        @media (max-width: 768px) {
            .terms-document h2 {
                font-size: 1.5rem;
            }
            
            .terms-document h4 {
                font-size: 1.2rem;
            }
        }
    </style>
</body>
</html>