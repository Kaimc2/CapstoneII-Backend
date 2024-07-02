<!DOCTYPE html>
<html>

<head>
    <title>Verify Your Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            text-align: center;
        }

        p {
            font-size: 16px;
        }

        .button-container {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #ffffff;
            background-color: #007bff;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            margin: auto;
            border: none;
            cursor: pointer;
        }

        .button:hover {
            background-color: #0056b3;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Verify Your Email Address</h1>
        <p>Thank you for registering! Please click the button below to verify your email address:</p>
        <div class="button-container">
            <a href="{{ url(`/api/auth/email/verify/$id/$hash`) }}" class="button">Verify Email</a>
        </div>
        <p style="text-align: center">If You did not create an account, no further action is required.</p>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Let Design. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
