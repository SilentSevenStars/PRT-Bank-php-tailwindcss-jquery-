<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Token Invalid or Expired - PTR Bank</title>
    <script type="text/javascript" src="assets/js/tailwind.js"></script>
    <script type="text/javascript" src="assets/js/jquery.min.js"></script>
</head>

<body class="bg-gray-100 font-sans min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md mx-4">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Token Invalid or Expired</h1>
            <div class="w-full h-1 bg-blue-900 rounded-full mb-6"></div>
        </div>

        <div class="flex justify-center mb-6">
            <div class="w-20 h-20 bg-red-500 rounded-full flex items-center justify-center">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
        </div>

        <div class="text-center mb-6">
            <p class="text-gray-700 text-lg leading-relaxed">
                This verification link is no longer valid<br>
                or expired.
            </p>
        </div>

        <div class="text-center mb-6">
            <p class="text-blue-600 font-medium">
                Please restart the process or login
            </p>
        </div>

        <div class="text-center">
            <button onclick="redirectToLogin()"
                class="bg-blue-900 hover:bg-blue-700 text-white font-medium py-3 px-8 rounded-full transition duration-300 ease-in-out transform hover:scale-105">
                Log In To Your Account
            </button>
        </div>
    </div>

</body>

</html>