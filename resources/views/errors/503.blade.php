<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Under Maintenance</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .maintenance-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 60px 40px;
            text-align: center;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .maintenance-icon {
            font-size: 80px;
            margin-bottom: 30px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        h1 {
            color: #2d3748;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .message {
            color: #4a5568;
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .info-box {
            background: #f7fafc;
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: left;
        }

        .info-box p {
            color: #2d3748;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .info-box p:last-child {
            margin-bottom: 0;
        }

        .info-box strong {
            color: #667eea;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 30px;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            animation: progress 2s ease-in-out infinite;
        }

        @keyframes progress {
            0% {
                width: 0%;
            }
            50% {
                width: 70%;
            }
            100% {
                width: 100%;
            }
        }

        .contact-info {
            color: #718096;
            font-size: 14px;
        }

        .contact-info a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .contact-info a:hover {
            text-decoration: underline;
        }

        .social-links {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: #f7fafc;
            border-radius: 50%;
            color: #667eea;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background: #667eea;
            color: white;
            transform: translateY(-3px);
        }

        @media (max-width: 600px) {
            .maintenance-container {
                padding: 40px 30px;
            }

            h1 {
                font-size: 26px;
            }

            .message {
                font-size: 16px;
            }

            .maintenance-icon {
                font-size: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-icon">🚧</div>
        
        <h1>We'll Be Back Soon!</h1>
        
        <p class="message">
            {{ $exception->getMessage() ?: 'Our website is currently undergoing scheduled maintenance. We apologize for any inconvenience.' }}
        </p>

        <div class="progress-bar">
            <div class="progress-bar-fill"></div>
        </div>

        <div class="info-box">
            <p><strong>What's happening?</strong></p>
            <p>We're making improvements to serve you better. Our team is working hard to get everything back up and running.</p>
            <p style="margin-top: 15px;"><strong>When will we be back?</strong></p>
            <p>We expect to be back online shortly. Please check back in a few minutes.</p>
        </div>

        <div class="contact-info">
            <p>Need immediate assistance?</p>
            <p>Contact us at <a href="mailto:support@example.com">support@example.com</a></p>
        </div>

        <div class="social-links">
            <a href="#" title="Twitter">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"></path>
                </svg>
            </a>
            <a href="#" title="Facebook">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"></path>
                </svg>
            </a>
            <a href="#" title="Email">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
            </a>
        </div>
    </div>

    <script>
        // Auto-refresh every 60 seconds
        setTimeout(function() {
            location.reload();
        }, 60000);
    </script>
</body>
</html>
