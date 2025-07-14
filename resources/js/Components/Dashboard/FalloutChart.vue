<!-- <template>
    <div class="p-4 bg-red-100 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-red-800 mb-4">FALLOUT</h3>
        <p v-if="loading" class="text-red-600">Memuat data...</p>
        <p v-else-if="error" class="text-red-600">{{ error }}</p>
        <Bar :data="chartData" :options="chartOptions" v-else />
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const rawData = ref([]);
const loading = ref(true);
const error = ref(null);

// Ambil data dari API
onMounted(async () => {
    try {
        const response = await axios.get('/api/dashboard-data/fallout');
        rawData.value = response.data; // Asumsi ini adalah array of objects dari GSheet
    } catch (err) {
        error.value = 'Gagal memuat data fallout.';
        console.error('Error fetching fallout data:', err);
    } finally {
        loading.value = false;
    }
});

// Ubah rawData menjadi format yang bisa dibaca Chart.js
const chartData = computed(() => {
    const labels = rawData.value.map(row => row[0]); // Asumsi kolom pertama adalah label
    const dataValues = rawData.value.map(row => row[1]); // Asumsi kolom kedua adalah nilai

    return {
        labels: labels,
        datasets: [
            {
                label: 'Jumlah Fallout',
                backgroundColor: '#ef4444', // Tailwind red-500
                data: dataValues,
            }
        ]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: {
            beginAtZero: true
        }
    }
};
</script> -->


<template>
  <div class="p-4 bg-red-100 rounded-lg shadow-md">
    <h3 class="text-lg font-semibold text-red-800 mb-4">STATISTIK DATA FALLOUT</h3>

    <p v-if="loading" class="status-message">Memuat data order...</p>
    <p v-else-if="error" class="error-message">{{ error }}</p>

    <div v-else class="chart-section">      
      <div v-if="hasFalloutData" class="chart-wrapper">
        <Bar
          id="fallout-chart"
          :options="chartOptions"
          :data="chartData"
        />
      </div>
      <p v-else class="text-center text-gray-600">Tidak ada data fallout ditemukan untuk filter ini.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { getDatabase, ref as dbRef, onValue } from 'firebase/database';
import { Bar } from 'vue-chartjs'; // Import komponen Bar chart
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';

// Mendaftarkan komponen dan skala yang dibutuhkan Chart.js
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

import { db } from '@/firebase'; // SESUAIKAN JALUR INI

const orders = ref([]);
const loading = ref(true);
const error = ref(null);
const selectedTeknisi = ref('');

// Definisi status yang dianggap 'fallout' untuk chart
const FALLOUT_CHART_STATUSES = ['KENDALA TEKNIK', 'MANJA', 'KENDALA PELANGGAN'];

// Referensi ke node 'orders' di Firebase
const ordersRef = dbRef(db, 'orders');

// Ambil data orders dari Firebase
const fetchOrders = () => {
  loading.value = true;
  error.value = null;
  onValue(
    ordersRef,
    (snapshot) => {
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
      loading.value = false;
    },
    (err) => {
      error.value = 'Gagal memuat data order dari Firebase.';
      console.error(err);
      loading.value = false;
    }
  );
};

onMounted(() => {
  fetchOrders();
});

// Computed property untuk mendapatkan nama teknisi unik untuk dropdown filter
const uniqueTeknisiNames = computed(() => {
  const names = new Set();
  orders.value.forEach(order => {
    if (order.teknisi) {
      names.add(order.teknisi);
    }
  });
  return Array.from(names).sort();
});

// Computed property untuk memfilter order yang termasuk 'fallout' dan berdasarkan teknisi
const filteredFalloutOrders = computed(() => {
  let filtered = orders.value.filter(order => {
    return FALLOUT_CHART_STATUSES.includes(order.status_order);
  });

  if (selectedTeknisi.value) {
    filtered = filtered.filter(order => order.teknisi === selectedTeknisi.value);
  }

  return filtered;
});

// Computed property untuk mengagregasi data untuk chart
const falloutChartData = computed(() => {
  const counts = {};
  FALLOUT_CHART_STATUSES.forEach(status => {
    counts[status] = 0; // Inisialisasi hitungan untuk setiap status
  });

  filteredFalloutOrders.value.forEach(order => {
    if (counts.hasOwnProperty(order.status_order)) {
      counts[order.status_order]++;
    }
  });

  // Urutkan data sesuai urutan yang Anda inginkan (misal: tertinggi ke terendah, atau sesuai definisi)
  // Untuk mencocokkan gambar, kita akan menjaga urutan eksplisit di FALLOUT_CHART_STATUSES
  const labels = FALLOUT_CHART_STATUSES;
  const data = labels.map(label => counts[label]);

  return { labels, data };
});

// Computed property untuk memformat data agar siap digunakan oleh Chart.js
const chartData = computed(() => {
  return {
    labels: falloutChartData.value.labels,
    datasets: [
      {
        label: 'Jumlah Order',
        backgroundColor: '#007bff', // Warna biru seperti pada gambar
        data: falloutChartData.value.data,
      }
    ]
  };
});

// Opsi konfigurasi untuk chart (membuatnya menjadi horizontal bar chart)
const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false, // Penting untuk mengontrol ukuran chart
  indexAxis: 'y', // Ini yang membuat bar chart menjadi horizontal
  plugins: {
    legend: {
      display: false, // Sembunyikan legenda
    },
    title: {
      display: false, // Sembunyikan judul bawaan chart.js karena kita sudah punya h3
    },
    tooltip: {
      callbacks: {
        label: function(context) {
          let label = context.dataset.label || '';
          if (label) {
            label += ': ';
          }
          if (context.parsed.x !== null) {
            label += context.parsed.x;
          }
          return label;
        }
      }
    }
  },
  scales: {
    x: {
      beginAtZero: true,
      title: {
        display: false, // Sembunyikan judul sumbu X
      },
      ticks: {
        precision: 0 // Pastikan label sumbu X adalah bilangan bulat
      }
    },
    y: {
      title: {
        display: false, // Sembunyikan judul sumbu Y
      },
    }
  }
}));

// Computed property untuk mengecek apakah ada data fallout untuk ditampilkan
const hasFalloutData = computed(() => falloutChartData.value.data.some(count => count > 0));

</script>

<style scoped>
.fallout-container {
  padding: 20px;
  font-family: sans-serif;
  background-color: #f0f2f5;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

h1 {
  color: #333;
  text-align: center;
  margin-bottom: 25px;
}

.controls {
  text-align: center;
  margin-bottom: 30px;
}

.controls label {
  margin-right: 10px;
  font-weight: bold;
  color: #555;
}

.controls select {
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 5px;
  background-color: white;
  font-size: 1rem;
  cursor: pointer;
}

.status-message {
  text-align: center;
  color: #666;
  font-style: italic;
  margin-top: 20px;
}

.error-message {
  text-align: center;
  color: #dc3545;
  font-weight: bold;
  margin-top: 20px;
}

.chart-section {
  margin-top: 30px;
  background-color: #ffffff;
  padding: 25px;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

.chart-section h3 {
  color: #444;
  text-align: center;
  margin-bottom: 20px;
}

.chart-wrapper {
  position: relative; /* Penting untuk maintainAspectRatio */
  height: 250px; /* Atur tinggi chart sesuai keinginan Anda */
  width: 100%;
}
/* Style untuk tabel yang tidak terpakai lagi di Fallout.vue ini,
   tapi saya biarkan jika Anda ingin merujuknya
*/
/*
table { ... }
table th, td { ... }
table thead th { ... }
table tbody tr:nth-child(even) { ... }
table tbody tr:hover { ... }
.last\:border-r-0:last-child { ... }
*/
</style>