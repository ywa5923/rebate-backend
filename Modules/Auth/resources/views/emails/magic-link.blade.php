<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FXREBATE - Magic Link Authentication</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .content {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .footer {
            margin-top: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            font-size: 14px;
            color: #6c757d;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Broker Authentication</h1>
    </div>

    <div class="content">
        <h2>
            @switch($action)
                @case('login')
                    Login to Your Broker Account
                    @break
                @case('registration')
                    Complete Your Broker Registration
                    @break
                @case('password_reset')
                    Reset Your Password
                    @break
                @default
                    Access Your Account
            @endswitch
        </h2>

        <p>Hello {{ $subject->name ?? 'FXREBATE User' }},</p>

        <p>
            @switch($action)
                @case('login')
                    Click the button below to securely log in to your broker account:
                    @break
                @case('registration')
                    Click the button below to complete your broker registration:
                    @break
                @case('password_reset')
                    Click the button below to reset your password:
                    @break
                @default
                    Click the button below to access your account:
            @endswitch
        </p>

        <div style="text-align: center;">
            <a href="{{ $magicLinkUrl }}" class="button">
                @switch($action)
                    @case('login')
                        Login Now
                        @break
                    @case('registration')
                        Complete Registration
                        @break
                    @case('password_reset')
                        Reset Password
                        @break
                    @default
                        Access Account
                @endswitch
            </a>
        </div>

        <div class="warning">
            <strong>Security Notice:</strong>
            <ul>
                <li>This link will expire on {{ $expiresAt->format('M d, Y \a\t g:i A') }}</li>
                <li>This link can only be used once</li>
                <li>If you didn't request this, please ignore this email</li>
            </ul>
        </div>

        <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
        <p style="word-break: break-all; background-color: #f8f9fa; padding: 10px; border-radius: 4px;">
            {{ $magicLinkUrl }}
        </p>
    </div>

    <div class="footer">
        <p>This email was sent to you because a magic link was requested for your broker account.</p>
        <p>If you have any questions, please contact our support team.</p>
        <p>&copy; {{ date('Y') }} Your Company Name. All rights reserved.</p>
    </div>
</body>
</html>
