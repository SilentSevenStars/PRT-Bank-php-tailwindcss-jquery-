<?php
session_start();
$email = $_GET['email'];

if(!isset($_GET['email']))
    header("Location: login.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Link - PTR Bank</title>
    <script type="text/javascript" src="assets/js/tailwind.js"></script>
    <script type="text/javascript" src="assets/js/jquery.min.js"></script>
</head>

<body class="bg-gray-100 font-sans min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md mx-4">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Password Reset Link</h1>
            <div class="w-full h-1 bg-blue-900 rounded-full mb-6"></div>
        </div>

        <div class="flex justify-center mb-6">
            <div class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
        </div>

        <div class="text-center mb-6">
            <p class="text-gray-700 text-lg leading-relaxed">
                We received your request to reset the password<br>
                for your account
            </p>
        </div>

        <div class="text-center mb-6 bg-gray-50 p-4 rounded-lg">
            <p class="text-sm text-gray-600">Reset link sent to:</p>
            <p class="text-blue-600 font-medium"><?= $email ?></p>
        </div>

        <div class="text-center mb-6">
            <p class="text-blue-600 font-medium">
                Please check your inbox to reset your password
            </p>
        </div>

        <div class="text-center">
            <a href="login.php"
                    class="bg-blue-900 hover:bg-blue-700 text-white font-medium py-3 px-8 rounded-full transition duration-300 ease-in-out transform hover:scale-105">
                Log In To Your Account
            </a>
        </div>
</body>
</html>
