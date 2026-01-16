<?php
// feedback.php
require_once "connect/db.php"; // gives $conn (mysqli)

// 1. quote_id validate
if (!isset($_GET['quote_id']) || !is_numeric($_GET['quote_id'])) {
    die("Invalid quotation link.");
}

$quoteId = (int) $_GET['quote_id'];

// 2. Quotation fetch karo (basic info hi)
$sql = "SELECT quotation_id, quote_number, customer_name, customer_email, final_cost, status 
        FROM solar_rooftop_quotations 
        WHERE quotation_id = $quoteId";

$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    die("Quotation not found or link expired.");
}

$q = $result->fetch_assoc();
$currentStatus = $q['status'] ?? 'sent';
// $allowed = ['approved', 'declined', 'under_review'];
if($q['status'] == 'approved'){
    header("Location: ../index");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quotation Feedback - <?= htmlspecialchars($q['quote_number']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --vk-green: #00a651;
            --vk-green-dark: #008c45;
            --vk-gray: #f5f5f5;
            --vk-text: #333333;
        }
        *{box-sizing:border-box;margin:0;padding:0;font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;}
        body{
            background:var(--vk-gray);
            color:var(--vk-text);
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:20px;
        }
        .feedback-card{
            background:#fff;
            max-width:600px;
            width:100%;
            border-radius:12px;
            padding:24px 26px;
            box-shadow:0 8px 24px rgba(0,0,0,0.08);
        }
        .feedback-header{
            margin-bottom:16px;
        }
        .feedback-header h1{
            font-size:22px;
            margin-bottom:4px;
            color:var(--vk-green-dark);
        }
        .feedback-meta{
            font-size:13px;
            color:#666;
            line-height:1.5;
        }
        .feedback-meta strong{color:#333;}
        .status-pill{
            display:inline-block;
            margin-top:6px;
            font-size:11px;
            padding:3px 10px;
            border-radius:999px;
            background:#e3f2fd;
            color:#1565c0;
        }
        .feedback-body{
            margin-top:18px;
        }
        .feedback-body p{
            margin-bottom:10px;
            font-size:14px;
        }
        form{
            margin-top:10px;
        }
        .status-options{
            display:flex;
            flex-direction:column;
            gap:8px;
            margin:12px 0 16px;
        }
        .status-option{
            padding:10px 12px;
            border-radius:8px;
            border:1px solid #ddd;
            display:flex;
            align-items:center;
            gap:10px;
            cursor:pointer;
            transition:0.2s;
            background:#fafafa;
        }
        .status-option:hover{
            border-color:var(--vk-green);
            box-shadow:0 2px 6px rgba(0,0,0,0.06);
        }
        .status-option input{width:18px;height:18px;}
        .status-title{font-weight:600;font-size:14px;}
        .status-desc{font-size:12px;color:#666;}
        textarea{
            width:100%;
            min-height:80px;
            border-radius:8px;
            border:1px solid #ddd;
            padding:10px 12px;
            font-size:14px;
            resize:vertical;
        }
        textarea:focus{
            outline:none;
            border-color:var(--vk-green);
            box-shadow:0 0 0 2px rgba(0,166,81,0.1);
        }
        .btn-submit{
            margin-top:14px;
            width:100%;
            border:none;
            border-radius:999px;
            padding:12px 16px;
            font-size:15px;
            font-weight:600;
            background:linear-gradient(to right, var(--vk-green), var(--vk-green-dark));
            color:#fff;
            cursor:pointer;
            transition:0.2s;
        }
        .btn-submit:hover{
            transform:translateY(-1px);
            box-shadow:0 6px 16px rgba(0,0,0,0.16);
        }
        .small-note{
            margin-top:8px;
            font-size:11px;
            color:#777;
            text-align:center;
        }
        @media (max-width:480px){
            .feedback-card{padding:18px 16px;}
            .feedback-header h1{font-size:18px;}
        }
    </style>
</head>
<body>
    <div class="feedback-card">
        <div class="feedback-header">
            <h1>Quotation Response</h1>
            <div class="feedback-meta">
                Quote No: <strong><?= htmlspecialchars($q['quote_number']); ?></strong><br>
                Customer: <strong><?= htmlspecialchars($q['customer_name']); ?></strong><br>
                Estimated Amount: <strong>₹<?= number_format((float)$q['final_cost'], 2); ?></strong><br>
                <span class="status-pill">
                    Current Status: <?= ucfirst(str_replace('_',' ', $currentStatus)); ?>
                </span>
            </div>
        </div>

        <div class="feedback-body">
            <p>
                Kindly select your response for this quotation and optionally share any remarks.
            </p>

            <form method="POST" action="feedback_api">
                <input type="hidden" name="quotation_id" value="<?= (int)$q['quotation_id']; ?>">

                <div class="status-options">
                    <label class="status-option">
                        <input type="radio" name="status" value="approved" required>
                        <div>
                            <div class="status-title">✅ I Accept this Quotation</div>
                            <div class="status-desc">You agree with the proposal and wish to proceed.</div>
                        </div>
                    </label>

                    <label class="status-option">
                        <input type="radio" name="status" value="under_review">
                        <div>
                            <div class="status-title">🤔 Under Consideration</div>
                            <div class="status-desc">You need more time or internal discussion before final decision.</div>
                        </div>
                    </label>

                    <label class="status-option">
                        <input type="radio" name="status" value="declined">
                        <div>
                            <div class="status-title">❌ I Do Not Wish to Proceed</div>
                            <div class="status-desc">You are declining this quotation at this moment.</div>
                        </div>
                    </label>
                </div>

                <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">
                    Remarks (optional)
                </label>
                <textarea name="remarks" placeholder="You may mention any specific reasons, requirements or conditions..."></textarea>

                <button type="submit" class="btn-submit">
                    Submit My Response
                </button>

                <div class="small-note">
                    Your response will be recorded against this quotation in VK Solar's system.
                </div>
            </form>
        </div>
    </div>
</body>
</html>
