<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .email-container {
            background-color: #ffffff;
            margin: 20px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #EEAF2F;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }

        .content {
            color: black;
            margin: 20px;
            font-size: 16px;
            line-height: 1.6;
        }

        .content p {
            margin: 10px 0;
        }

        .button-container {
            text-align: center;
            margin: 20px 0;
        }

        .button-container a {
            color: white
        }

        .button {
            background-color: #EEAF2F;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777777;
            text-align: center;
        }

        .image {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 10px;
        }

        .image>img {
            width: 300px;
            height: 200px;
            border-radius: 10px;
            margin: auto;
        }

        .content .bottom-content {
            display: flex;
            justify-content: center;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <h2>Password Reset Request</h2>
        </div>
        <div class="image">
            <img src="{{ asset('storage/assets/logo_cropped.png') }}" alt="logo">
        </div>
        <div class="content">
            <p>Dear {{ $email }},</p>
            <p>We received a request to reset your password. Please use the token below to reset your password.</p>
            <div class="button-container">
                <a href="{{ env('FRONTEND_URL') }}reset-password?token={{ $token }}&email={{ $email }}"
                    class="button">Reset Password</a>
            </div>
            <p style="bottom-content">If you did not request a password reset,
                please ignore this email.</p>
        </div>
        <div class="footer">
            <p>If you have any questions, feel free to contact our support team.</p>
            <p>&copy; {{ date('Y') }} Let Design. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
