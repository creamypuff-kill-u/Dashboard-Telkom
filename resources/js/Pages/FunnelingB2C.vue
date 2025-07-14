<template>
  <div class="p-4 bg-yellow-100 rounded-lg shadow-md">
    <h3 class="text-lg font-semibold text-yellow-800 mb-4">FUNNELING B2C PERFORMANCE</h3>

    <p v-if="loading" class="status-message">Memuat data...</p>
    <p v-if="error" class="error-message">{{ error }}</p>

    <div class="tree-chart-wrapper">
        <svg :width="svgWidth" :height="svgHeight">
          <g :transform="`translate(${margin.left}, ${margin.top})`">
            <template v-if="treeChartData">
              <FunnelingB2CNode
                :node="treeChartData"
                :x="chartCenter"
                :y="10"
                :parentX="chartCenter"
                :parentY="nodeHeight"
                :level="0"
                :nodeWidth="nodeWidth"
                :nodeHeight="nodeHeight"
                :horizontalSpacing="horizontalSpacing"
                :verticalSpacing="verticalSpacing"
              />
            </template>
          </g>
        </svg>
      </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { getDatabase, ref as dbRef, onValue } from 'firebase/database';
// PASTIKAN JALUR INI BENAR SESUAI DENGAN LOKASI FunnelingB2CNode.vue
// Karena mereka di folder yang sama (Dashboard), './' sudah benar.
import FunnelingB2CNode from '../Components/Dashboard/FunnelingB2CNode.vue';
// Inisialisasi Firebase (pastikan ini sudah dilakukan di main.js atau file konfigurasi Anda)
const db = getDatabase();

// State reaktif
const teknisiPerformance = ref([]);
const selectedTeknisi = ref('');
const loading = ref(true);
const error = ref(null);

// Konfigurasi dimensi dan spasi untuk diagram pohon
const nodeWidth = 120;
const nodeHeight = 60;
const horizontalSpacing = 10; // Jarak antar node horizontal di level yang sama
const verticalSpacing = 50;   // Jarak vertikal antar level
const margin = { top: 0, right: 20, bottom: 20, left: 0 };

// Fungsi untuk mengambil data dari Firebase
const fetchData = () => {
  loading.value = true;
  error.value = null;
  const dbPath = dbRef(db, 'teknisiPerformanceData');
  onValue(
    dbPath,
    (snapshot) => {
      const data = snapshot.val();
      teknisiPerformance.value = data
        ? Object.entries(data).map(([id, item]) => ({ id, ...item }))
        : [];
      loading.value = false;
    },
    (err) => {
      error.value = 'Gagal memuat data.';
      console.error(err);
      loading.value = false;
    }
  );
};

// Panggil fetchData saat komponen dimount
onMounted(() => {
  fetchData();
});

// Computed property untuk mendapatkan nama teknisi unik untuk dropdown filter
const uniqueTeknisiNames = computed(() => {
  const names = new Set();
  teknisiPerformance.value.forEach(item => {
    // Asumsikan setiap item memiliki properti 'teknisiName'
    if (item.teknisiName) {
      names.add(item.teknisiName);
    }
  });
  return Array.from(names);
});

// Computed property untuk memfilter data berdasarkan teknisi yang dipilih
const filteredData = computed(() => {
  if (!selectedTeknisi.value) {
    return teknisiPerformance.value;
  }
  // Asumsikan 'row.teknisiName' adalah properti yang digunakan untuk filtering
  return teknisiPerformance.value.filter(
    (row) => row.teknisiName === selectedTeknisi.value
  );
});

// Computed property untuk menghitung total keseluruhan kategori
const totalKeseluruhan = computed(() => {
  const totals = {
    RE: 0,
    CANCEL: 0,
    FALLOUT: 0,
    OGP_SURVEY: 0,
    OGP_TARIK: 0,
    PROSET: 0,
    PS: 0,
  };

  filteredData.value.forEach((row) => {
    for (const key in totals) {
      // Pastikan properti ada di row sebelum mengaksesnya
      if (Object.prototype.hasOwnProperty.call(row, key)) {
        totals[key] += Number(row[key]) || 0;
      }
    }
  });

  const psRePercent =
    totals.RE > 0 ? ((totals.PS / totals.RE) * 100).toFixed(2) + '%' : '0%';

  return {
    ...totals,
    PS_RE: psRePercent,
  };
});

// Computed property untuk menyiapkan data dalam struktur pohon untuk diagram SVG
const treeChartData = computed(() => {
  const totals = totalKeseluruhan.value;

  // Nilai PI (Progress in) dihitung dari anak-anaknya: FALLOUT dan PS
  // PROGRESS diasumsikan 0 karena tidak ada di `totalKeseluruhan`
  const PI_value = (totals.FALLOUT || 0) + (totals.PS || 0);

  return {
    name: 'RE',
    value: totals.RE || 0,
    color: '#DC3545', // Merah
    children: [
      {
        name: 'CANCEL',
        value: totals.CANCEL || 0,
        color: '#20C997' // Hijau Kebiruan
      },
      {
        name: 'PI', // Node perantara, nilai dihitung
        value: PI_value,
        color: '#FFC107', // Kuning
        children: [
          {
            name: 'PROGRESS',
            value: 0, // Tidak ada di totalKeseluruhan, diasumsikan 0
            color: '#007BFF' // Biru
          },
          {
            name: 'FALLOUT',
            value: totals.FALLOUT || 0,
            color: '#FD7E14', // Oranye
            // children: [] // Tambahkan ini jika Fallout bisa punya anak
          },
          {
            name: 'PS',
            value: totals.PS || 0,
            color: '#28A745', // Hijau
            // children: [] // Tambahkan ini jika PS bisa punya anak
          }
        ]
      }
    ]
  };
});

// Perhitungan dimensi SVG secara dinamis
const calculateDimensions = (node) => {
  let maxDepth = 0;
  const levels = new Map(); // Map<level, numNodesAtLevel>

  const countNodesAtLevel = (currentNode, depth) => {
    maxDepth = Math.max(maxDepth, depth); // Update maxDepth
    if (!levels.has(depth)) {
      levels.set(depth, 0);
    }
    levels.set(depth, levels.get(depth) + 1); // Hitung node ini

    if (currentNode.children && currentNode.children.length > 0) {
      currentNode.children.forEach(child => countNodesAtLevel(child, depth + 1));
    }
  };

  if (node) { // Pastikan node tidak null/undefined
    countNodesAtLevel(node, 0);
  }

  let maxContentWidth = 0;
  levels.forEach((numNodes, depth) => {
    // Lebar yang dibutuhkan untuk level ini
    const levelWidth = numNodes * nodeWidth + (numNodes - 1) * horizontalSpacing;
    maxContentWidth = Math.max(maxContentWidth, levelWidth);
  });

  return {
    width: maxContentWidth,
    height: (maxDepth + 1) * nodeHeight + maxDepth * verticalSpacing // Tinggi total: (jumlah level * tinggi node) + (jarak antar level * jumlah jarak)
  };
};

const dimensions = computed(() => calculateDimensions(treeChartData.value));

// Lebar SVG: lebar konten + margin kiri/kanan + sedikit padding ekstra
const svgWidth = computed(() => dimensions.value.width + margin.left + margin.right + 100);
// Tinggi SVG: tinggi konten + margin atas/bawah + sedikit padding ekstra
const svgHeight = computed(() => dimensions.value.height + margin.top + margin.bottom + 50);

// chartCenter adalah posisi X untuk node root agar terpusat dalam area gambar SVG yang efektif.
// Area efektif: svgWidth - margin.left - margin.right
const chartCenter = computed(() => {
  const effectiveDrawingWidth = svgWidth.value - margin.left - margin.right;
  return (effectiveDrawingWidth / 2) - (nodeWidth); // X untuk node RE
});

// Tambahkan console.log untuk membantu debugging di browser console
console.log('treeChartData:', treeChartData.value);
console.log('dimensions:', dimensions.value);
console.log('svgWidth:', svgWidth.value, 'svgHeight:', svgHeight.value);
console.log('chartCenter:', chartCenter.value);

</script>

<style scoped>
/* Hapus blok :root ini jika tidak ada penggunaan variabel CSS global lainnya.
   Warna sekarang langsung di set di treeChartData.
*/
/*
:root {
  --color-re: #DC3545;
  --color-cancel: #20C997;
  --color-pi: #FFC107;
  --color-progress: #007BFF;
  --color-fallout: #FD7E14;
  --color-ps: #28A745;
  --node-border-color: #333;
  --line-color: #ccc;
}
*/

.funneling-b2c-container {
  padding: 20px;
  font-family: sans-serif;
  background-color: #f0f2f5;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

h1, h2 {
  color: #333;
  text-align: center;
  margin-bottom: 20px;
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
  margin-top: 40px;
  background-color: #ffffff;
  padding: 25px;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

.totals-summary {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 20px;
  margin-bottom: 30px;
}

.total-item {
  background-color: #e9ecef;
  padding: 10px 15px;
  border-radius: 5px;
  font-size: 0.95rem;
  color: #333;
}

.chart-title {
  margin-top: 40px;
  margin-bottom: 25px;
  color: #444;
}

.tree-chart-wrapper {
  overflow-x: auto; /* Memungkinkan scroll horizontal jika diagram terlalu lebar */
  background-color: #f9f9f9;
  border-radius: 8px;
  padding: 10px;
  box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
  display: flex; /* Menggunakan flexbox untuk memusatkan SVG */
  justify-content: center; /* Memusatkan SVG secara horizontal */
  align-items: center; /* Memusatkan SVG secara vertikal */
  min-height: 200px; /* Minimal tinggi agar terlihat */
}

/* Style default untuk elemen SVG bisa diatur di sini jika tidak di komponen node */
svg {
  display: block; /* Menghilangkan spasi ekstra di bawah SVG */
  /* margin: 0 auto; -- Tidak perlu jika pakai flexbox di wrapper */
}
</style>