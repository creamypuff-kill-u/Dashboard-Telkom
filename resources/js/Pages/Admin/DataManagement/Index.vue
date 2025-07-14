<template>
    <Head title="Manajemen Data" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manajemen Orders</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div v-if="currentSection === 'orders'">
                        <h3 class="text-xl font-bold mb-4">Daftar & Kelola Order</h3>
                        <button @click="openOrderForm()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4">
                            Tambah Order Baru
                        </button>

                        <p v-if="orderLoading" class="text-center text-gray-600">Memuat data order...</p>
                        <p v-else-if="orderError" class="text-center text-red-600">{{ orderError }}</p>
                        
                        <table v-else-if="orders.length > 0" class="min-w-full bg-white border border-gray-300">
                            <thead>
                                <tr>
                                    <th class="py-2 px-4 bg-gray-700 text-white font-semibold text-center border-b border-r border-gray-600 last:border-r-0">SC ID</th>
                                    <th class="py-2 px-4 bg-gray-700 text-white font-semibold text-center border-b border-r border-gray-600 last:border-r-0">Order Date</th>
                                    <th class="py-2 px-4 bg-gray-700 text-white font-semibold text-center border-b border-r border-gray-600 last:border-r-0">Order</th>
                                    <th class="py-2 px-4 bg-gray-700 text-white font-semibold text-center border-b border-r border-gray-600 last:border-r-0">Teknisi</th>
                                    <th class="py-2 px-4 bg-gray-700 text-white font-semibold text-center border-b border-r border-gray-600 last:border-r-0">Status Order</th>
                                    <th class="py-2 px-4 bg-gray-700 text-white font-semibold text-center border-b border-r border-gray-600 last:border-r-0">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="order in orders" :key="order.id" class="border-b border-gray-300 last:border-b-0">
                                    <td class="py-2 px-4 text-center border-r border-gray-300">{{ order.sc_id }}</td>
                                    <td class="py-2 px-4 text-center border-r border-gray-300">{{ formatDisplayDate(order.order_date) }}</td>
                                    <td class="py-2 px-4 text-center border-r border-gray-300">{{ order.order_id }}</td>
                                    <td class="py-2 px-4 text-center border-r border-gray-300">{{ order.teknisi }}</td>
                                    <td class="py-2 px-4 text-center border-r border-gray-300">{{ order.status_order }}</td>
                                    <td class="py-2 px-4 text-center">
                                        <button @click="openOrderForm(order)" class="bg-yellow-500 hover:bg-yellow-700 text-white text-sm py-1 px-2 rounded mr-2">Edit</button>
                                        <button @click="deleteOrder(order.id)" class="bg-red-500 hover:bg-red-700 text-white text-sm py-1 px-2 rounded">Hapus</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p v-else class="text-center text-gray-600">Tidak ada data order ditemukan.</p>
                    </div>

                    <!-- <div v-else-if="currentSection === 'technician-schedule'">
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
                                    <th class="py-2 px-4 bg-teal-600 text-white font-semibold text-center border-b border-r border-teal-700 cursor-pointer">
                                        <div class="flex items-center justify-center">NAMA</div>
                                    </th>
                                    <th class="py-2 px-4 bg-teal-600 text-white font-semibold text-center border-b border-teal-700">JADWAL</th>
                                    <th class="py-2 px-4 bg-teal-600 text-white font-semibold text-center border-b border-teal-700">Aksi</th>
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
                    </div> -->

                    <div v-if="showOrderForm" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
                        <div class="bg-white p-6 rounded-lg shadow-xl w-1/2">
                            <h2 class="text-xl font-bold mb-4">{{ editingOrderItem ? 'Edit Order' : 'Tambah Order Baru' }}</h2>
                            <form @submit.prevent="saveOrder" class="grid grid-cols-2 gap-4">
                                <div class="col-span-1">
                                    <label for="order_date" class="block text-gray-700 text-sm font-bold mb-2">Tanggal Order:</label>
                                    <input type="date" id="order_date" v-model="orderForm.order_date"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                        required />
                                    <p v-if="orderFormErrors.order_date" class="text-red-500 text-xs mt-1">{{ orderFormErrors.order_date[0] }}</p>
                                </div>
                                <div class="col-span-1">
                                    <label for="order_id" class="block text-gray-700 text-sm font-bold mb-2">Order ID:</label>
                                    <select id="order_id" v-model="orderForm.order_id"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                        required >
                                        <option value="">Pilih Order ID</option>
                                        <option v-for="orderId in masterOrderIdsList" :key="orderId" :value="orderId">{{ orderId }}</option>
                                    </select>
                                </div>
                                <div class="col-span-1">
                                    <label for="sc_id" class="block text-gray-700 text-sm font-bold mb-2">SC ID:</label>
                                    <input type="text" id="sc_id" v-model="orderForm.sc_id"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                        required />
                                    <p v-if="orderFormErrors.sc_id" class="text-red-500 text-xs mt-1">{{ orderFormErrors.sc_id[0] }}</p>
                                </div>
                                <div class="col-span-1">
                                    <label for="teknisi" class="block text-gray-700 text-sm font-bold mb-2">Teknisi:</label>
                                    <select id="teknisi" v-model="orderForm.teknisi"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                        required>
                                        <option value="">Pilih Teknisi</option>
                                        <option v-for="teknisi in masterTeknisiList" :key="teknisi" :value="teknisi">{{ teknisi }}</option>
                                    </select>
                                    <!-- <select id="teknisi" v-model="orderForm.teknisi"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                        required>
                                        <option value="">Pilih Teknisi</option>
                                        <option v-for="teknisi in uniqueTeknisiListFromOrders" :key="teknisi" :value="teknisi">{{ teknisi }}</option>
                                    </select> -->
                                    <p v-if="orderFormErrors.teknisi" class="text-red-500 text-xs mt-1">{{ orderFormErrors.teknisi[0] }}</p>
                                </div>
                                <div class="col-span-1">
                                    <label for="status_order" class="block text-gray-700 text-sm font-bold mb-2">Status Order:</label>
                                    <select id="status_order" v-model="orderForm.status_order"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                        required>
                                        <option value="">Pilih Status</option>
                                        <option value="MANJA">MANJA</option>
                                        <option value="CANCEL">CANCEL</option>
                                        <option value="PS">PS</option>
                                        <option value="KENDALA TEKNIK">KENDALA TEKNIK</option>
                                        <option value="PROSET">PROSET</option>
                                        <option value="OGP SURVEY">OGP SURVEY</option>
                                        <option value="KENDALA PELANGGAN">KENDALA PELANGGAN</option>
                                        </select>
                                    <p v-if="orderFormErrors.status_order" class="text-red-500 text-xs mt-1">{{ orderFormErrors.status_order[0] }}</p>
                                </div>
                                <div class="col-span-2">
                                    <label for="keterangan" class="block text-gray-700 text-sm font-bold mb-2">Keterangan:</label>
                                    <textarea id="keterangan" v-model="orderForm.keterangan"
                                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                                    <p v-if="orderFormErrors.keterangan" class="text-red-500 text-xs mt-1">{{ formErrors.keterangan[0] }}</p>
                                </div>
                                <div class="flex items-center justify-between col-span-2 mt-4">
                                    <button type="submit"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                        Simpan
                                    </button>
                                    <button type="button" @click="closeOrderForm"
                                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                        Batal
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div v-if="showScheduleForm" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
                        <div class="bg-white p-6 rounded-lg shadow-xl w-96">
                            <h2 class="text-xl font-bold mb-4">{{ editingScheduleItem ? 'Edit Jadwal' : 'Tambah Jadwal Baru' }}</h2>
                            <form @submit.prevent="saveSchedule">
                                <div class="mb-4">
                                    <label for="nama_teknisi" class="block text-gray-700 text-sm font-bold mb-2">Nama:</label>
                                    <input type="text" id="nama_teknisi" v-model="scheduleForm.nama"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                        required />
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
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted, watch, computed } from 'vue';
import { db } from '@/firebase';
import { ref as dbRef, onValue, push, set, update, remove } from 'firebase/database';

const page = usePage();

// State untuk navigasi section
const currentSection = ref(page.props.ziggy?.query?.section || 'orders'); 
watch(() => page.props.ziggy?.query?.section, (newSection) => {
    if (newSection) {
        currentSection.value = newSection;
    }
}, { immediate: true }); 

// --- State dan Logika untuk ORDERS ---
const orders = ref([]);
const orderLoading = ref(true);
const orderError = ref(null);
const showOrderForm = ref(false); // <-- Variabel visibility modal Order
const editingOrderItem = ref(null);
const orderForm = ref({ // <-- Variabel form untuk Order
    order_date: '',
    order_id: '',
    sc_id: '',
    teknisi: '',
    status_order: '',
    keterangan: ''
});
const orderFormErrors = ref({}); // <-- Variabel error form untuk Order

const ordersRef = dbRef(db, 'orders');

const fetchOrders = () => {
    onValue(ordersRef, (snapshot) => {
        const data = snapshot.val();
        const loadedOrders = [];
        if (data) {
            for (let id in data) {
                loadedOrders.push({
                    id: id,
                    order_date: data[id].order_date,
                    order_id: data[id].order_id,
                    sc_id: data[id].sc_id,
                    teknisi: data[id].teknisi,
                    status_order: data[id].status_order,
                    keterangan: data[id].keterangan,
                });
            }
        }
        orders.value = loadedOrders;
        orderLoading.value = false;
    }, (err) => {
        orderError.value = 'Gagal memuat data order dari Firebase.';
        console.error('Error fetching orders from Firebase:', err);
        orderLoading.value = false;
    });
};

onMounted(fetchOrders);

// --- Fungsi untuk membuka form Order ---
const openOrderForm = (item = null) => {
    if (item) {
        editingOrderItem.value = item;
        orderForm.value = JSON.parse(JSON.stringify(item));

        // Tambahkan logika ini: Jika status_order dari data lama adalah 'STATUS ORDER',
        // ubah menjadi string kosong agar cocok dengan opsi placeholder 'Pilih Status Order'
        if (orderForm.value.status_order === 'STATUS ORDER') {
            orderForm.value.status_order = ''; 
        }
    } else {
        editingOrderItem.value = null;
        orderForm.value = { 
            order_date: '',
            order_id: '',
            sc_id: '',
            teknisi: '',
            status_order: '', // Ini akan otomatis memilih opsi placeholder 'Pilih Status Order'
            keterangan: ''
        };
    }
    orderFormErrors.value = {}; 
    showOrderForm.value = true;
};

// --- Fungsi untuk menutup form Order ---
const closeOrderForm = () => {
    showOrderForm.value = false; // <-- GUNAKAN showOrderForm
    editingOrderItem.value = null;
    orderForm.value = { // <-- GUNAKAN orderForm
        order_date: '',
        order_id: '',
        sc_id: '',
        teknisi: '',
        status_order: '',
        keterangan: ''
    };
    orderFormErrors.value = {}; // <-- GUNAKAN orderFormErrors
};

// --- Fungsi untuk menyimpan (tambah/edit) Order ---
const saveOrder = async () => {
    orderFormErrors.value = {}; // <-- GUNAKAN orderFormErrors

    // --- Validasi Frontend Sederhana ---
    if (!orderForm.value.order_date) orderFormErrors.value.order_date = ['Tanggal Order harus diisi.']; // <-- GUNAKAN orderForm
    if (!orderForm.value.order_id) orderFormErrors.value.order_id = ['Order ID harus diisi.']; // <-- GUNAKAN orderForm
    if (!orderForm.value.sc_id) orderFormErrors.value.sc_id = ['SC ID harus diisi.']; // <-- GUNAKAN orderForm
    if (!orderForm.value.teknisi) orderFormErrors.value.teknisi = ['Teknisi harus diisi.']; // <-- GUNAKAN orderForm
    if (!orderForm.value.status_order) orderFormErrors.value.status_order = ['Status Order harus dipilih.']; // <-- GUNAKAN orderForm

    if (Object.keys(orderFormErrors.value).length > 0) { // <-- GUNAKAN orderFormErrors
        alert('Silakan lengkapi semua field yang wajib diisi!');
        return;
    }

    try {
        if (editingOrderItem.value) {
            const itemRef = dbRef(db, `orders/${editingOrderItem.value.id}`);
            await update(itemRef, { ...orderForm.value }); // <-- GUNAKAN orderForm
            alert('Order berhasil diperbarui di Firebase!');
        } else {
            await push(ordersRef, {
                ...orderForm.value, // <-- GUNAKAN orderForm
                created_at: new Date().toISOString(),
            });
            alert('Order berhasil ditambahkan ke Firebase!');
        }
        closeOrderForm();
    } catch (e) {
        console.error('Error saving order to Firebase:', e);
        orderError.value = 'Gagal menyimpan order ke Firebase.';
    }
};

const deleteOrder = async (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus order ini dari Firebase?')) {
        try {
            const itemRef = dbRef(db, `orders/${id}`);
            await remove(itemRef);
            alert('Order berhasil dihapus dari Firebase!');
        } catch (e) {
            console.error('Error deleting order from Firebase:', e);
            orderError.value = 'Gagal menghapus order dari Firebase.';
        }
    }
};

// --- State dan Logika untuk Master List Teknisi ---
const masterTeknisiList = ref([]);
const masterTeknisiLoading = ref(true);
const masterTeknisiError = ref(null);

const masterTeknisiRef = dbRef(db, 'masterTeknisi');

const fetchMasterTeknisi = () => {
    onValue(masterTeknisiRef, (snapshot) => {
        const data = snapshot.val();
        const loadedTeknisi = [];
        if (data) {
            // Asumsi data di masterTeknisi adalah objek { id: { nama: "..." }}
            for (let id in data) {
                loadedTeknisi.push(data[id].nama); // Ambil hanya nilai 'nama'
            }
        }
        masterTeknisiList.value = loadedTeknisi.sort(); // Urutkan alfabetis
        masterTeknisiLoading.value = false;
    }, (err) => {
        masterTeknisiError.value = 'Gagal memuat daftar master teknisi.';
        console.error('Error fetching master teknisi from Firebase:', err);
        masterTeknisiLoading.value = false;
    });
};

onMounted(() => {
    fetchOrders(); // Panggil fetchOrders
    fetchSchedules(); // Panggil fetchSchedules
    fetchMasterTeknisi(); // <-- Panggil fungsi ini saat komponen dimuat
});


// --- State dan Logika BARU untuk Master List Order ID ---
const masterOrderIdsList = ref([]);
const masterOrderIdsLoading = ref(true);
const masterOrderIdsError = ref(null);

const masterOrderIdsRef = dbRef(db, 'masterOrderIds'); // Referensi ke node 'masterOrderIds'

const fetchMasterOrderIds = () => {
    onValue(masterOrderIdsRef, (snapshot) => {
        const data = snapshot.val();
        const loadedOrderIds = [];
        if (data) {
            // Asumsi data di masterOrderIds adalah objek { id: { id: "..." }}
            for (let key in data) {
                if (data[key] && data[key].id) { // Cek jika objek ada dan punya properti 'id'
                    loadedOrderIds.push(data[key].id); // Ambil hanya nilai 'id'
                } else {
                    console.warn("Entri masterOrderIds tidak memiliki properti 'id' atau bernilai kosong:", data[key]);
                }
            }
        }
        masterOrderIdsList.value = loadedOrderIds.sort(); // Urutkan alfabetis
        masterOrderIdsLoading.value = false;
    }, (err) => {
        masterOrderIdsError.value = 'Gagal memuat daftar master Order ID.';
        console.error('Error fetching master Order IDs from Firebase:', err);
        masterOrderIdsLoading.value = false;
    });
};

onMounted(() => {
    fetchOrders();
    fetchSchedules();
    fetchMasterTeknisi();
    fetchMasterOrderIds(); // <-- Panggil fungsi ini saat komponen dimuat
});


// --- FUNGSI BARU UNTUK FORMAT TANGGAL ---
const formatDisplayDate = (isoString) => {
    if (!isoString) return '';
    try {
        // Coba parsing sebagai YYYY-MM-DD dulu, jika gagal baru ISO
        let date = new Date(isoString);
        if (isNaN(date.getTime())) { // Cek jika parsing gagal
            // Jika disimpan sebagai YYYY-MM-DD tanpa T, parsing bisa bermasalah di beberapa browser
            // Coba parsing manual
            const parts = isoString.split('-');
            if (parts.length === 3) {
                date = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
            } else {
                return isoString; // Kembalikan string asli jika format tidak dikenal
            }
        }
        
        // Pastikan tanggal valid
        if (isNaN(date.getTime())) {
            return isoString; // Kembalikan string asli jika tetap tidak valid
        }

        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Bulan dimulai dari 0
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    } catch (e) {
        console.error('Error formatting date:', e);
        return isoString; // Kembalikan string asli jika ada error
    }
};

// --- State dan Logika untuk JADWAL TEKNISI ---
const schedules = ref([]);
const scheduleLoading = ref(true);
const scheduleError = ref(null);
const showScheduleForm = ref(false); // <-- Variabel visibility modal Jadwal Teknisi
const editingScheduleItem = ref(null);
const scheduleForm = ref({ nama: '', jadwal: 'MASUK' }); // <-- Variabel form untuk Jadwal Teknisi
const scheduleFormErrors = ref({}); // <-- Variabel error form untuk Jadwal Teknisi

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

onMounted(fetchSchedules);

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

// --- Computed Property untuk dropdown Teknisi di form Order ---
const uniqueTeknisiListFromOrders = computed(() => {
    const allNames = orders.value.map(order => order.teknisi);
    const uniqueNames = [...new Set(allNames)];
    return uniqueNames.filter(name => name && name.trim() !== '').sort();
});

</script>

<style scoped>
.last\:border-r-0:last-child {
    border-right-width: 0;
}
</style>