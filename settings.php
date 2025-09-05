<?php
session_start();

if (!isset($_SESSION['user_id']))
    header("Location: login.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - PRT Bank</title>
    <script type="text/javascript" src="assets/js/tailwind.js"></script>
    <script type="text/javascript" src="assets/js/jquery.min.js"></script>
</head>

<body class="bg-gray-100 font-sans">
    <div class="flex h-screen">
        <?php include 'layout/sidebar.php'; ?>

        <div class="flex-1 p-6 overflow-y-auto bg-white text-gray-900">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Account Settings</h2>

            <div class="bg-white rounded-xl shadow-lg p-6 border max-w-lg">
                <form method="POST" id="updateProfileForm">
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-1" for="username">Username</label>
                        <input type="text" id="username" name="username"
                            class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-1" for="email">Email</label>
                        <input type="email" id="email" name="email"
                            class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-1" for="password">New Password (leave blank to keep current)</label>
                        <input type="password" id="password" name="password"
                            class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <input type="hidden" name="id" value="<?= $_SESSION['user_id'] ?>">
                    <input type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-500" value="Save Changes" name="updateProfile">
                </form>
            </div>
        </div>
    </div>

   <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-96 text-center">
            <h2 class="text-xl font-bold text-green-600 mb-4">✅ Setting Updated</h2>
            <p class="text-gray-700 mb-6">Your setting has been updated successfully!</p>
            <button id="closeModal" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-500">
                OK
            </button>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            loadInfo(<?= $_SESSION['user_id'] ?>)
        })

        function loadInfo(user_id) {
            $.ajax({
                url: "config/request.php",
                method: "POST",
                data: {
                    'get_profile': true,
                    'userId': user_id,
                },
                success: function(result) {
                    let datas = JSON.parse(result)
                    if (datas.length > 0) {
                        datas.forEach(function(data) {
                            $('#username').val(data['username'])
                            $('#email').val(data['email'])
                        })
                    }
                }
            })
        }

        $('#updateProfileForm').on('submit', function(e) {
            e.preventDefault()
            var datas = $(this).serializeArray()
            var data_array = {}
            $.map(datas, function(data) {
                data_array[data['name']] = data['value']
            })
            console.log(data_array)
            $.ajax({
                url: "config/request.php",
                method: "POST",
                data: {
                    'update_profile': true,
                    ...data_array
                },
                success: function() {
                    $("#successModal").removeClass("hidden")
                    loadInfo()
                },
                error: function() {
                    alert("Something went wrong")
                }
            })
        })
        $("#closeModal").on("click", function() {
            $("#successModal").addClass("hidden")
        })
    </script>
</body>

</html>