<?php
session_start();

if (!isset($_SESSION['contacts'])) {
    $_SESSION['contacts'] = [];
}

function get_contacts() {
    return $_SESSION['contacts'];
}

function save_contacts($contacts) {
    $_SESSION['contacts'] = $contacts;
}

$message = ''; 
$edit_contact = null;
$edit_id = null;
$errors = [];

function validate_contact_form($data) {
    $errors = [];
    if (empty($data['name'])) {
        $errors['name'] = 'Nama wajib diisi.';
    }
    if (empty($data['phone'])) {
        $errors['phone'] = 'Nomor telepon wajib diisi.';
    } elseif (!preg_match('/^[0-9]+$/', $data['phone'])) {
        $errors['phone'] = 'Nomor telepon hanya boleh berisi angka.';
    }
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid.';
    }
    return $errors;
}

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $contact_id = isset($_POST['contact_id']) ? $_POST['contact_id'] : null;

    $data = ['name' => $name, 'phone' => $phone, 'email' => $email];
    $errors = validate_contact_form($data);

    if (empty($errors)) {
        $contacts = get_contacts();

        if ($contact_id !== null && isset($contacts[$contact_id])) {
            $contacts[$contact_id] = $data;
        } else {
            $contacts[] = $data;
        }

        save_contacts($contacts);
        header('Location: index.php');
        exit;
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $contacts = get_contacts();

    if (isset($contacts[$id])) {
        $edit_contact = $contacts[$id];
        $edit_id = $id;
    } 
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $contacts = get_contacts();

    if (isset($contacts[$id])) {
        unset($contacts[$id]);
        $contacts = array_values($contacts);
        save_contacts($contacts);
    } 
    header('Location: index.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'reset') {
    unset($_SESSION['contacts']);
    session_destroy();
    header('Location: index.php');
    exit;
}

$contacts = get_contacts();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Kontak Sederhana - TA 4</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass-container {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            background-color: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
        
        body {
            background: linear-gradient(135deg, #1f2937, #000000, #1f2937);
    </style>
</head>
<body class="min-h-screen p-8 text-gray-100">
    <div class="max-w-5xl mx-auto">
        <header class="text-center mb-12">
            <h1 class="text-5xl font-extrabold text-white tracking-wider animate-[wiggle_1s_ease-in-out_infinite] [animation-iteration-count:1]">
                <span class="text-indigo-400">Technologia</span> Contacts
            </h1>
            <p class="text-gray-400 mt-2">Sistem Manajemen Kontak Sederhana</p>
        </header>

        <?php 
        ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1">
                <div class="glass-container rounded-3xl p-8 transition duration-500 hover:shadow-xl hover:shadow-indigo-500/20">
                    <h2 class="text-3xl font-bold mb-6 text-indigo-400">
                        <?php echo $edit_contact ? '✏️ Edit Kontak' : '➕ Tambah Kontak'; ?>
                    </h2>
                    <form action="index.php" method="POST" class="space-y-4">
                        <?php if ($edit_id !== null): ?>
                            <input type="hidden" name="contact_id" value="<?php echo $edit_id; ?>">
                        <?php endif; ?>

                        <?php 
                        function render_input($id, $label, $type, $value, $errors, $required = true) {
                            $is_error = isset($errors[$id]);
                            $border_class = $is_error ? 'border-red-500 ring-red-500' : 'border-slate-600 focus:border-indigo-400 focus:ring-indigo-400';
                            $required_attr = $required ? 'required' : '';
                            ?>
                            <div>
                                <label for="<?php echo $id; ?>" class="block text-sm font-medium text-gray-300"><?php echo $label; ?></label>
                                <input 
                                    type="<?php echo $type; ?>" 
                                    id="<?php echo $id; ?>" 
                                    name="<?php echo $id; ?>" 
                                    class="mt-1 block w-full bg-slate-800/70 text-white rounded-lg shadow-inner p-3 transition duration-300 border <?php echo $border_class; ?>" 
                                    value="<?php echo htmlspecialchars($value ?? ''); ?>" 
                                    <?php echo $required_attr; ?>
                                >
                                <?php if ($is_error): ?>
                                    <p class="text-red-400 text-xs mt-1 animate-pulse"><?php echo $errors[$id]; ?></p>
                                <?php endif; ?>
                            </div>
                        <?php
                        }
                        
                        $current_name = $edit_contact['name'] ?? $_POST['name'] ?? '';
                        $current_phone = $edit_contact['phone'] ?? $_POST['phone'] ?? '';
                        $current_email = $edit_contact['email'] ?? $_POST['email'] ?? '';
                        
                        render_input('name', 'Nama Lengkap', 'text', $current_name, $errors);
                        render_input('phone', 'Nomor Telepon', 'text', $current_phone, $errors);
                        render_input('email', 'Email (Opsional)', 'email', $current_email, $errors, false);
                        ?>

                        <button type="submit" name="submit" class="w-full py-3 px-4 rounded-lg shadow-md text-white font-semibold transition duration-300 ease-in-out transform 
                            <?php echo $edit_contact ? 'bg-orange-500 hover:bg-orange-600 hover:scale-105' : 'bg-indigo-500 hover:bg-indigo-600 hover:scale-105'; ?> focus:outline-none focus:ring-4 focus:ring-indigo-500/50 mt-6">
                            <?php echo $edit_contact ? 'Simpan Perubahan 💾' : 'Tambahkan Kontak 👤'; ?>
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <h2 class="text-3xl font-bold mb-6 text-white">
                    Daftar Kontak (<?php echo count($contacts); ?>)
                </h2>

                <?php if (empty($contacts)): ?>
                    <div class="glass-container rounded-3xl p-10 text-center text-gray-400 border-dashed border-2 border-indigo-500/30">
                        <p class="text-lg">Daftar kontak masih kosong. Tambahkan kontak pertama Anda di sebelah kiri!</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach ($contacts as $id => $contact): ?>
                            <div class="glass-container rounded-2xl p-6 transition duration-300 ease-in-out transform hover:scale-[1.03] hover:border-indigo-500/50 relative">
                                <span class="absolute top-0 right-0 p-3 text-xs font-mono text-gray-500">#<?php echo $id + 1; ?></span>
                                
                                <p class="text-2xl font-bold text-indigo-300 mb-2">
                                    <?php echo htmlspecialchars($contact['name']); ?>
                                </p>
                                
                                <div class="space-y-1 text-gray-300">
                                    <p class="flex items-center text-sm">
                                        <span class="mr-2 text-indigo-400">📞</span> 
                                        <?php echo htmlspecialchars($contact['phone']); ?>
                                    </p>
                                    <p class="flex items-center text-sm">
                                        <span class="mr-2 text-indigo-400">📧</span> 
                                        <?php echo htmlspecialchars($contact['email'] ?? 'Tidak ada email'); ?>
                                    </p>
                                </div>

                                <div class="mt-4 flex space-x-3">
                                    <a href="index.php?action=edit&id=<?php echo $id; ?>" class="bg-indigo-500 hover:bg-indigo-600 text-white text-xs font-semibold px-4 py-2 rounded-full transition duration-150 transform hover:shadow-lg hover:scale-105">
                                        Edit
                                    </a>
                                    <a href="index.php?action=delete&id=<?php echo $id; ?>" onclick="return confirm('Yakin hapus <?php echo htmlspecialchars($contact['name']); ?>?')" class="bg-red-500 hover:bg-red-600 text-white text-xs font-semibold px-4 py-2 rounded-full transition duration-150 transform hover:shadow-lg hover:scale-105">
                                        Hapus
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="mt-10 text-center">
                    <a href="index.php?action=reset" onclick="return confirm('⚠️ PERINGATAN! Ini akan menghapus SEMUA data kontak (Session). Lanjutkan?')" class="text-sm font-medium text-gray-500 hover:text-red-400 transition duration-300 transform hover:scale-105">
                        [ Hapus Semua Data ]
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>