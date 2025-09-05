<aside class="w-64 bg-gray-900 text-white flex flex-col justify-between p-5 min-h-screen">
  <div>
    <a href="index.php"><img src="assets/image/sidebarLogo.png" alt=""></a>
    <nav class="space-y-2">
      <a href="./profile.php" class="block px-4 py-2 rounded hover:bg-gray-700 
        <?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'bg-gray-700' : '' ?>">
        Profile
      </a>
      <a href="./index.php" class="block px-4 py-2 rounded hover:bg-gray-700 
        <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'bg-gray-700' : '' ?>">
        Dashboard
      </a>
      <a href="./transaction.php" class="block px-4 py-2 rounded hover:bg-gray-700 
        <?= basename($_SERVER['PHP_SELF']) == 'transaction.php' ? 'bg-gray-700' : '' ?>">
        Transaction
      </a>
      <a href="./report_analysis.php" class="block px-4 py-2 rounded hover:bg-gray-700 
        <?= basename($_SERVER['PHP_SELF']) == 'report_analysis.php' ? 'bg-gray-700' : '' ?>">
        Report and Analysis
      </a>
      <a href="./loan.php" class="block px-4 py-2 rounded hover:bg-gray-700 
        <?= basename($_SERVER['PHP_SELF']) == 'loan.php' ? 'bg-gray-700' : '' ?>">
        Loan
      </a>
      <a href="./settings.php" class="block px-4 py-2 rounded hover:bg-gray-700 
        <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'bg-gray-700' : '' ?>">
        Settings
      </a>
    </nav>
  </div>
  <a href="#" id="logoutBtn"
    class="block px-4 py-2 mt-4 bg-red-600 text-center rounded hover:bg-red-500">
    Logout
  </a>
</aside>

<!-- Only keep one modal -->
<div id="hs-sign-out-alert" class="hidden size-full fixed top-0 start-0 z-80 overflow-x-hidden overflow-y-auto">
  <div class="hs-overlay-open:mt-7 hs-overlay-open:opacity-100 hs-overlay-open:duration-500 mt-0 opacity-0 ease-out transition-all sm:max-w-lg sm:w-full m-3 sm:mx-auto">
    <div class="relative flex flex-col bg-white shadow-lg rounded-xl dark:bg-neutral-900">
      <div class="absolute top-2 end-2">
        <button type="button" id="closeModalBtn"
          class="size-8 inline-flex justify-center items-center gap-x-2 rounded-full bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:text-neutral-400"
          aria-label="Close">
          <span class="sr-only">Close</span>
          ✕
        </button>
      </div>

      <div class="p-4 sm:p-10 text-center overflow-y-auto">
        <span class="mb-4 inline-flex justify-center items-center size-15.5 rounded-full border-4 border-yellow-50 bg-yellow-100 text-yellow-500 dark:bg-yellow-700 dark:border-yellow-600 dark:text-yellow-100">
          ⚠️
        </span>

        <h3 class="mb-2 text-2xl font-bold text-gray-800 dark:text-neutral-200">
          Sign out
        </h3>
        <p class="text-gray-500 dark:text-neutral-500">
          Are you sure you would like to sign out of your Preline account?
        </p>

        <div class="mt-6 flex justify-center gap-x-4">
          <button id="confirmLogout" type="button"
            class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 hover:bg-gray-50 dark:bg-transparent dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-800">
            Sign out
          </button>
          <button id="cancelLogout" type="button"
            class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700">
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    const $modal = $("#hs-sign-out-alert");

    // Show modal
    $("#logoutBtn").on("click", function(e) {
      e.preventDefault();
      $modal.removeClass("hidden").addClass("flex"); // make it visible
      $modal.find("> div").removeClass("opacity-0 mt-0").addClass("opacity-100 mt-7");
    });

    // Confirm logout
    $("#confirmLogout").on("click", function() {
      window.location.href = "logout.php";
    });

    // Cancel / Close
    $("#cancelLogout, #closeModalBtn").on("click", function() {
      $modal.addClass("hidden").removeClass("flex");
      $modal.find("> div").removeClass("opacity-100 mt-7").addClass("opacity-0 mt-0");
    });
  });
</script>