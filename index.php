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
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); }
        .glassmorphism { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.08); }
    </style>
</head>
<body class="min-h-screen text-slate-100 p-4 md:p-8">

    <div class="max-w-6xl mx-auto">
        <header class="mb-10 text-center md:text-left flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-indigo-400">
                    <i class="fa-solid fa-id-card-clip mr-2 text-blue-400"></i>HR Portal
                </h1>
                <p class="text-sm text-slate-400 mt-1">Manajemen & Pencarian Riwayat Karyawan Eksekutif</p>
            </div>
            <div id="connection-status" class="text-xs font-semibold px-3 py-1.5 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span> Connecting to Firebase...
            </div>
        </header>

        <div class="glassmorphism rounded-2xl p-6 mb-8 shadow-2xl">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="relative flex-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                    <input 
                        type="text" 
                        id="search-input"
                        placeholder="Cari berdasarkan Nama, NIK, Jabatan, atau Departemen..." 
                        class="w-full bg-slate-900/60 border border-slate-700/60 rounded-xl py-3.5 pl-12 pr-4 text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    >
                </div>
                <button onclick="clearSearch()" id="btn-reset" class="hidden bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium px-6 py-3.5 rounded-xl transition-all items-center justify-center">
                    Reset
                </button>
            </div>
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
                    <tbody id="employee-table-body" class="divide-y divide-slate-800/60">
                    </tbody>
                </table>
            </div>
            
            <div class="bg-slate-950/40 px-6 py-4 border-t border-slate-800 text-xs text-slate-500 flex justify-between items-center">
                <div id="total-employees">Menghitung karyawan...</div>
                <div>Sistem HR v2.1</div>
            </div>
        </div>
    </div>

    <script>
        // 1. URL FIREBASE SUDAH DIPERBAIKI (Ditambahkan node utama dan .json)
        const firebaseURL = "https://karyawan-3a1e8-default-rtdb.firebaseio.com/riwayat_karyawan.json"; 

        let allEmployees = [];

        // Fungsi Ambil Data dari Firebase
        async function fetchEmployees() {
            try {
                const response = await fetch(firebaseURL);
                const data = await response.json();
                
                if(data) {
                    // Mengubah objek Firebase menjadi array
                    allEmployees = Object.keys(data).map(key => ({
                        id: key,
                        ...data[key]
                    }));
                    
                    // Update status koneksi menjadi sukses
                    const statusDiv = document.getElementById('connection-status');
                    statusDiv.className = "text-xs font-semibold px-3 py-1.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center gap-2";
                    statusDiv.innerHTML = `<span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Firebase Live Connected`;
                    
                    renderTable(allEmployees);
                } else {
                    // Jika database terhubung tapi kosong
                    document.getElementById('employee-table-body').innerHTML = `
                        <tr><td colspan="5" class="py-16 text-center text-slate-400">Database Firebase terhubung, tetapi tidak ada data ditemukan.</td></tr>
                    `;
                    document.getElementById('total-employees').innerHTML = `Menampilkan <b>0</b> karyawan`;
                }
            } catch (error) {
                console.error("Gagal memuat data:", error);
                document.getElementById('employee-table-body').innerHTML = `
                    <tr><td colspan="5" class="py-16 text-center text-red-400">Gagal terhubung ke Firebase. Periksa URL atau aturan database (.read: true)</td></tr>
                `;
            }
        }

        // Fungsi untuk Merender Data ke Tabel HTML
        function renderTable(data) {
            const tableBody = document.getElementById('employee-table-body');
            const totalDiv = document.getElementById('total-employees');
            tableBody.innerHTML = '';

            if(data.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="py-16 text-center">
                            <div class="text-slate-500 mb-3"><i class="fa-regular fa-folder-open text-5xl opacity-30"></i></div>
                            <p class="text-slate-400 font-medium">Tidak ada data riwayat karyawan ditemukan</p>
                        </td>
                    </tr>`;
                totalDiv.innerHTML = `Menampilkan <b>0</b> karyawan`;
                return;
            }

            data.forEach(emp => {
                // Antisipasi jika ada data properti yang kosong/null di Firebase
                const nama = emp.nama || "Tanpa Nama";
                const nik = emp.nik || "-";
                const jabatan = emp.jabatan || "-";
                const departemen = emp.departemen || "-";
                const status = emp.status || "Kontrak";
                const tanggal = emp.tanggal_masuk ? new Date(emp.tanggal_masuk) : new Date();

                const badgeClass = status.toLowerCase() === 'tetap' 
                    ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' 
                    : 'bg-amber-500/10 text-amber-400 border border-amber-500/20';

                // Format tanggal sederhana
                const dateOptions = { day: '2-digit', month: 'short', year: 'numeric' };
                const formattedDate = tanggal.toLocaleDateString('id-ID', dateOptions);

                const row = `
                    <tr class="hover:bg-white/[0.02] transition-colors group">
                        <td class="py-5 px-6 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500/20 to-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 font-bold text-sm uppercase">
                                    ${nama.substring(0, 2)}
                                </div>
                                <div>
                                    <div class="font-semibold text-slate-200 group-hover:text-blue-400 transition-colors">${nama}</div>
                                    <div class="text-xs text-slate-500">ID: ${emp.id}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-5 px-6 whitespace-nowrap text-sm text-slate-300 font-mono">${nik}</td>
                        <td class="py-5 px-6 whitespace-nowrap">
                            <div class="text-sm font-medium text-slate-200">${jabatan}</div>
                            <div class="text-xs text-indigo-400/80">${departemen}</div>
                        </td>
                        <td class="py-5 px-6 whitespace-nowrap text-sm text-slate-400">
                            <i class="fa-regular fa-calendar text-slate-500 mr-1.5"></i>${formattedDate}
                        </td>
                        <td class="py-5 px-6 whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-md text-xs font-semibold ${badgeClass}">${status}</span>
                        </td>
                    </tr>
                `;
                tableBody.innerHTML += row;
            });

            totalDiv.innerHTML = `Menampilkan <b>${data.length}</b> karyawan`;
        }

        // Logika Pencarian Real-time (Ketik langsung memfilter)
        const searchInput = document.getElementById('search-input');
        const btnReset = document.getElementById('btn-reset');

        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            
            if(query !== '') {
                btnReset.classList.remove('hidden');
                btnReset.classList.add('flex');
            } else {
                btnReset.classList.add('hidden');
            }

            const filtered = allEmployees.filter(emp => {
                const nama = (emp.nama || "").toLowerCase();
                const nik = (emp.nik || "").toLowerCase();
                const jabatan = (emp.jabatan || "").toLowerCase();
                const departemen = (emp.departemen || "").toLowerCase();

                return nama.includes(query) || 
                       nik.includes(query) || 
                       jabatan.includes(query) || 
                       departemen.includes(query);
            });

            renderTable(filtered);
        });

        function clearSearch() {
            searchInput.value = '';
            btnReset.classList.add('hidden');
            renderTable(allEmployees);
        }

        // Jalankan fungsi saat halaman dimuat
        fetchEmployees();
    </script>
</body>
</html>
