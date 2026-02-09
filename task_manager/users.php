<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'db.php';
require_once 'classes/User.php';

$db = new Database();
$userObj = new User($db->getConnection());
$users = $userObj->getUsers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased">
    <div class="max-w-4xl mx-auto p-6">
        <div class="flex justify-between items-center mb-8 border-b border-gray-200 pb-6">
            <h2 class="text-2xl font-bold tracking-tight">Registered Users</h2>
            <div class="flex gap-4 text-sm font-medium">
                <a href="dashboard.php" class="text-gray-600 hover:text-black transition-colors">Back to Dashboard</a>
                <a href="logout.php" class="text-red-600 hover:text-red-800 transition-colors">Logout</a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($users as $user): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#<?= htmlspecialchars($user['id']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($user['username']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>