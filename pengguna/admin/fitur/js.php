<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('tambahForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Menghentikan aksi default form submit

            var form = this;
            var formData = new FormData(form);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'proses/<?= $current_page ?>/tambah.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = xhr.responseText.trim();
                    console.log(response); // Debugging

                    // Menangani setiap respons dari PHP
                    switch (response) {
                        case 'success':
                            form.reset();
                            document.getElementById('closeTambahModal').click();
                            loadTable(); // reload table data

                            Swal.fire({
                                title: "Berhasil!",
                                text: "Data berhasil ditambahkan",
                                icon: "success",
                                timer: 1200, // 1,2 detik
                                showConfirmButton: false, // Tidak menampilkan tombol OK
                            });
                            break;
                        case 'error_nomor_hp_exists':
                            Swal.fire({
                                title: "Error",
                                text: "Nomor HP sudah terdaftar. Silakan gunakan nomor HP lain.",
                                icon: "error",
                                timer: 2000, // 2 detik
                                showConfirmButton: false,
                            });
                            break;
                        case 'error_password_length':
                            Swal.fire({
                                title: "Error",
                                text: "Password harus terdiri dari minimal 8 karakter.",
                                icon: "error",
                                timer: 2000, // 2 detik
                                showConfirmButton: false,
                            });
                            break;
                        case 'error_password_strength':
                            Swal.fire({
                                title: "Error",
                                text: "Password harus mengandung huruf besar, huruf kecil, dan angka.",
                                icon: "error",
                                timer: 2000, // 2 detik
                                showConfirmButton: false,
                            });
                            break;
                        case 'data_tidak_lengkap':
                            Swal.fire({
                                title: "Error",
                                text: "Data yang Anda masukkan belum lengkap.",
                                icon: "error",
                                timer: 2000, // 2 detik
                                showConfirmButton: false,
                            });
                            break;
                        default:
                            Swal.fire({
                                title: "Error",
                                text: "Gagal menambahkan data. Silakan coba lagi.",
                                icon: "error",
                                timer: 2000, // 2 detik
                                showConfirmButton: false,
                            });
                            break;
                    }
                } else {
                    Swal.fire({
                        title: "Error",
                        text: "Terjadi kesalahan saat mengirim data.",
                        icon: "error",
                        timer: 2000, // 2 detik
                        showConfirmButton: false,
                    });
                }
            };
            xhr.send(formData);
        });
    });



    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('editForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Menghentikan aksi default form submit

            var form = this;
            var formData = new FormData(form);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'proses/<?= $current_page ?>/edit.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = xhr.responseText.trim();
                    console.log(response); // Debugging

                    if (response === 'success') {
                        form.reset();
                        document.getElementById('closeEditModal').click();
                        loadTable(); // reload table data

                        Swal.fire({
                            title: "Berhasil!",
                            text: "Data berhasil diperbarui",
                            icon: "success",
                            timer: 1200, // 1,2 detik
                            showConfirmButton: false, // Tidak menampilkan tombol OK
                        });
                    } else if (response === 'error_nomor_hp_exists') {
                        Swal.fire({
                            title: "Error",
                            text: "Nomor HP sudah terdaftar. Silakan gunakan nomor HP lain.",
                            icon: "info",
                            timer: 2000, // 2 detik
                            showConfirmButton: false,
                        });
                    } else if (response === 'error_password_length') {
                        Swal.fire({
                            title: "Error",
                            text: "Password harus memiliki panjang minimal 8 karakter.",
                            icon: "info",
                            timer: 2000, // 2 detik
                            showConfirmButton: false,
                        });
                    } else if (response === 'error_password_strength') {
                        Swal.fire({
                            title: "Error",
                            text: "Password harus mengandung huruf kapital, huruf kecil, dan angka.",
                            icon: "info",
                            timer: 2000, // 2 detik
                            showConfirmButton: false,
                        });
                    } else if (response === 'data_tidak_lengkap') {
                        Swal.fire({
                            title: "Error",
                            text: "Data yang anda masukkan belum lengkap.",
                            icon: "info",
                            timer: 2000, // 2 detik
                            showConfirmButton: false,
                        });
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: "Gagal memperbarui data.",
                            icon: "error",
                            timer: 2000, // 2 detik
                            showConfirmButton: false,
                        });
                    }
                } else {
                    Swal.fire({
                        title: "Error",
                        text: "Terjadi kesalahan saat mengirim data.",
                        icon: "error",
                        timer: 2000, // 2 detik
                        showConfirmButton: false,
                    });
                }
            };
            xhr.send(formData);
        });
    });

    function hapus(id) {
        Swal.fire({
            title: "Apakah Anda yakin?",
            text: "Setelah dihapus, Anda tidak akan dapat memulihkan data ini!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, hapus!",
            cancelButtonText: "Batal",
            dangerMode: true,
        }).then((result) => {
            if (result.isConfirmed) {
                // Jika pengguna mengonfirmasi untuk menghapus
                var xhr = new XMLHttpRequest();

                xhr.open('POST', 'proses/<?= $current_page ?>/hapus.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {

                    if (xhr.status === 200) {
                        var response = xhr.responseText.trim();
                        if (response === 'success') {
                            loadTable();
                            Swal.fire({
                                title: 'Sukses!',
                                text: 'Data berhasil dihapus.',
                                icon: 'success',
                                timer: 1200, // 1,2 detik
                                showConfirmButton: false // Menghilangkan tombol OK
                            }).then(() => {
                                location.reload()
                            })
                        } else if (response === 'error') {
                            Swal.fire({
                                title: 'Error',
                                text: 'Gagal menghapus Data.',
                                icon: 'error',
                                timer: 2000, // 2 detik
                                showConfirmButton: false // Menghilangkan tombol OK
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: 'Terjadi kesalahan saat mengirim data.',
                                icon: 'error',
                                timer: 2000, // 2 detik
                                showConfirmButton: false // Menghilangkan tombol OK
                            });
                        }
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'Terjadi kesalahan saat mengirim data.',
                            icon: 'error',
                            timer: 2000, // 2 detik
                            showConfirmButton: false // Menghilangkan tombol OK
                        });
                    }
                };
                xhr.send("id=" + id);
            } else {
                // Jika pengguna membatalkan penghapusan
                Swal.fire({
                    title: 'Penghapusan dibatalkan',
                    icon: 'info',
                    timer: 1500, // 1,5 detik
                    showConfirmButton: false // Menghilangkan tombol OK
                });
            }
        });
    }

    function copyToClipboard(button, text) {
        // Salin teks ke clipboard
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);

        // Perbarui tooltip dengan pesan sukses
        const tooltip = bootstrap.Tooltip.getInstance(
            button); // Mendapatkan instance tooltip yang ada
        if (tooltip) {
            tooltip.setContent({
                '.tooltip-inner': 'Teks berhasil disalin!'
            });
        }

        // Tampilkan tooltip secara manual
        $(button).tooltip('show');

        // Sembunyikan tooltip setelah beberapa detik
        setTimeout(() => {
            $(button).tooltip('hide');
        }, 2000);
    }

    // Inisialisasi semua tooltip saat dokumen siap
    document.addEventListener('DOMContentLoaded', function() {
        var tooltips = document.querySelectorAll(
            '[data-bs-toggle="tooltip"]');
        tooltips.forEach(function(tooltip) {
            new bootstrap.Tooltip(tooltip);
        });
    });

    function loadTable() {
        // Get current page and search query from URL
        var currentPage = new URLSearchParams(window.location.search).get('page') || 1;
        var searchQuery = new URLSearchParams(window.location.search).get('search') || '';

        var xhrTable = new XMLHttpRequest();
        xhrTable.onreadystatechange = function() {
            if (xhrTable.readyState == 4 && xhrTable.status == 200) {
                document.getElementById('load_data').innerHTML = xhrTable.responseText;
            }
        };

        // Send request with current page and search query
        xhrTable.open('GET', 'proses/<?= $current_page ?>/load_data.php?page=' + currentPage + '&search=' +
            encodeURIComponent(
                searchQuery), true);
        xhrTable.send();
    }
</script>