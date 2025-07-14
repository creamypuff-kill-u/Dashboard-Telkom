<!-- resources/js/Components/Dashboard/teknisiPerformanceData.vue -->
<template>
  <div class="p-4 bg-white rounded-lg shadow-md border border-gray-200">
    <!-- Dropdown Filter -->
    <div class="flex items-center mb-4">
      <select v-model="selectedTeknisi" class="block w-64 px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:border-blue-300">
        <option value="">TEKNISI</option>
        <option v-for="teknisi in teknisiList" :key="teknisi" :value="teknisi">{{ teknisi }}</option>
      </select>
    </div>

    <!-- Loading / Error -->
    <p v-if="loading" class="text-center text-gray-500">Memuat data teknisi...</p>
    <p v-if="error" class="text-center text-red-600">{{ error }}</p>

    <!-- Table -->
    <div v-if="!loading && filteredData.length > 0" class="overflow-auto">
      <table class="min-w-full border border-gray-300 text-sm">
        <thead>
          <tr class="bg-red-700 text-white">
            <th class="px-3 py-2 border text-center">NO</th>
            <th class="px-3 py-2 border text-left">TEKNISI</th>
            <th class="px-3 py-2 border text-center">RE</th>
            <th class="px-3 py-2 border text-center">CANCEL</th>
            <th class="px-3 py-2 border text-center">FALLOUT</th>
            <th class="px-3 py-2 border text-center">OGP SURVEY</th>
            <th class="px-3 py-2 border text-center">OGP TARIK</th>
            <th class="px-3 py-2 border text-center">PROSET</th>
            <th class="px-3 py-2 border text-center">PS</th>
            <th class="px-3 py-2 border text-center">PS/RE</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in filteredData" :key="row.id" class="hover:bg-gray-50">
            <td class="px-3 py-2 border text-center">{{ index + 1 }}</td>
            <td class="px-3 py-2 border text-left">{{ row.teknisi }}</td>
            <td class="px-3 py-2 border text-center">{{ row.RE }}</td>
            <td class="px-3 py-2 border text-center">{{ row.CANCEL }}</td>
            <td class="px-3 py-2 border text-center">{{ row.FALLOUT }}</td>
            <td class="px-3 py-2 border text-center">{{ row.OGP_SURVEY }}</td>
            <td class="px-3 py-2 border text-center">{{ row.OGP_TARIK }}</td>
            <td class="px-3 py-2 border text-center">{{ row.PROSET }}</td>
            <td class="px-3 py-2 border text-center">{{ row.PS }}</td>
            <td class="px-3 py-2 border text-center">{{ row.PS_RE }}</td>
          </tr>
          <tr class="bg-gray-100 font-bold">
            <td class="px-3 py-2 border text-center" colspan="2">Total Keseluruhan</td>
            <td class="px-3 py-2 border text-center">{{ totalKeseluruhan.RE }}</td>
            <td class="px-3 py-2 border text-center">{{ totalKeseluruhan.CANCEL }}</td>
            <td class="px-3 py-2 border text-center">{{ totalKeseluruhan.FALLOUT }}</td>
            <td class="px-3 py-2 border text-center">{{ totalKeseluruhan.OGP_SURVEY }}</td>
            <td class="px-3 py-2 border text-center">{{ totalKeseluruhan.OGP_TARIK }}</td>
            <td class="px-3 py-2 border text-center">{{ totalKeseluruhan.PROSET }}</td>
            <td class="px-3 py-2 border text-center">{{ totalKeseluruhan.PS }}</td>
            <td class="px-3 py-2 border text-center">{{ totalKeseluruhan.PS_RE }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <p v-if="!loading && filteredData.length === 0" class="text-center text-gray-500 mt-4">Tidak ada data ditemukan.</p>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { db } from '@/firebase'
import { ref as dbRef, onValue } from 'firebase/database'

const loading = ref(true)
const error = ref(null)
const teknisiPerformance = ref([])
const selectedTeknisi = ref('')

// Ambil data dari Firebase
const fetchData = () => {
  const dbPath = dbRef(db, 'teknisiPerformanceData')
  onValue(
    dbPath,
    (snapshot) => {
      const data = snapshot.val()
      teknisiPerformance.value = data
        ? Object.entries(data).map(([id, item]) => ({ id, ...item }))
        : []
      loading.value = false
    },
    (err) => {
      error.value = 'Gagal memuat data.'
      console.error(err)
    }
  )
}

onMounted(() => {
  fetchData()
})

// Daftar teknisi unik
const teknisiList = computed(() => {
  const list = teknisiPerformance.value.map((d) => d.teknisi).filter(Boolean)
  return [...new Set(list)]
})

// Filter data berdasarkan dropdown
const filteredData = computed(() => {
  if (!selectedTeknisi.value) return teknisiPerformance.value
  return teknisiPerformance.value.filter(
    (d) => d.teknisi === selectedTeknisi.value
  )
})

const totalKeseluruhan = computed(() => {
  const totals = {
    RE: 0,
    CANCEL: 0,
    FALLOUT: 0,
    OGP_SURVEY: 0,
    OGP_TARIK: 0,
    PROSET: 0,
    PS: 0,
  }

  filteredData.value.forEach((row) => {
    for (const key in totals) {
      totals[key] += Number(row[key]) || 0
    }
  })

  // Hitung PS/RE (dalam persentase)
  const psRePercent =
    totals.RE > 0 ? ((totals.PS / totals.RE) * 100).toFixed(2) + '%' : '0%'

  return {
    ...totals,
    PS_RE: psRePercent,
  }
})

</script>
