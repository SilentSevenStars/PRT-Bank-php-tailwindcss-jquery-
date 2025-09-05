<?php
  require_once "config/class/Auth.php";
  session_start();

  $auth = new Auth;

  if(isset($_SESSION['user_id']))
    header("Location: index.php");

  if(!isset($_GET['token']))
    header("Location: index.php");

  $token_hashed = hash("sha256", $_GET['token']);

  $auth->checkToken($token_hashed);

  if($auth->res->num_rows <= 0){
    header("Location: invalid.php");
  }

  $data = $auth->res->fetch_assoc();

  if(strtotime($data["reset_expires"]) <= time()){
    header("Location: invalid.php");
  }


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Management - Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <script type="text/javascript" src="assets/js/tailwind.js"></script>
    <script type="text/javascript" src="assets/js/jquery.min.js"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100 font-sans">
    <div class="flex bg-white rounded-2xl shadow-2xl overflow-hidden max-w-5xl w-full h-[600px]">

    <div class="hidden md:flex w-1/2">
      <img src="assets/image/logo.png" alt="Bank Background" class="w-full h-full object-cover">
    </div>

    <div class="flex flex-col justify-center items-center w-full md:w-1/2 p-8 text-center">
      <img src="assets/image/logo.png" alt="Bank Logo" class="w-20 mb-4">
      <h2 class="text-xl font-semibold text-blue-900 mb-6">CREATE YOUR ACCOUNT</h2>

      <form method="POST" action="config/request.php" class="w-full max-w-xs space-y-4">


        <div class="relative">
          <input type="password" name="password" id="password" placeholder="New Password" required
                 class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"/>
          <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 cursor-pointer" onclick="togglePassword()">
            <i id="eye-icon" class="fas fa-eye"></i>
          </span>
        </div>

        <div class="relative">
          <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required
                 class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"/>
          <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 cursor-pointer" onclick="toggleConfirmPassword()">
            <i id="eye-icon-confirm" class="fas fa-eye"></i>
          </span>
        </div>

        <input type="hidden" name="id" id="id" value="<?= $data['id'] ?>">

        <input type="submit" value="Register" name="reset-password"  class="w-full bg-blue-900 text-white py-3 rounded-lg shadow-md hover:bg-blue-800 transition">

        <p class="text-sm text-gray-600 mt-2">
          Already have an account? 
          <a href="login.php" class="text-blue-600 hover:underline">Login</a>
        </p>
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

    function toggleConfirmPassword() {
      const input = document.getElementById('confirm_password');
      const icon = document.getElementById('eye-icon-confirm');
      input.type = input.type === "password" ? "text" : "password";
      icon.classList.toggle("fa-eye-slash");
      icon.classList.toggle("fa-eye");
    }
  </script>
</body>
</html>