<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PAMSIMAS - Lupa Username & Password</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #6571FF 0%, #4c63d2 50%, #8b94ff 100%);
            min-height: 100vh;
            display: flex;
            position: relative;
        }

        /* Desktop Layout */
        .main-container {
            display: flex;
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(101, 113, 255, 0.3);
            margin: 40px 20px;
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Left Panel - Info Section */
        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, #6571FF 0%, #4c63d2 100%);
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(5deg);
            }
        }

        .brand-section {
            position: relative;
            z-index: 1;
        }

        .logo-container {
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(20px);
            border: 3px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 30px;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .logo-icon {
            font-size: 50px;
            color: white;
        }

        .brand-section h1 {
            color: white;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .brand-section .subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 40px;
        }

        .feature-list {
            list-style: none;
            margin-top: 20px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            animation: fadeInLeft 0.6s ease-out;
            animation-fill-mode: both;
        }

        .feature-item:nth-child(1) {
            animation-delay: 0.3s;
        }

        .feature-item:nth-child(2) {
            animation-delay: 0.4s;
        }

        .feature-item:nth-child(3) {
            animation-delay: 0.5s;
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .feature-icon {
            font-size: 20px;
            margin-right: 15px;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px;
            border-radius: 8px;
            flex-shrink: 0;
        }

        /* Right Panel - Form Section */
        .right-panel {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .back-btn {
            position: absolute;
            top: 30px;
            left: 30px;
            background: #f3f4f6;
            border: none;
            color: #6571FF;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            font-size: 18px;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .back-btn:hover {
            background: #6571FF;
            color: white;
            transform: scale(1.1);
        }

        .form-section {
            max-width: 400px;
            margin: 0 auto;
            width: 100%;
        }

        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .form-header h2 {
            color: #1f2937;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .form-header p {
            color: #6b7280;
            font-size: 16px;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 28px;
        }

        .form-group label {
            display: block;
            color: #374151;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 15px;
        }

        .input-container {
            position: relative;
        }

        .input-container input {
            width: 100%;
            padding: 18px 55px 18px 18px;
            border: 2px solid #e5e7eb;
            border-radius: 14px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f9fafb;
        }

        .input-container input:focus {
            outline: none;
            border-color: #6571FF;
            background: white;
            box-shadow: 0 0 0 4px rgba(101, 113, 255, 0.1);
            transform: translateY(-2px);
        }

        .input-icon {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #6571FF;
            font-size: 22px;
        }

        .info-boxes {
            margin-bottom: 20px;
        }

        .info-box {
            background: linear-gradient(135deg, #f8faff, #f1f5ff);
            border: 1px solid #e0e7ff;
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            animation: fadeIn 0.6s ease-out both;
            transition: all 0.3s ease;
        }

        .info-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(101, 113, 255, 0.1);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .info-box:nth-child(2) {
            animation-delay: 0.1s;
        }

        .info-icon {
            color: #6571FF;
            font-size: 22px;
            margin-right: 14px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .info-text {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
            flex: 1;
        }

        .send-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #6571FF, #4c63d2);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 28px;
            position: relative;
            overflow: hidden;
        }

        .send-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .send-btn:hover::before {
            left: 100%;
        }

        .send-btn:hover {
            background: linear-gradient(135deg, #4c63d2, #3b52cc);
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(101, 113, 255, 0.4);
        }

        .send-btn:active {
            transform: translateY(-1px);
        }

        .send-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            background: #9ca3af;
        }

        .success-message,
        .error-message {
            padding: 18px;
            border-radius: 14px;
            margin-bottom: 20px;
            display: none;
            align-items: center;
            gap: 14px;
            animation: slideDown 0.5s ease-out;
        }

        .success-message {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border: 1px solid #86efac;
            color: #166534;
        }

        .error-message {
            background: linear-gradient(135deg, #fef2f2, #fecaca);
            border: 1px solid #f87171;
            color: #dc2626;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message-icon {
            font-size: 22px;
            flex-shrink: 0;
        }

        .back-to-login {
            text-align: center;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e5e7eb;
        }

        .back-to-login a {
            color: #6571FF;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 15px;
        }

        .back-to-login a:hover {
            color: #4c63d2;
            text-decoration: underline;
        }

        /* Tablet Styles */
        @media (max-width: 1024px) {
            .main-container {
                margin: 20px;
                border-radius: 20px;
            }

            .left-panel,
            .right-panel {
                padding: 40px 30px;
            }

            .brand-section h1 {
                font-size: 28px;
            }

            .form-header h2 {
                font-size: 24px;
            }
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            body {
                padding: 0;
            }

            .main-container {
                flex-direction: column;
                margin: 0;
                border-radius: 0;
                min-height: 100vh;
            }

            .left-panel {
                padding: 40px 30px 30px;
                text-align: center;
            }

            .logo-container {
                width: 80px;
                height: 80px;
                margin: 0 auto 20px;
            }

            .logo-icon {
                font-size: 36px;
            }

            .brand-section h1 {
                font-size: 24px;
            }

            .brand-section .subtitle {
                font-size: 16px;
                margin-bottom: 20px;
            }

            .feature-list {
                display: none;
            }

            .right-panel {
                padding: 30px 20px;
            }

            .back-btn {
                top: 20px;
                left: 20px;
                width: 40px;
                height: 40px;
                font-size: 16px;
            }

            .form-section {
                max-width: none;
            }

            .form-header h2 {
                font-size: 22px;
            }

            .form-header p {
                font-size: 15px;
            }
        }

        /* Small Mobile Styles */
        @media (max-width: 480px) {
            .left-panel {
                padding: 30px 20px 20px;
            }

            .right-panel {
                padding: 20px 15px;
            }

            .brand-section h1 {
                font-size: 20px;
            }

            .form-header h2 {
                font-size: 20px;
            }

            .input-container input {
                padding: 16px 50px 16px 16px;
            }

            .send-btn {
                padding: 16px;
            }
        }
    </style>
</head>

<body>
    <div class="main-container">
        <!-- Left Panel - Brand/Info Section -->
        <div class="left-panel">
            <div class="brand-section">
                <div class="logo-container">
                    <span class="logo-icon">🔐</span>
                </div>
                <h1>KPSPAMS DS.TENGGERLOR</h1>
                <p class="subtitle">Sistem Manajemen Air Minum & Sanitasi</p>

                <ul class="feature-list">
                    <li class="feature-item">
                        <span class="feature-icon">🔒</span>
                        <span>Keamanan data terjamin</span>
                    </li>
                    <li class="feature-item">
                        <span class="feature-icon">📱</span>
                        <span>Notifikasi via WhatsApp</span>
                    </li>
                    <li class="feature-item">
                        <span class="feature-icon">⚡</span>
                        <span>Reset password instan</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Right Panel - Form Section -->
        <div class="right-panel">
            <button class="back-btn" onclick="goBack()">←</button>
            
            <div class="form-section">
                <div class="form-header">
                    <h2>Lupa Username/Password?</h2>
                    <p>Masukkan nomor WhatsApp untuk mendapatkan username dan password baru</p>
                </div>

                <div id="successMessage" class="success-message">
                    <span class="message-icon">✅</span>
                    <span id="successText"></span>
                </div>
                <div id="errorMessage" class="error-message">
                    <span class="message-icon">❌</span>
                    <span id="errorText"></span>
                </div>

                <form action="{{ url('/api/auth/forgot-password') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="phoneNumber">Nomor WhatsApp</label>
                        <div class="input-container">
                            <input type="tel" id="phoneNumber" name="no_hp" placeholder="Contoh: 08123456789"
                                maxlength="15" required>
                            <span class="input-icon">📱</span>
                        </div>
                    </div>

                    <div class="info-boxes">
                        <div class="info-box">
                            <span class="info-icon">💬</span>
                            <span class="info-text">Username dan password baru akan dikirim melalui WhatsApp Anda</span>
                        </div>

                        <div class="info-box">
                            <span class="info-icon">ℹ️</span>
                            <span class="info-text">Jika nomor belum terdaftar atau ingin mengganti username, silakan
                                datang ke kantor Pamsimas</span>
                        </div>
                    </div>

                    <button type="submit" class="send-btn">
                        📤 Kirim Sekarang
                    </button>
                </form>

                <div class="back-to-login">
                    <a href="javascript:goBack()">← Kembali ke Halaman Login</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Basic input formatting - only numbers
        document.getElementById('phoneNumber').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            e.target.value = value;
        });

        // Simple back function
        function goBack() {
            window.history.back();
        }

        // Auto focus on phone input when page loads
        window.addEventListener('load', function () {
            document.getElementById('phoneNumber').focus();
        });
    </script>
</body>

</html>