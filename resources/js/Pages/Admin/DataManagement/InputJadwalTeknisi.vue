<template>
    <Head title="Jadwal Teknisi" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Input & Kelola Jadwal Teknisi</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-xl font-bold mb-4">Daftar & Kelola Jadwal Teknisi</h3>
                    <button @click="openScheduleForm()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4">
                        Tambah Jadwal Baru
                    </button>

                    <p v-if="scheduleLoading" class="text-center text-gray-600">Memuat jadwal...</p>
                    <p v-else-if="scheduleError" class="text-center text-red-600">{{ scheduleError }}</p>
                    
                    <table v-else-if="schedules.length > 0" class="min-w-full bg-white border border-gray-300">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 bg-teal-600 text-white font-semibold text-center border-b border-r border-teal-700 w-1/12"></th>
                                <th class="py-2 px-4 bg-teal-600 text-white font-semibold text-center border-b border-teal-700">NAMA</th>
                                <th class="py-2 px-4 bg-teal-600 text-white font-semibold text-center border-b border-teal-700">JADWAL</th>
                                <th class="py-2 px-4 bg-teal-600 text-white font-semibold text-center border-b border-teal-700">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item) in schedules" :key="item.id" class="border-b border-gray-300 last:border-b-0">
                                <td class="py-2 px-4 text-center border-r border-gray-300">{{ item.index }}.</td>
                                <td class="py-2 px-4 border-r border-gray-300">{{ item.nama }}</td>
                                <td class="py-2 px-4 text-center">{{ item.jadwal }}</td>
                                <td class="py-2 px-4 text-center">
                                    <button @click="openScheduleForm(item)" class="bg-yellow-500 hover:bg-yellow-700 text-white text-sm py-1 px-2 rounded mr-2">Edit</button>
                                    <button @click="deleteSchedule(item.id)" class="bg-red-500 hover:bg-red-700 text-white text-sm py-1 px-2 rounded">Hapus</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-else class="text-center text-gray-600">Tidak ada data jadwal teknisi.</p>
                </div>
            </div>

            <div v-if="showScheduleForm" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white p-6 rounded-lg shadow-xl w-96">
                    <h2 class="text-xl font-bold mb-4">{{ editingScheduleItem ? 'Edit Jadwal' : 'Tambah Jadwal Baru' }}</h2>
                    <form @submit.prevent="saveSchedule">
                        <div class="mb-4">
                            <label for="nama_teknisi" class="block text-gray-700 text-sm font-bold mb-2">Nama:</label>
                            <select id="nama_teknisi" v-model="scheduleForm.nama"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                required>
                                <option value="">Pilih Teknisi</option>
                                <option v-for="teknisi in masterTeknisiList" :key="teknisi" :value="teknisi">{{ teknisi }}</option>
                            </select>
                            <p v-if="scheduleFormErrors.nama" class="text-red-500 text-xs mt-1">{{ scheduleFormErrors.nama[0] }}</p>
                        </div>
                        <div class="mb-6">
                            <label for="jadwal_status" class="block text-gray-700 text-sm font-bold mb-2">Jadwal:</label>
                            <select id="jadwal_status" v-model="scheduleForm.jadwal"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                required>
                                <option value="MASUK">MASUK</option>
                                <option value="LIBUR">LIBUR</option>
                            </select>
                            <p v-if="scheduleFormErrors.jadwal" class="text-red-500 text-xs mt-1">{{ scheduleFormErrors.jadwal[0] }}</p>
                        </div>
                        <div class="flex items-center justify-between">
                            <button type="submit"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Simpan
                            </button>
                            <button type="button" @click="closeScheduleForm"
                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue'; // Tidak perlu watch atau usePage di sini
import { db } from '@/firebase';
import { ref as dbRef, onValue, push, set, update, remove } from 'firebase/database';

// --- State dan Logika untuk Master List Teknisi (PENTING: tambahkan ini!) ---
const masterTeknisiList = ref([]);
const masterTeknisiLoading = ref(true);
const masterTeknisiError = ref(null);

const masterTeknisiRef = dbRef(db, 'masterTeknisi'); // Referensi ke node masterTeknisi

const fetchMasterTeknisi = () => {
    onValue(masterTeknisiRef, (snapshot) => {
        const data = snapshot.val();
        console.log('Data mentah dari /masterTeknisi:', data); // TAMBAHKAN INI
        const loadedTeknisi = [];
        if (data) {
            for (let id in data) {
                if (data[id] && data[id].nama) {
                    loadedTeknisi.push(data[id].nama);
                } else {
                console.warn("Entri masterTeknisi tidak memiliki properti 'nama' atau bernilai kosong:", data[id]);
                }
            }
        }
        masterTeknisiList.value = loadedTeknisi.sort();
        masterTeknisiLoading.value = false;
    }, (err) => {
        masterTeknisiError.value = 'Gagal memuat daftar master teknisi.';
        console.error('Error fetching master teknisi from Firebase:', err);
        masterTeknisiLoading.value = false;
    });
};

onMounted(() => {
    fetchSchedules(); 
    fetchMasterTeknisi(); // <-- TAMBAHKAN PANGGILAN INI
});
// --- Akhir State dan Logika Master List Teknisi ---


// --- State dan Logika untuk JADWAL TEKNISI (ini yang dibutuhkan di sini) ---
const schedules = ref([]);
const scheduleLoading = ref(true);
const scheduleError = ref(null);
const showScheduleForm = ref(false);
const editingScheduleItem = ref(null);
const scheduleForm = ref({ nama: '', jadwal: 'MASUK' });
const scheduleFormErrors = ref({});

const schedulesRef = dbRef(db, 'technicianSchedules');

const fetchSchedules = () => {
    onValue(schedulesRef, (snapshot) => {
        const data = snapshot.val();
        const loadedSchedules = [];
        let index = 1;

        if (data) {
            for (let id in data) {
                loadedSchedules.push({
                    id: id,
                    index: index++,
                    nama: data[id].nama,
                    jadwal: data[id].jadwal,
                });
            }
        }
        schedules.value = loadedSchedules;
        scheduleLoading.value = false;
    }, (err) => {
        scheduleError.value = 'Gagal memuat jadwal teknisi dari Firebase.';
        console.error('Error fetching schedules from Firebase:', err);
        scheduleLoading.value = false;
    });
};

onMounted(() => {
    fetchSchedules(); // <-- HANYA PANGGIL fetchSchedules di SINI
});
const openScheduleForm = (item = null) => {
    if (item) {
        editingScheduleItem.value = item;
        scheduleForm.value.nama = item.nama;
        scheduleForm.value.jadwal = item.jadwal;
    } else {
        editingScheduleItem.value = null;
        scheduleForm.value = { nama: '', jadwal: 'MASUK' };
    }
    scheduleFormErrors.value = {};
    showScheduleForm.value = true;
};

const closeScheduleForm = () => {
    showScheduleForm.value = false;
    scheduleForm.value = { nama: '', jadwal: 'MASUK' };
    editingScheduleItem.value = null;
    scheduleFormErrors.value = {};
};

const saveSchedule = async () => {
    scheduleFormErrors.value = {};

    if (!scheduleForm.value.nama) {
        scheduleFormErrors.value.nama = ['Nama harus diisi.'];
    }
    if (!scheduleForm.value.jadwal || (scheduleForm.value.jadwal !== 'MASUK' && scheduleForm.value.jadwal !== 'LIBUR')) {
        scheduleFormErrors.value.jadwal = ['Jadwal harus MASUK atau LIBUR.'];
    }

    if (Object.keys(scheduleFormErrors.value).length > 0) {
        alert('Silakan lengkapi semua field yang wajib diisi!');
        return;
    }

    try {
        if (editingScheduleItem.value) {
            const itemRef = dbRef(db, `technicianSchedules/${editingScheduleItem.value.id}`);
            await update(itemRef, {
                nama: scheduleForm.value.nama,
                jadwal: scheduleForm.value.jadwal,
            });
            alert('Jadwal berhasil diperbarui di Firebase!');
        } else {
            await push(schedulesRef, {
                nama: scheduleForm.value.nama,
                jadwal: scheduleForm.value.jadwal,
                created_at: new Date().toISOString(),
            });
            alert('Jadwal berhasil ditambahkan ke Firebase!');
        }
        closeScheduleForm();
    } catch (e) {
        console.error('Error saving schedule to Firebase:', e);
        scheduleError.value = 'Gagal menyimpan jadwal ke Firebase.';
    }
};

const deleteSchedule = async (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus jadwal ini dari Firebase?')) {
        try {
            const itemRef = dbRef(db, `technicianSchedules/${id}`);
            await remove(itemRef);
            alert('Jadwal berhasil dihapus dari Firebase!');
        } catch (e) {
            console.error('Error deleting schedule from Firebase:', e);
            scheduleError.value = 'Gagal menghapus jadwal dari Firebase.';
        }
    }
};
</script>

<style scoped>
/* Tambahkan gaya khusus untuk halaman ini jika diperlukan */
</style>