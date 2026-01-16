<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Thank You - Quotation Response</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --vk-green: #00a651;
            --vk-green-dark: #008c45;
            --vk-gray: #f5f5f5;
        }
        *{box-sizing:border-box;margin:0;padding:0;font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;}
        body{
            background:var(--vk-gray);
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:20px;
        }
        .thankyou-card{
            background:#fff;
            max-width:480px;
            width:100%;
            text-align:center;
            border-radius:12px;
            padding:40px 30px;
            box-shadow:0 8px 24px rgba(0,0,0,0.08);
            position: relative;
        }
        
        .thankyou-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(to right, var(--vk-green), var(--vk-green-dark));
            border-radius: 12px 12px 0 0;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: var(--vk-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 36px;
            box-shadow: 0 4px 12px rgba(0,166,81,0.3);
        }
        
        h1{
            font-size:28px;
            margin-bottom:12px;
            color:var(--vk-green-dark);
        }
        p{
            font-size:16px;
            color:#444;
            margin-bottom:10px;
            line-height:1.5;
        }
        
        .status-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid var(--vk-green);
        }
        
        .status-box h3 {
            color: var(--vk-green-dark);
            margin-bottom: 5px;
            font-size: 18px;
        }

        .goto-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: 25px;
            background: linear-gradient(to right, var(--vk-green), var(--vk-green-dark));
            color: #fff;
            padding: 16px 32px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            box-shadow: 0 6px 20px rgba(0,166,81,0.35);
            transition: all 0.3s ease;
            gap: 10px;
            border: none;
            cursor: pointer;
        }

        .goto-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,166,81,0.45);
        }

        .goto-btn:active {
            transform: translateY(-1px);
        }

        .goto-btn i {
            transition: transform 0.3s ease;
        }

        .goto-btn:hover i {
            transform: translateX(5px);
        }

        .contact-info {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 14px;
            color: #666;
        }

        @media (max-width: 480px) {
            .thankyou-card {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .success-icon {
                width: 60px;
                height: 60px;
                font-size: 28px;
            }
            
            .goto-btn {
                padding: 14px 28px;
                font-size: 16px;
            }
        }

        @media print {
            .goto-btn {
                background: var(--vk-green) !important;
                color: #fff !important;
                box-shadow: none !important;
                border: 1px solid var(--vk-green) !important;
            }
        }
    </style>
</head>
<body>
    <div class="thankyou-card">
        <!-- Success Icon -->
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        
        <h1>Thank You for Your Response</h1>
        <p>Your selection has been recorded successfully.</p>
        
        <!-- Status Information Box -->
        <div class="status-box">
            <h3>Quotation Status Updated</h3>
            <p>Your feedback has been processed and our team will contact you shortly.</p>
        </div>
        
        <p>We appreciate your time and consideration in providing your feedback.</p>
        <p>You may close this window now or return to our website.</p>

        <!-- Enhanced Button -->
        <a href="https://vksolarenergy.com" class="goto-btn">
            Go to Website <i class="fas fa-arrow-right"></i>
        </a>
        
        <!-- Contact Information -->
        <div class="contact-info">
            <p>Need assistance? Contact us at <strong>info@vksolarenergy.com</strong></p>
        </div>
    </div>

    <script>
        // Add subtle entrance animation
        document.addEventListener('DOMContentLoaded', function() {
            const card = document.querySelector('.thankyou-card');
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>