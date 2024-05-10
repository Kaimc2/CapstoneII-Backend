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
        <form id="verifyForm" action="http://127.0.0.1:8000/api/auth/verify_email" method="POST">
            <input type="hidden" name="email" value="{{ $email }}">
            <input type="hidden" name="password" value="{{ $password }}">
            <input type="hidden" name="name" value="{{ $name }}">
            <button type="submit" class="button">Verify Email</button>
        </form>
    </div>
    <p style="text-align: center">If you did not create an account, no further action is required.</p>
    <div class="footer">
        <p>&copy; {{ date('Y') }} Your Clothes. All rights reserved.</p>
    </div>
</div>

<script>
    document.getElementById('verifyForm').addEventListener('submit', function(event) {
        event.preventDefault();

        var form = event.target;
        var xhr = new XMLHttpRequest();
        xhr.open(form.method, form.action, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('email', form.email.value);
        xhr.setRequestHeader('password', form.password.value);
        xhr.setRequestHeader('name', form.name.value);

        var token = 'some_random_token';
        xhr.send('token=' + encodeURIComponent(token));
    });
</script>
</body>
</html>
