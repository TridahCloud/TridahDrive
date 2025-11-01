<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to TridahDrive</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #31d8b2;
        }
        .logo {
            font-size: 32px;
            font-weight: bold;
            background: linear-gradient(135deg, #204e7e 0%, #31d8b2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }
        .greeting {
            font-size: 24px;
            color: #204e7e;
            margin-bottom: 20px;
        }
        .content {
            color: #555;
            margin-bottom: 30px;
        }
        .features {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .feature-item {
            margin: 10px 0;
            padding-left: 25px;
            position: relative;
        }
        .feature-item:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #31d8b2;
            font-weight: bold;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #204e7e 0%, #31d8b2 100%);
            color: #ffffff;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .cta-button:hover {
            opacity: 0.9;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            color: #999;
            font-size: 14px;
        }
        .footer a {
            color: #31d8b2;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">TridahDrive</div>
            <div class="greeting">Welcome, {{ $user->name }}!</div>
        </div>

        <div class="content">
            <p>Thank you for joining TridahDrive! We're excited to have you on board.</p>
            
            <p>TridahDrive is your all-in-one business management platform, combining three powerful applications:</p>

            <div class="features">
                <div class="feature-item">
                    <strong>Invoicer</strong> - Create professional invoices, manage clients, and track payments
                </div>
                <div class="feature-item">
                    <strong>BookKeeper</strong> - Complete accounting solution with transaction tracking and tax reports
                </div>
                <div class="feature-item">
                    <strong>Project Board</strong> - Manage projects with kanban boards, tasks, and team collaboration
                </div>
            </div>

            <p>All your work is organized within customizable drives - create personal drives for yourself or shared drives for your team.</p>

            <div style="text-align: center;">
                <a href="{{ route('dashboard') }}" class="cta-button">Get Started</a>
            </div>

            <p>If you have any questions or need help getting started, feel free to reach out to our support team or visit our <a href="https://github.com/TridahCloud/TridahDrive" style="color: #31d8b2;">GitHub repository</a>.</p>

            <p>Happy organizing!</p>
            <p style="margin-top: 20px;">
                <strong>The TridahDrive Team</strong>
            </p>
        </div>

        <div class="footer">
            <p>This is an automated message from TridahDrive.</p>
            <p>
                <a href="{{ route('dashboard') }}">Visit Dashboard</a> | 
                <a href="https://github.com/TridahCloud/TridahDrive">GitHub</a>
            </p>
        </div>
    </div>
</body>
</html>

