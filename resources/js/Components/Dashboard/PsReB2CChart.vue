<!-- <template>
    <div class="p-4 bg-green-100 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-green-800 mb-4">PS/RE B2C</h3>
        <p class="text-red-600">Komponen PS/RE B2C Chart belum tersedia.</p>
        </div>
</template>

<script setup>
// Logika Anda untuk mengambil data dan menampilkan grafik akan di sini
</script> -->

<template>
  <div class="p-4 bg-green-100 rounded-lg shadow-md">
    <h3 class="text-lg font-semibold text-green-800 mb-4">PS/RE B2C Performance Harian</h3>

    <p v-if="loading" class="status-message">Memuat data order...</p>
    <p v-else-if="error" class="error-message">{{ error }}</p>

    <div v-else class="chart-section">
      <div v-if="hasChartData" class="chart-wrapper">
        <Line
          id="ps-re-combo-chart"
          :options="chartOptions"
          :data="chartData"
        />
      </div>
      <p v-else class="text-center text-gray-600">Tidak ada data RE/PS yang cukup untuk membuat chart.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { getDatabase, ref as dbRef, onValue } from 'firebase/database';
import { Line } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  BarElement,
  CategoryScale,
  LinearScale,
  LineController,
  BarController
} from 'chart.js';
import ChartDataLabels from 'chartjs-plugin-datalabels'; // Import plugin datalabels

// Mendaftarkan semua komponen dan skala yang dibutuhkan Chart.js
ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  BarElement,
  CategoryScale,
  LinearScale,
  LineController,
  BarController,
  ChartDataLabels // Daftarkan plugin datalabels
);

// Pastikan Anda telah menginisialisasi 'db' di file '@firebase' Anda,
// atau import `db` langsung jika sudah diekspor dari sana.
import { db } from '@/firebase'; // SESUAIKAN JALUR INI

const orders = ref([]); // Hanya perlu orders data
const loading = ref(true);
const error = ref(null);
const selectedTeknisi = ref('');

const ordersRef = dbRef(db, 'orders');

// Status-status yang termasuk dalam perhitungan RE (sekarang termasuk PS)
const RE_STATUSES_FOR_TOTAL = ['CANCEL', 'PROSET', 'OGP SURVEY', 'MANJA', 'KENDALA PELANGGAN', 'KENDALA TEKNIK', 'PS'];
const PS_STATUS = 'PS'; // Status spesifik untuk PS

// Ambil data orders dari Firebase
const fetchOrders = () => {
  loading.value = true;
  error.value = null;
  onValue(
    ordersRef,
    (snapshot) => {
      const data = snapshot.val();
      orders.value = data ? Object.entries(data).map(([id, item]) => ({ id, ...item })) : [];
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

// Computed property untuk memfilter data order berdasarkan teknisi yang dipilih
const filteredOrders = computed(() => {
  if (!selectedTeknisi.value) {
    return orders.value;
  }
  return orders.value.filter(
    (order) => order.teknisi === selectedTeknisi.value
  );
});

// Computed property untuk mengagregasi data RE dan PS per tanggal dari data orders
const aggregatedDataForChart = computed(() => {
  const dataMap = new Map(); // Key: YYYY-MM-DD, Value: { RE: N, PS: M }

  filteredOrders.value.forEach(order => {
    const dateKey = order.order_date; // Menggunakan order_date sebagai kunci

    if (dateKey) {
      if (!dataMap.has(dateKey)) {
        dataMap.set(dateKey, { RE: 0, PS: 0 });
      }
      const current = dataMap.get(dateKey);

      // Hitung RE: jika status order termasuk dalam daftar status yang relevan untuk RE (termasuk PS)
      if (RE_STATUSES_FOR_TOTAL.includes(order.status_order)) {
        current.RE++;
      }
      
      // Hitung PS: jika status order secara spesifik adalah PS_STATUS
      // Ini tetap menggunakan `if` terpisah karena PS dihitung sendiri dan juga termasuk dalam RE
      if (order.status_order === PS_STATUS) {
        current.PS++;
      }
      // Status lain yang tidak relevan untuk RE atau PS diabaikan
    }
  });

  // Konversi Map ke array dan urutkan berdasarkan tanggal (YYYY-MM-DD)
  const sortedDates = Array.from(dataMap.keys()).sort();

  const labels = [];
  const reData = [];
  const psData = [];
  const formattedLabels = [];

  sortedDates.forEach(date => {
    const data = dataMap.get(date);
    labels.push(date); // YYYY-MM-DD
    reData.push(data.RE);
    psData.push(data.PS);
    formattedLabels.push(formatChartDate(date)); // Untuk tampilan (e.g., 1 Jul)
  });

  return { labels, formattedLabels, reData, psData };
});


// Fungsi untuk format tanggal agar sesuai tampilan chart (e.g., "1 Jul")
const formatChartDate = (dateString) => {
  const date = new Date(dateString + 'T00:00:00'); // Tambahkan T00:00:00 untuk menghindari masalah zona waktu
  if (isNaN(date.getTime())) {
    return dateString;
  }
  const day = date.getDate();
  const monthNames = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
  const month = monthNames[date.getMonth()];
  return `${day} ${month}`;
};

// Computed property untuk memformat data agar siap digunakan oleh Chart.js
const chartData = computed(() => {
  return {
    labels: aggregatedDataForChart.value.formattedLabels,
    datasets: [
      {
        type: 'line', // Dataset untuk RE (garis)
        label: 'RE',
        borderColor: '#007bff', // Warna biru untuk garis RE
        backgroundColor: 'rgba(0, 123, 255, 0.2)', // Area di bawah garis (opsional)
        data: aggregatedDataForChart.value.reData,
        fill: false, // Jangan mengisi area di bawah garis
        tension: 0.4, // Untuk garis melengkung
        pointRadius: 5, // Ukuran titik pada garis
        pointBackgroundColor: '#007bff',
        yAxisID: 'y', // Menggunakan sumbu Y utama
        datalabels: {
          align: 'end',
          anchor: 'end',
          offset: 4,
          color: '#007bff',
          font: { weight: 'bold' },
          formatter: function(value) { return value > 0 ? value : ''; } // Hanya tampilkan label jika nilai > 0
        }
      },
      {
        type: 'bar', // Dataset untuk PS (bar)
        label: 'PS',
        backgroundColor: '#17a2b8', // Warna teal untuk bar PS
        data: aggregatedDataForChart.value.psData,
        yAxisID: 'y',
        datalabels: {
          align: 'end',
          anchor: 'end',
          offset: 4,
          color: '#17a2b8',
          font: { weight: 'bold' },
          formatter: function(value) { return value > 0 ? value : ''; } // Hanya tampilkan label jika nilai > 0
        }
      }
    ]
  };
});

// Opsi konfigurasi untuk chart kombinasi
const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    title: { display: false },
    legend: {
      display: true,
      position: 'top', // Tetap di bagian atas chart
      align: 'start', 
      labels: { usePointStyle: true,
        marginBottom: 15,
       }
    },
    tooltip: {
      mode: 'index',
      intersect: false,
      callbacks: {
        label: function(context) {
          let label = context.dataset.label || '';
          if (label) { label += ': '; }
          if (context.parsed.y !== null) { label += context.parsed.y; }
          return label;
        }
      }
    },
    datalabels: {
      display: true
    }
  },
  scales: {
    x: {
      title: { display: false },
      grid: { display: false }
    },
    y: {
      beginAtZero: true,
      title: { display: false },
      grid: {
        borderDash: [5, 5],
        color: '#e0e0e0'
      },
      ticks: { precision: 0 }
    }
  }
}));

// Computed property untuk mengecek apakah ada data untuk chart
const hasChartData = computed(() => {
  return aggregatedDataForChart.value.labels.length > 0 &&
         (aggregatedDataForChart.value.reData.some(val => val > 0) ||
          aggregatedDataForChart.value.psData.some(val => val > 0));
});

// Debugging logs
console.log('--- Chart Debug Info (PS/RE from Orders) ---');
console.log('orders (raw):', orders.value);
console.log('filteredOrders:', filteredOrders.value);
console.log('aggregatedDataForChart:', aggregatedDataForChart.value);
console.log('chartData:', chartData.value);
console.log('hasChartData:', hasChartData.value);
console.log('-------------------------------------------');

</script>

<style scoped>
.ps-re-chart-container {
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

.chart-wrapper {
  position: relative;
  height: 350px; /* Atur tinggi chart sesuai keinginan */
  width: 100%;
}
</style>