<template>
    <div class="p-4 bg-white rounded-lg shadow-md border border-gray-200">
        <div v-if="dataType === 'orders'" class="flex items-center mb-4 space-x-4">
            <div class="relative">
                <input type="text" v-model="filterScId" @input="debounceProcessData"
                        placeholder="SC ID Masukkan nilai"
                        class="block w-full bg-white border border-gray-300 hover:border-gray-400 px-4 py-2 rounded shadow leading-tight focus:outline-none focus:shadow-outline text-gray-700" />
            </div>

            <div class="relative inline-block text-left">
                <select v-model="selectedStatusOrder" @change="processAndSetMainData" 
                        class="block appearance-none w-full bg-white border border-gray-300 hover:border-gray-400 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline text-gray-700">
                    <option value="">STATUS ORDER</option>
                    <option v-for="status in statusOrderList" :key="status" :value="status">{{ status }}</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"></svg>
                </div>
            </div>

            <button @click="resetFilters"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                RESET FILTER
            </button>
        </div>

        <p v-if="loading" class="text-center text-gray-600">Memuat data...</p>
        <p v-else-if="error" class="text-center text-red-600">{{ error }}</p>
        
        <table v-else-if="paginatedData.length > 0" class="min-w-full bg-white border border-gray-300">
            <thead>
                <tr>
                    <th v-for="(header, index) in currentHeaders" :key="index"
                        class="py-2 px-4 bg-orange-600 text-white font-semibold text-center border-b border-r border-orange-700 last:border-r-0">
                        {{ header.label }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(row, rowIndex) in paginatedData" :key="rowIndex" class="border-b border-gray-300 last:border-b-0">
                    <td v-for="(header, colIndex) in currentHeaders" :key="colIndex"
                        class="py-2 px-4 text-center border-r border-gray-300 last:border-r-0">
                        {{ getNestedProperty(row, header.key) }}
                    </td>
                </tr>
                <tr v-if="dataType === 'orders' && totalRow" class="font-bold bg-gray-200 border-t-2 border-gray-400">
                    <td class="py-2 px-4 text-center border-r border-gray-300">{{ totalRow.label }}</td>
                    <td v-for="(value, index) in totalRow.values" :key="index"
                        class="py-2 px-4 text-center border-r border-gray-300 last:border-r-0">
                        {{ value }}
                    </td>
                </tr>
            </tbody>
        </table>
        <p v-else class="text-center text-gray-600">Tidak ada data ditemukan.</p>

        <div v-if="totalDataCount > itemsPerPage" class="flex justify-end items-center mt-4 text-sm text-gray-600">
            <span>{{ startIndex + 1 }} - {{ endIndex }} / {{ totalDataCount }}</span>
            <button @click="prevPage" :disabled="currentPage === 1"
                    class="ml-4 px-2 py-1 border rounded disabled:opacity-50">
                &lt;
            </button>
            <button @click="nextPage" :disabled="currentPage * itemsPerPage >= totalDataCount"
                        class="ml-2 px-2 py-1 border rounded disabled:outline-none disabled:opacity-50">
                &gt;
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch, defineProps } from 'vue'; 
import { db } from '@/firebase';
import { ref as dbRef, onValue } from 'firebase/database';

// --- PROPS ---
const props = defineProps({
    dataType: {
        type: String,
        required: true,
        validator: (value) => ['orders', 'funnelingData', 'falloutData', 'psReB2cData'].includes(value)
    }
});
// --- AKHIR PROPS ---

const rawAllData = ref([]); 
const mainDataDisplay = ref([]); 
const totalRow = ref(null);
const loading = ref(true);
const error = ref(null);

// Filter state (hanya untuk orders)
const filterScId = ref('');
// Perubahan: filterStatusOrder menjadi selectedStatusOrder
const selectedStatusOrder = ref(''); 
const statusOrderList = ref([]);
const selectedTeknisi = ref('');
const teknisiList = ref([]); // Untuk filter teknisi di order

const currentPage = ref(1);
const itemsPerPage = 10;
const totalDataCount = ref(0);

// Referensi ke masterTeknisi untuk filter
const masterTeknisiRef = dbRef(db, 'masterTeknisi'); 
const masterTeknisiList = ref([]);

// --- Definisi Headers dan Kolom Mapping Dinamis ---
const dataConfigs = {
    orders: {
        headers: [
            { label: 'SC ID', key: 'sc_id' },
            { label: 'Order Date', key: 'order_date', format: 'date' },
            { label: 'Order ID', key: 'order_id' },
            { label: 'Teknisi', key: 'teknisi' },
            { label: 'Status Order', key: 'status_order' },
            { label: 'Keterangan', key: 'keterangan' }
        ],
        dbNode: 'orders',
        calculateTotals: (data) => {
            const totalRE = data.reduce((sum, item) => sum + (item.re || 0), 0);
            const totalCancel = data.reduce((sum, item) => sum + (item.cancel || 0), 0);
            const totalPSRE = data.reduce((sum, item) => sum + (item.ps_re || 0), 0);
        }
    },
    funnelingData: {
        headers: [
            { label: 'RE', key: 'RE' }, 
            { label: 'CANCEL', key: 'CANCEL' },
            { label: 'PI', key: 'PI' },
            { label: 'PROGRESS', key: 'PROGRESS' },
            { label: 'FALLOUT', key: 'FALLOUT' },
            { label: 'PS', key: 'PS' }
        ],
        dbNode: 'funnelingData',
        isSingleton: true // Untuk data yang hanya 1 baris objek (bukan array of objects)
    },
    falloutData: {
        headers: [
            { label: 'Kategori', key: 'category' },
            { label: 'Jumlah', key: 'count' }
        ],
        dbNode: 'falloutData'
    },
    psReB2cData: {
        headers: [
            { label: 'Tanggal', key: 'date', format: 'date' },
            { label: 'PS Value', key: 'ps_value' },
            { label: 'RE Value', key: 're_value' }
        ],
        dbNode: 'psReB2cData'
    }
};

// Computed property untuk header saat ini
const currentHeaders = computed(() => {
    return dataConfigs[props.dataType]?.headers || [];
});

// Fungsi untuk mendapatkan properti bersarang (misal: 'tanggal.hari')
const getNestedProperty = (obj, key) => {
    if (key.includes('.')) {
        return key.split('.').reduce((acc, part) => acc && acc[part], obj);
    }
    // Handle specific formatting for dates
    if (currentHeaders.value.find(h => h.key === key && h.format === 'date')) {
        return formatDisplayDate(obj[key]);
    }
    return obj[key];
};

// ... (fungsi formatDisplayDate, debounceProcessData, resetFilters) ...

// Fetch master teknisi untuk filter (hanya dipanggil sekali)
const fetchMasterTeknisiForFilter = () => {
    onValue(masterTeknisiRef, (snapshot) => {
        const data = snapshot.val();
        const loadedTeknisi = [];
        if (data) {
            for (let id in data) {
                if (data[id] && data[id].nama) {
                    loadedTeknisi.push(data[id].nama);
                }
            }
        }
        teknisiList.value = ['', ...loadedTeknisi.sort()];
    }, (err) => {
        console.error('Error fetching master teknisi for filter from Firebase:', err);
    });
};


// Fungsi utama untuk mengambil data dari Firebase Realtime Database
const fetchData = () => { 
    loading.value = true;
    error.value = null;

    const config = dataConfigs[props.dataType];
    if (!config) {
        error.value = `Konfigurasi data untuk tipe '${props.dataType}' tidak ditemukan.`;
        loading.value = false;
        return;
    }

    const currentDataRef = dbRef(db, config.dbNode);

    onValue(currentDataRef, (snapshot) => {
        const data = snapshot.val();
        let loadedData = [];

        if (data) {
            if (config.isSingleton) { // Untuk data seperti funnelingData (1 objek saja)
                loadedData.push(data); // Push objek itu sendiri sebagai 1 baris
            } else { // Untuk data array of objects
                for (let id in data) {
                    loadedData.push({ id: id, ...data[id] });
                }
            }
        }
        rawAllData.value = loadedData; 
        processAndSetMainData(); // Lalu proses data ini
        error.value = null;
    }, (err) => {
        error.value = `Gagal memuat data ${props.dataType} dari Firebase.`;
        console.error(`Error fetching ${props.dataType} data from Firebase:`, err);
        loading.value = false;
    });
};

// Fungsi untuk memproses dan mengatur data (filter, total, paginasi)
const processAndSetMainData = () => {
    loading.value = true;
    error.value = null; 

    let filteredData = rawAllData.value; 

    // --- Filter Data (Hanya untuk orders) ---
    if (props.dataType === 'orders') {
        if (filterScId.value) {
            filteredData = filteredData.filter(item => item.sc_id && String(item.sc_id).toLowerCase().includes(filterScId.value.toLowerCase()));
        }
        // Perubahan: Menggunakan selectedStatusOrder untuk filter
        if (selectedStatusOrder.value) { 
            filteredData = filteredData.filter(item => item.status_order && String(item.status_order).toLowerCase() === selectedStatusOrder.value.toLowerCase());
        }
        if (selectedTeknisi.value) {
            filteredData = filteredData.filter(item => item.teknisi && String(item.teknisi).toLowerCase() === selectedTeknisi.value.toLowerCase());
        }

        // Ini mengisi statusOrderList dari data yang sudah difilter atau semua data
        // Agar sesuai dengan opsi yang tersedia di dropdown
        const uniqueStatuses = new Set(filteredData.map(item => item.status_order).filter(Boolean));
        statusOrderList.value = Array.from(uniqueStatuses).sort(); // Hapus baris ['', ...] jika Anda tidak ingin opsi kosong di dropdown filter
    }
    // --- Akhir Filter Data ---

    // --- Logika untuk Baris "Total Keseluruhan" (Hanya untuk orders) ---
    // if (props.dataType === 'orders' && dataConfigs.orders.calculateTotals) {
    //     totalRow.value = dataConfigs.orders.calculateTotals(filteredData);
    // } else {
    //     totalRow.value = null;
    // }
    
    totalDataCount.value = filteredData.length;
    currentPage.value = 1; 
    mainDataDisplay.value = filteredData; 
    loading.value = false;
};

// Pagination computed properties
const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return mainDataDisplay.value.slice(start, end);
});
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage);
const endIndex = computed(() => Math.min(currentPage.value * itemsPerPage, totalDataCount.value));

// Pagination methods
const nextPage = () => {
    if (currentPage.value * itemsPerPage < totalDataCount.value) {
        currentPage.value++;
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

// Watch dataType prop untuk fetch ulang data
watch(() => props.dataType, (newDataType) => {
    fetchData(); // Panggil fetchData setiap kali dataType berubah
}, { immediate: true }); // Panggil saat inisialisasi

// Dipanggil hanya sekali untuk filter Teknisi (untuk Order)
onMounted(() => {
    if (props.dataType === 'orders') { // Hanya fetch master teknisi jika tipe datanya order
        fetchMasterTeknisiForFilter();
    }
});


// --- FUNGSI BARU UNTUK FORMAT TANGGAL ---
const formatDisplayDate = (isoString) => {
    if (!isoString) return '';
    try {
        let date = new Date(isoString);
        if (isNaN(date.getTime())) { 
            const parts = isoString.split('-');
            if (parts.length === 3) {
                date = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
            } else {
                return isoString; 
            }
        }
        
        if (isNaN(date.getTime())) {
            return isoString; 
        }

        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0'); 
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    } catch (e) {
        console.error('Error formatting date:', e);
        return isoString; 
    }
};

// Fungsi debounce (tetap seperti sebelumnya, tidak diubah)
let debounceTimeout = null;
const debounceProcessData = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    processAndSetMainData();
  }, 300); // Debounce for 300ms
};

// Fungsi resetFilters (tetap seperti sebelumnya, tidak diubah)
const resetFilters = () => {
  filterScId.value = '';
  selectedStatusOrder.value = ''; // Menggunakan selectedStatusOrder
  selectedTeknisi.value = '';
  processAndSetMainData();
};


</script>

<style scoped>
/* Tambahan untuk menghilangkan border kanan pada kolom terakhir */
th.last\:border-r-0:last-child,
td.last\:border-r-0:last-child {
    border-right-width: 0;
}
</style>