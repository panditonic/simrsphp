<?php

include 'db.php'; // Pastikan file db.php ada di direktori yang sama

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    try {
        $db = new Database();
        $user = $db->login($email, $password);
        if ($user) {
            $_SESSION['user'] = $user;
            header('Location: dasbor');
            exit;
        } else {
            $error = 'Email atau password salah.';
        }
    } catch (Exception $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Responsive Tailwind Login Page</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100">
  <div class="flex flex-col md:flex-row bg-white rounded-lg shadow-lg overflow-hidden w-full max-w-4xl m-4">
    <!-- Left panel with gradient (hidden on small screens) -->
    <div class="hidden md:block md:w-1/2 bg-gradient-to-br from-blue-500 to-indigo-600 p-10">
      <h2 class="text-white text-3xl font-bold mb-4">Welcome Back!</h2>
      <p class="text-blue-100">Enter your credentials to access your account and continue where you left off.</p>
    </div>
    <!-- Right panel: login form -->
    <div class="w-full md:w-1/2 p-8">
      <h2 class="text-2xl font-semibold text-gray-700 mb-6 text-center md:text-left">Sign In</h2>
      <?php if ($error): ?>
        <div class="mb-4 text-red-600 bg-red-100 rounded p-2 text-sm"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form action="" method="POST" class="space-y-5">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-600 mb-1">Email address</label>
          <input
            type="email"
            id="email"
            name="email"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
            placeholder="you@example.com"
          />
        </div>
        <div>
          <label for="password" class="block text-sm font-medium text-gray-600 mb-1">Password</label>
          <input
            type="password"
            id="password"
            name="password"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
            placeholder="••••••••"
          />
        </div>
        <div class="flex items-center justify-between text-sm">
          <label class="flex items-center">
            <input type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
            <span class="ml-2 text-gray-600">Remember me</span>
          </label>
          <a href="#" class="text-blue-500 hover:underline">Forgot Password?</a>
        </div>
        <button
          type="submit"
          class="w-full py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors"
        >
          Sign In
        </button>
      </form>
      <p class="mt-6 text-center text-sm text-gray-600">
        Don't have an account?
        <a href="#" class="text-blue-500 hover:underline font-medium">Sign up</a>
      </p>
    </div>
  </div>
</body>
</html>