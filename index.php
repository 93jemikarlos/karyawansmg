<?php
// Skrip Koneksi & Pencarian Firebase REST API
$firebaseURL = "https://karyawan-3a1e8-default-rtdb.firebaseio.com/"; // <-- GANTI DENGAN URL FIREBASE ANDA
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$results = [];

// Mengambil data dari Firebase
$url = $firebaseURL . "riwayat_karyawan.json";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

// Proses Filter / Pencarian
if ($data) {
    if ($searchQuery !== '') {
        foreach ($data as $id => $karyawan) {
            // Pencarian berdasarkan Nama, NIK, Jabatan, atau Departemen (Case-Insensitive)
            if (
                strpos(strtolower($karyawan['nama']), strtolower($searchQuery)) !== false ||
                strpos(strtolower($karyawan['nik']), strtolower($searchQuery)) !== false ||
                strpos(strtolower($karyawan['jabatan']), strtolower($searchQuery)) !== false ||
                strpos(strtolower($karyawan['departemen']), strtolower($searchQuery)) !== false
            ) {
                $karyawan['id'] = $id;
                $results[] = $karyawan;
            }
        }
    } else {
        // Jika tidak ada pencarian, tampilkan semua data
        foreach ($data as $id => $karyawan) {
            $karyawan['id'] = $id;
            $results[] = $karyawan;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Riwayat Karyawan Premium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
        }
        .glassmorphism {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body class="min-h-screen text-slate-100 p-4 md:p-8">

    <div class="max-w-6xl mx-auto">
        <header class="mb-10 text-center md:text-left flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-indigo-400">
                    <i class="fa-solid to-blue-500 fa-id-card-clip mr-2 text-blue-400"></i>HR Portal
                </h1>
                <p class="text-sm text-slate-400 mt-1">Manajemen & Pencarian Riwayat Karyawan Eksekutif</p>
            </div>
            <div class="text-xs font-semibold px-3 py-1.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Firebase Live Database Connected
            </div>
        </header>

        <div class="glassmorphism rounded-2xl p-6 mb-8 shadow-2xl">
            <form method="GET" action="" class="flex flex-col md:flex-row gap-4">
                <div class="relative flex-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="<?php echo htmlspecialchars($searchQuery); ?>"
                        placeholder="Cari berdasarkan Nama, NIK, Jabatan, atau Departemen..." 
                        class="w-full bg-slate-900/60 border border-slate-700/60 rounded-xl py-3.5 pl-12 pr-4 text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    >
                </div>
                <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-medium px-8 py-3.5 rounded-xl shadow-lg shadow-indigo-600/20 transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-sliders"></i> Filter Data
                </button>
                <?php if($searchQuery !== ''): ?>
                    <a href="index.php" class="bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium px-6 py-3.5 rounded-xl transition-all flex items-center justify-center">
                        Reset
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="glassmorphism rounded-2xl overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-900/50 border-b border-slate-800 text-slate-400 text-xs font-semibold uppercase tracking-wider">
                            <th class="py-4 px-6">Karyawan</th>
                            <th class="py-4 px-6">NIK</th>
                            <th class="py-4 px-6">Posisi & Dep</th>
                            <th class="py-4 px-6">Tanggal Masuk</th>
                            <th class="py-4 px-6">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <?php if (!empty($results)): ?>
                            <?php foreach ($results as $emp): ?>
                                <tr class="hover:bg-white/[0.02] transition-colors group">
                                    <td class="py-5 px-6 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500/20 to-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 font-bold text-sm uppercase">
                                                <?php echo substr($emp['nama'], 0, 2); ?>
                                            </div>
                                            <div>
                                                <div class="font-semibold text-slate-200 group-hover:text-blue-400 transition-colors">
                                                    <?php echo htmlspecialchars($emp['nama']); ?>
                                                </div>
                                                <div class="text-xs text-slate-500">ID: <?php echo htmlspecialchars($emp['id']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-5 px-6 whitespace-nowrap text-sm text-slate-300 font-mono">
                                        <?php echo htmlspecialchars($emp['nik']); ?>
                                    </td>
                                    <td class="py-5 px-6 whitespace-nowrap">
                                        <div class="text-sm font-medium text-slate-200"><?php echo htmlspecialchars($emp['jabatan']); ?></div>
                                        <div class="text-xs text-indigo-400/80"><?php echo htmlspecialchars($emp['departemen']); ?></div>
                                    </td>
                                    <td class="py-5 px-6 whitespace-nowrap text-sm text-slate-400">
                                        <i class="fa-regular fa-calendar text-slate-500 mr-1.5"></i>
                                        <?php echo date('d M Y', strtotime($emp['tanggal_masuk'])); ?>
                                    </td>
                                    <td class="py-5 px-6 whitespace-nowrap">
                                        <?php if(strtolower($emp['status']) === 'tetap'): ?>
                                            <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                Tetap
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                                Kontrak
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-16 text-center">
                                    <div class="text-slate-500 mb-3">
                                        <i class="fa-regular fa-folder-open text-5xl opacity-30"></i>
                                    </div>
                                    <p class="text-slate-400 font-medium">Tidak ada data riwayat karyawan ditemukan</p>
                                    <p class="text-xs text-slate-600 mt-1">Coba kata kunci lain atau periksa koneksi Firebase Anda.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="bg-slate-950/40 px-6 py-4 border-t border-slate-800 text-xs text-slate-500 flex justify-between items-center">
                <div>Menampilkan <b><?php echo count($results); ?></b> karyawan</div>
                <div>Sistem HR v2.1</div>
            </div>
        </div>
    </div>

</body>
</html>
