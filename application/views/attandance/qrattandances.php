<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BPI - Attendance</title>
    <link rel="icon" href="<?= base_url('assets/image/config/favicon/1696477096.png') ?>" type="image/png" sizes="16x16">
    
    <script type="text/javascript" src="<?= base_url('assets/jquery.min.js') ?>"></script>
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/bootstrap/css/bootstrap.min.css?4') ?>">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        
        html, body {
            height: 100vh;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background: white;
            color: #ffffff;
        }

        /* Container Utama Fullscreen */
        .kiosk-wrapper {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 40px 60px;
        }
        
        /* card Tengah */
        .kiosk-card {
            background: #253152;
            width: 100%;
            max-width: 580px;
            border: none;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        /* Input Gaib untuk Scanner Fisik (HID) */
        #hidden-scanner-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
            top: -100px;
        }

        /* Glow Effect Badge Jam */
        .live-clock-badge {
            background-color: #ef4444;
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.6);
            font-weight: 700;
            letter-spacing: 0.5px;
            font-size: 14px;
        }

        /* --- STATE 1: WAITING SCREEN --- */
        .badge-terminal {
            background: rgba(255, 255, 255, 0.1);
            color: #cbd5e1;
            font-size: 11px;
            letter-spacing: 1px;
            font-weight: 600;
        }
        
        .white-box-scanner {
            position: relative;
            box-shadow: inset 0 0 0 2px #3b82f6;
        }
        
        /* Bracket Siku Biru di Scanner Area */
        .white-box-scanner::before, .white-box-scanner::after {
            content: ''; 
            position: absolute; 
            width: 24px; 
            height: 24px; 
            border: 3.5px solid #3b82f6;
        }
        .white-box-scanner::before { top: 16px; left: 16px; border-right: 0; border-bottom: 0; }
        .white-box-scanner::after { bottom: 16px; right: 16px; border-left: 0; border-top: 0; }

        .scanner-status-dot {
            font-size: 12px; 
            font-weight: 700; 
            color: #3b82f6; 
            letter-spacing: 1px;
        }
        
        .pulse-dot {
            width: 9px; 
            height: 9px; 
            background: #3b82f6; 
            border-radius: 50%; 
            display: inline-block;
            animation: kioskPulse 1.5s infinite;
        }

        /* --- STATE 2: SUCCESS SCREEN (PERBESAR DI SINI) --- */
        .badge-success-terminal {
            background: rgba(34, 197, 94, 0.2);
            color: #4ade80;
            border: 2px solid rgba(34, 197, 94, 0.5);
            font-size: 16px; /* Diperbesar dari 11px */
            letter-spacing: 1.5px;
            font-weight: 700;
        }
        
        .white-box-success {
            border-top: 10px solid #22c55e; /* Dipertebal dari 6px */
        }
        
        .avatar-profile {
            width: 140px; /* Diperbesar dari 80px */
            height: 140px; /* Diperbesar dari 80px */
            border-radius: 24px;
            object-fit: cover;
            border: 4px solid #3b82f6;
        }
        
        .text-success-neon { 
            color: #22c55e; 
            font-weight: 900; 
            letter-spacing: 2px;
            font-size: 2.2rem; /* Diperbesar signifikan */
        }
        
        /* Grid Detail Karyawan di Sisi Bawah */
        .info-grid-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
        }

        /* --- ANIMASI DOT --- */
        @keyframes kioskPulse {
            0% { transform: scale(0.9); opacity: 0.6; }
            50% { transform: scale(1.25); opacity: 1; box-shadow: 0 0 10px #3b82f6; }
            100% { transform: scale(0.9); opacity: 0.6; }
        }

        /* Mengubah screen-success menjadi modal overlay yang berada di tengah */
        #screen-success:not(.d-none) {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(15, 23, 42, 0.9); /* Sedikit lebih gelap agar fokus */
            backdrop-filter: blur(10px);
            z-index: 9999;
            
            display: flex !important;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* DIUBAH: Memperbesar max-width kotak sukses agar teks besar tidak berdesakan */
        #screen-success .white-box-success {
            width: 100%;
            max-width: 650px; /* Diperbesar dari 420px */
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.5);
        }

        /* Fix html5-qrcode UI overrides */
        #reader { border: none !important; }
        #reader video { border-radius: 12px !important; object-fit: cover !important; }
        #reader__dashboard_section_csr span { color: #3b82f6 !important; }
    </style>
</head>
<body>

<div class="kiosk-wrapper">
    
    <input type="text" id="scanner-input" style="opacity: 0; position: absolute; left: -9999px;" autofocus>

    <header class="d-flex justify-content-between align-items-center w-100">
        <div class="d-flex align-items-center gap-3">
            <div class="p-2 rounded-3 d-flex align-items-center justify-content-center" style="background: white; border: 1px solid #3b4f7c; width: 50px; height: 50px;">
                <img src="<?= base_url('assets/image/config/logo/1696477096.png') ?>" alt="BPI Logo" style="width: 50px;">
            </div>
            <div>
                <h1 class="h5 m-0 fw-bold" style="letter-spacing: 1px; color: #012970 !important;">Banshu Plastic Indonesia</h1>
                <p class="m-0 text-muted" style="font-size: 11px; letter-spacing: 0.5px; color: #012970 !important;">ATTENDANCE SYSTEM</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="text-muted small fw-semibold" id="kiosk-date" style="color: #64748b !important; font-size: 14px;">24/06/2026</div>
            <div class="badge rounded-pill live-clock-badge px-3 py-2" id="kiosk-clock">05:36:05 PM</div>
        </div>
    </header>

    <main class="d-flex justify-content-center align-items-center flex-grow-1">
        <div class="card kiosk-card rounded-4 p-4 p-md-4">

            <div class="kiosk-screen-handler" id="screen-waiting">
                <span class="badge badge-terminal rounded-pill px-3 py-2 mb-3">♦ EMPLOYEE ATTENDANCE</span>
                
                 <div class="d-flex justify-content-center gap-2 mb-4 flex-nowrap" style="width: 100%; max-width: 450px; margin: 0 auto;">
                    <button id="btn-mode-scanner" class="btn btn-outline-primary btn-scanner rounded-pill fw-bold" style="flex: 1; position: relative; z-index: 9999; pointer-events: auto; cursor: pointer; white-space: nowrap; font-size: 14px; padding: 10px 10px;">Via Scanner</button>
                    <button id="btn-mode-camera" class="btn btn-primary btn-camera rounded-pill fw-bold" style="flex: 1; position: relative; z-index: 9999; pointer-events: auto; cursor: pointer; white-space: nowrap; font-size: 14px; padding: 10px 10px;">✓ Camera Active</button>
                </div>

                <div id="scanner-mode-box" class="white-box-scanner bg-white text-dark rounded-4 p-5 text-center d-none">
                    <div class="mb-5 text-primary">
                        <svg width="60" height="60" viewBox="0 0 24 24" fill="#3b82f6">
                            <path d="M3 3h8v8H3zm2 2v4h4V5zm11-2h5v5h-5zm2 2v1h1V5zm-13 11h8v8H3zm2 2v4h4v-4zm11-2h2v2h-2zm2 2h3v3h-3zm-2 3h2v2h-2zm4-3h1v1h-1zm-1-2h1v1h-1zm4 2h1v3h-1z"/></svg>
                    </div>
                    <h2 class="h5 text-dark mb-2" style="font-weight: 800; letter-spacing: 0.5px;">SCAN QR CODE FOR ATTENDANCE HERE</h2>
                    <div class="scanner-status-dot d-inline-flex align-items-center gap-2">
                        <span class="pulse-dot"></span> SCANNER ACTIVE
                    </div>
                </div>

                <div id="camera-mode-box" class="bg-white rounded-4 p-4 text-center" style="width: 100%; max-width: 400px; margin: 0 auto; box-shadow: inset 0 0 0 2px #3b82f6; position: relative;">
                    <div id="reader" style="width: 100%; min-height: 250px; overflow: hidden; border-radius: 12px; background: #f8fafc; display: flex; align-items: center; justify-content: center;"></div>
                    <div class="scanner-status-dot d-inline-flex align-items-center gap-2 mt-4">
                        <span class="pulse-dot"></span> CAMERA ACTIVE
                    </div>
                </div>
                
                <p class="text-muted small mt-4 mb-0" style="color: #94a3b8 !important;">Please scan your Employee QR Card to record attendance</p>
            </div>

            <div class="kiosk-screen-handler d-none" id="screen-success">
                <span class="badge badge-success-terminal rounded-pill px-4 py-3 mb-4">● ATTENDANCE RECORDED SUCCESSFULLY</span>
                
                <div class="white-box-success bg-white text-dark rounded-4 p-5 text-start">
                    <div class="text-center mb-4">
                        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150" alt="Profile Karyawan" class="avatar-profile mb-3">
                        <h3 class="text-success-neon m-0">SUCCESS!</h3>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom border-light">
                        <span class="text-muted fw-bold" style="font-size: 14px; color: #94a3b8 !important;">EMPLOYEE ID</span>
                        <span class="fw-black text-dark" style="font-size: 22px; font-weight: 800;" id="emp-id">XXXXXXX1</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom border-light">
                        <span class="text-muted fw-bold" style="font-size: 14px; color: #94a3b8 !important;">NAME</span>
                        <span class="fw-black text-dark" style="font-size: 24px; font-weight: 800;" id="emp-name">Alexandra</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom border-light">
                        <span class="text-muted fw-bold" style="font-size: 14px; color: #94a3b8 !important;" id="attendance-label">CHECK IN</span>
                        <span class="fw-bold text-success d-inline-flex align-items-center gap-2" style="font-size: 24px;">
                            <span style="color: #22c55e; font-size: 18px;">●</span> <span id="emp-time" style="font-weight: 800;">05:36:44 PM</span>
                        </span>
                    </div>

                    <div class="row g-3 mt-4">
                        <div class="col-6">
                            <div class="info-grid-box rounded-3 p-3 text-center">
                                <div class="fw-bold mb-1" style="font-size: 11px; color: #94a3b8; letter-spacing: 0.5px;">DEPARTMENT</div>
                                <div class="text-dark" style="font-size: 15px; font-weight: 700;" id="emp-dept">Production</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-grid-box rounded-3 p-3 text-center">
                                <div class="fw-bold mb-1" style="font-size: 11px; color: #94a3b8; letter-spacing: 0.5px;">DATE</div>
                                <div class="text-dark" style="font-size: 15px; font-weight: 700;" id="emp-date">24/06/2026</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="info-grid-box rounded-3 p-3 text-center">
                                <div class="fw-bold mb-1" style="font-size: 11px; color: #94a3b8; letter-spacing: 0.5px;">SHIFT</div>
                                <div class="text-dark" style="font-size: 15px; font-weight: 700;" id="emp-shift">Morning Shift</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <p class="text-muted mt-4 mb-0" style="color: #94a3b8 !important; font-size: 16px; font-weight: 500;">Attendance Recorded Successfully</p>
                <p class="text-secondary mb-0" style="font-size: 13px; color: #64748b !important;">Screen resets automatically</p>
            </div>

		</div>
    </main>

    <footer class="d-flex justify-content-center align-items-center gap-3 text-secondary border-0 small" style="font-size: 11px; letter-spacing: 0.5px; font-weight: 600; color: #3b4f7c !important;">
        <p>&copy; 2026 PT Banshu Plastic Indonesia. All rights reserved.</p>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        const scannerInput = $('#scanner-input');
        window.currentMode = 'camera'; // Default mode kamera
        window.html5QrCode = null;

        // 1. FUNGSI FOKUS OTOMATIS (Bebas Klik)
        function maintainFocus() {
            if (window.currentMode === 'scanner') {
                scannerInput.focus();
            }
        }

        $(window).on('focus', function() {
            maintainFocus();
        });

        scannerInput.on('blur', function() { 
            setTimeout(maintainFocus, 50); 
        });

        $(document).on('click touchstart', function(e) { 
            // Jangan jalankan maintainFocus jika yang di-klik adalah tombol atau elemen di dalam tombol
            if (!$(e.target).closest('button').length) {
                maintainFocus(); 
            }
        });


        // 2. JAM & TANGGAL REALTIME
        function updateClock() {
            const now = new Date();
            $('#kiosk-clock').text(now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true }));
            const dateStr = String(now.getDate()).padStart(2, '0') + '/' + 
                            String(now.getMonth() + 1).padStart(2, '0') + '/' + 
                            now.getFullYear();
            $('#kiosk-date, #footer-current-date').text(dateStr);
        }
        setInterval(updateClock, 1000);
        updateClock();


        // 3. PROSES SCAN SENSOR ENTER
        scannerInput.on('keypress', function(e) {
            if (e.which === 13) { 
                let qrData = $(this).val().trim();
                if (qrData.length > 0) {
                    processAbsen(qrData);
                    $(this).val(''); 
                }
            }
        });

        $('#btn-mode-camera').on('click touchstart', function(e) {
            e.preventDefault();
            e.stopPropagation();
            switchMode('camera');
        });

        $('#btn-mode-scanner').on('click touchstart', function(e) {
            e.preventDefault();
            e.stopPropagation();
            switchMode('scanner');
        });

        function showNotification(msg) {
            let notif = $('<div style="position:fixed; top:20px; right:20px; background:#3b82f6; color:white; padding:15px 25px; border-radius:8px; box-shadow:0 10px 15px rgba(0,0,0,0.2); z-index:9999; font-weight:700; transform:translateY(-20px); opacity:0; transition:all 0.3s ease;">' + msg + '</div>');
            $('body').append(notif);
            setTimeout(() => { notif.css({transform: 'translateY(0)', opacity: 1}); }, 10);
            setTimeout(() => { 
                notif.css({transform: 'translateY(-20px)', opacity: 0}); 
                setTimeout(() => notif.remove(), 300);
            }, 3000);
        }

        function switchMode(mode) {
            if (window.currentMode === mode && mode !== 'camera') return; // Jangan lakukan apa-apa jika mode sudah aktif
            window.currentMode = mode;

            if (mode === 'camera') {
                showNotification('📸 Mode Kamera Diaktifkan');
                $('#scanner-mode-box').addClass('d-none');
                $('#camera-mode-box').removeClass('d-none');
                
                $('#btn-mode-scanner').html('Via Scanner').removeClass('btn-primary').addClass('btn-outline-primary');
                $('#btn-mode-camera').html('✓ Camera Active').removeClass('btn-outline-primary').addClass('btn-primary');
                
                if (typeof Html5Qrcode === 'undefined') {
                    alert('Script scanner belum termuat sepenuhnya. Harap periksa koneksi internet atau tunggu beberapa saat.');
                    return;
                }

                if (!window.html5QrCode) {
                    window.html5QrCode = new Html5Qrcode("reader");
                }
                
                Html5Qrcode.getCameras().then(devices => {
                    if (devices && devices.length) {
                        window.html5QrCode.start(
                            { facingMode: "environment" }, 
                            {
                                fps: 10,
                                qrbox: { width: 250, height: 250 }
                            },
                            (decodedText, decodedResult) => {
                                if (window.html5QrCode.getState() === 2) { // 2 = SCANNING
                                    window.html5QrCode.pause();
                                    processAbsen(decodedText);
                                }
                            },
                            (errorMessage) => {
                                // ignore background errors
                            }
                        ).catch((err) => {
                            console.log("Error starting camera: ", err);
                            alert("Kamera tidak dapat diakses. Pastikan Anda telah memberikan izin (allow) pada browser.");
                        });
                    } else {
                        alert("Tidak ada kamera yang terdeteksi di perangkat ini.");
                    }
                }).catch(err => {
                    console.log("Error getting cameras: ", err);
                    alert("Gagal mendapatkan daftar kamera.");
                });

            } else {
                showNotification('📟 Mode Scanner Diaktifkan');
                $('#scanner-mode-box').removeClass('d-none');
                $('#camera-mode-box').addClass('d-none');
                
                $('#btn-mode-scanner').html('✓ Scanner Active').removeClass('btn-outline-primary').addClass('btn-primary');
                $('#btn-mode-camera').html('Via Camera QR').removeClass('btn-primary').addClass('btn-outline-primary');
                
                if (window.html5QrCode && window.html5QrCode.getState() === 2) {
                    window.html5QrCode.stop().then(() => {
                        maintainFocus();
                    }).catch(err => console.log(err));
                } else {
                    maintainFocus();
                }
            }
        }

        // 4. FUNGSI AJAX
        function processAbsen(data) {
            $('#status-area').html('<h4 style="color:blue;">Memproses scan...</h4>');

            $.ajax({
                url: "<?= base_url('attandance/Qrattandances/createData') ?>",
                type: "POST",
                data: { qrcode: data },
                dataType: 'json',
                success: function(response) {
                    if (response.theme === 'success') {

                        let audio = new Audio("<?= base_url('assets/audio/Success.mp3') ?>");
                        audio.play().catch(function(error) {
                            console.log("Browser memblokir autoplay audio sebelum ada interaksi:", error);
                        });

                        $('#emp-id').text(response.results.id);
                        $('#emp-name').text(response.results.name);
                        $('#emp-time').text(response.results.time);
                        $('#emp-dept').text(response.results.dept);
                        $('#emp-shift').text(response.results.shift);
                        $('#emp-date').text(response.results.date);
                        $('#attendance-label').text(response.type);


                        $('#screen-success').removeClass('d-none');

                        setTimeout(function() {
                            $('#screen-success').addClass('d-none');
                            $('#status-area').html('<h3>Place your Employee QR Card in front of the scanner</h3>');
                            if (window.currentMode === 'camera' && window.html5QrCode && window.html5QrCode.getState() === 3) { // 3 = PAUSED
                                window.html5QrCode.resume();
                            }
                        }, 4000); 

                    } else {
                        let audio = new Audio("<?= base_url('assets/audio/Try_again.mp3') ?>");
                        audio.play().catch(function(error) {
                            console.log("Browser memblokir autoplay audio sebelum ada interaksi:", error);
                        });
                        alert(response.message);
                        $('#status-area').html('<h3>Place your Employee QR Card in front of the scanner</h3>');
                        if (window.currentMode === 'camera' && window.html5QrCode && window.html5QrCode.getState() === 3) {
                            window.html5QrCode.resume();
                        }
                    }
                },
                error: function(xhr, status, error) {
                    alert("Koneksi bermasalah dengan server.");
                    $('#status-area').html('<h3>Place your Employee QR Card in front of the scanner</h3>');
                    if (window.currentMode === 'camera' && window.html5QrCode && window.html5QrCode.getState() === 3) {
                        window.html5QrCode.resume();
                    }
                }
            });
        }
        // Auto-start camera mode saat halaman dibuka
        switchMode('camera');
    });
</script>

</body>
</html>