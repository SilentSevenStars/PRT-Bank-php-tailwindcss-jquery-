<?php
  session_start();

  if(isset($_SESSION['user_id']))
    header("Location: index.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Management - Login</title>
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <script type="text/javascript" src="assets/js/tailwind.js"></script>
    <script type="text/javascript" src="assets/js/jquery.min.js"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100 font-sans">
    <div class="flex bg-white rounded-2xl shadow-2xl overflow-hidden max-w-5xl w-full h-[600px]">

    <div class="hidden md:flex w-1/2">
      <img src="assets/image/logo.png" alt="Bank Background" class="w-full h-full object-cover"/>
    </div>

    <div class="flex flex-col justify-center items-center w-full md:w-1/2 p-8 text-center">
      <img src="assets/image/logo.png" alt="Bank Logo" class="w-40 mb-4"/>
      <h2 class="text-xl font-semibold text-blue-900 mb-6">WELCOME BACK</h2>

      <form method="POST" action="config/request.php" class="w-full max-w-xs space-y-4">
        <input type="hidden" name="action" value="login">

        <div class="relative">
          <input type="text" name="username" placeholder="Username" required
                 class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"/>
          <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
            <i class="fas fa-user"></i>
          </span>
        </div>

        <div class="relative">
          <input type="password" name="password" id="password" placeholder="Password" required
                 class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"/>
          <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 cursor-pointer" onclick="togglePassword()">
            <i id="eye-icon" class="fas fa-eye"></i>
          </span>
        </div>

        <input type="submit" name="login" value="Login" class="w-full bg-blue-900 text-white py-3 rounded-lg shadow-md hover:bg-blue-800 transition">

        <div class="flex justify-between mt-2 text-sm">
          <a href="forget-password.php" class="text-blue-600 hover:underline">Forgot Password?</a>
          <a href="register.php" class="text-blue-600 hover:underline">Register</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    function togglePassword() {
      const input = document.getElementById('password');
      const icon = document.getElementById('eye-icon');
      input.type = input.type === "password" ? "text" : "password";
      icon.classList.toggle("fa-eye-slash");
      icon.classList.toggle("fa-eye");
    }
  </script>
</body>
</html>