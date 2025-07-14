<template>
    <div class="p-4 bg-white rounded-lg shadow-md border border-gray-200">
        <h3 class="text-xl font-bold text-center text-gray-800 mb-4 pb-2 border-b border-gray-300">JADWAL TEKNISI</h3>

        <p v-if="loading" class="text-center text-gray-600">Memuat jadwal...</p>
        <p v-else-if="error" class="text-center text-red-600">{{ error }}</p>

        <div v-else>
            <table v-if="schedules.length > 0" class="min-w-full bg-white border border-gray-300">
                <thead>
                    <tr>
                        <th class="py-2 px-4 bg-teal-600 text-white font-semibold text-center border-b border-r border-teal-700 w-1/12">No.</th>
                        <th class="py-2 px-4 bg-teal-600 text-white font-semibold text-center border-b border-r border-teal-700 cursor-pointer">
                            <div class="flex items-center justify-center">NAMA</div>
                        </th>
                        <th class="py-2 px-4 bg-teal-600 text-white font-semibold text-center border-b border-teal-700">JADWAL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item) in schedules" :key="item.id" class="border-b border-gray-300 last:border-b-0">
                        <td class="py-2 px-4 text-center border-r border-gray-300">{{ item.index }}.</td>
                        <td class="py-2 px-4 border-r border-gray-300">{{ item.nama }}</td>
                        <td class="py-2 px-4 text-center">{{ item.jadwal }}</td>
                    </tr>
                </tbody>
            </table>
            <p v-else class="text-center text-gray-600">Tidak ada data jadwal teknisi.</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { db } from '@/firebase';
import { ref as dbRef, onValue } from 'firebase/database';

const schedules = ref([]);
const loading = ref(true);
const error = ref(null);

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
        loading.value = false;
    }, (err) => {
        error.value = 'Gagal memuat jadwal teknisi dari Firebase.';
        console.error('Error fetching schedules from Firebase:', err);
        loading.value = false;
    });
};

onMounted(fetchSchedules);
</script>

<style scoped>
th.last\:border-r-0:last-child,
td.last\:border-r-0:last-child {
    border-right-width: 0;
}
</style>