<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ambil nama user dari session, fallback ke "Admin"
$displayName = 'Admin';
if (isset($_SESSION['user']['id'])) {
    // Koneksi ke DB (bisa gunakan class Database dari login.php)
    $env = parse_ini_file(__DIR__ . '/.env');
    $pdo = new PDO(
        "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4",
        $env['DB_USER'],
        $env['DB_PASS']
    );
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && !empty($row['name'])) {
        $displayName = $row['name'];
    }
} else {
    // Jika tidak ada session, redirect ke login
    header('Location: /login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMRS</title>
    <!-- Favicon from Google Material Icons -->
    <link rel="icon" href="https://img.icons8.com/?size=100&id=4PbFeZOKAc61&format=png&color=000000" type="image/svg+xml">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<body class="bg-gray-100 min-h-screen flex">
    <!-- Sidebar -->
    <aside id="sidebar" class="w-64 bg-white shadow-lg flex flex-col min-h-screen transition-transform duration-200
        fixed z-30 inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 md:flex">
        <div class="p-6 border-b">
            <span class="text-2xl font-bold text-blue-600">SIMRS</span>
        </div>
        <nav class="flex-1 p-4">
            <ul class="space-y-2">
                <li><a href="/dasbor" class="flex items-center px-4 py-2 rounded-lg text-gray-700 hover:bg-blue-100"><span class="material-icons mr-2">dashboard</span>Dasbor</a></li>
                <li><a href="/user" class="flex items-center px-4 py-2 rounded-lg text-gray-700 hover:bg-blue-100"><span class="material-icons mr-2">people</span>Pengguna</a></li>
                <li><a href="/role" class="flex items-center px-4 py-2 rounded-lg text-gray-700 hover:bg-blue-100"><span class="material-icons mr-2">lock</span>Role</a></li>
                <li><a href="/permission" class="flex items-center px-4 py-2 rounded-lg text-gray-700 hover:bg-blue-100"><span class="material-icons mr-2">lock</span>Permission</a></li>
                <li><a href="/patient" class="flex items-center px-4 py-2 rounded-lg text-gray-700 hover:bg-blue-100"><span class="material-icons mr-2">people</span>Pasien</a></li>
                <li><a href="/doctor" class="flex items-center px-4 py-2 rounded-lg text-gray-700 hover:bg-blue-100"><span class="material-icons mr-2">people</span>Dokter</a></li>
                <li><a href="/logout" class="flex items-center px-4 py-2 rounded-lg text-red-600 hover:bg-red-100"><span class="material-icons mr-2">logout</span>Logout</a></li>
            </ul>
        </nav>
    </aside>
    <!-- Overlay for mobile -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-40 z-20 hidden md:hidden"></div>
    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-h-screen md:ml-0">
        <!-- Header/Navbar -->
        <header class="bg-white shadow p-4 flex items-center justify-between p-6 border-b">
            <!-- Hamburger Button -->
            <button id="sidebar-toggle" class="md:hidden mr-2 focus:outline-none">
                <span class="material-icons text-3xl text-gray-700">menu</span>
            </button>
            <h1 class="text-xl font-semibold text-gray-800">Dashboard</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600">Hello, <?= htmlspecialchars($displayName) ?></span>
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($displayName) ?>" alt="User Avatar" class="w-8 h-8 rounded-full">
            </div>
        </header>
        <!-- Dashboard Content -->
        <main class="flex-1 p-6">
            <?= $module_content ?>
        </main>
    </div>
    <script>
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebarOverlay = document.getElementById('sidebar-overlay');

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            sidebarOverlay.classList.toggle('hidden');
        });

        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        });
    </script>
</body>
</html>