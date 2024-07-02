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
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }

        .content {
            margin: 20px 0;
            line-height: 1.6;
        }

        .content p {
            margin: 10px 0;
        }

        .button-container {
            text-align: center;
            margin: 20px 0;
        }

        .button {
            background-color: #4CAF50;
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

        .image>div>img {
            width: 300px;
            height: 200px;
            border-radius: 10px;
            margin: auto;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <h2>Password Reset Request</h2>
        </div>
        <div class="image">
            <div>
                <img src="https://content.imageresizer.com/images/memes/2-gay-black-mens-kissing-thumbnail-url-1hnv2v.jpg"
                    alt="" width="30" height="30">
            </div>
        </div>
        <div class="content">
            <p>Dear {{ $email }},</p>
            <p>We received a request to reset your password. Please use the token below to reset your password.</p>
            <div class="button-container">
                <a href="http://127.0.0.1:8000/api/auth/reset_password?token={{ $token }}&email={{ $email }}"
                    class="button">Reset Password</a>
            </div>
            <p>If you did not request a password reset, please ignore this email.</p>
        </div>
        <div class="footer">
            <p>If you have any questions, feel free to contact our support team.</p>
            <p>&copy; {{ date('Y') }} Let Design. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
