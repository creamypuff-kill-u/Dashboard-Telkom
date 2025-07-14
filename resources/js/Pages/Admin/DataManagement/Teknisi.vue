<!-- Ini file Teknisi.vue -->
<template>
    <Head title="Data Performa Teknisi" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manajemen Data Performa Teknisi</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-xl font-bold mb-4">Daftar & Kelola Performa Teknisi</h3>
                    <button @click="openForm(null)" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded mb-4">
                        Tambah Performa Baru
                    </button>

                    <p v-if="loading" class="text-center text-gray-600">Memuat performa teknisi...</p>
                    <p v-else-if="error" class="text-center text-red-600">{{ error }}</p>

                    <table v-else-if="teknisiPerformance.length > 0" class="min-w-full bg-white border border-gray-300">
                        <thead>
                            <tr>
                                <th class="py-1 px-2 bg-purple-700 text-white font-semibold text-center border-b border-r border-purple-800 last:border-r-0">No</th>
                                <th class="py-1 px-2 bg-purple-700 text-white font-semibold text-center border-b border-r border-purple-800 last:border-r-0">TEKNISI</th>
                                <th class="py-1 px-2 bg-purple-700 text-white font-semibold text-center border-b border-r border-purple-800 last:border-r-0">RE</th>
                                <th class="py-1 px-2 bg-purple-700 text-white font-semibold text-center border-b border-r border-purple-800 last:border-r-0">CANCEL</th>
                                <th class="py-1 px-2 bg-purple-700 text-white font-semibold text-center border-b border-r border-purple-800 last:border-r-0">FALLOUT</th>
                                <th class="py-1 px-2 bg-purple-700 text-white font-semibold text-center border-b border-r border-purple-800 last:border-r-0">OGP SURVEY</th>
                                <th class="py-1 px-2 bg-purple-700 text-white font-semibold text-center border-b border-r border-purple-800 last:border-r-0">OGP TARIK</th>
                                <th class="py-1 px-2 bg-purple-700 text-white font-semibold text-center border-b border-r border-purple-800 last:border-r-0">PROSET</th>
                                <th class="py-1 px-2 bg-purple-700 text-white font-semibold text-center border-b border-r border-purple-800 last:border-r-0">PS</th>
                                <th class="py-1 px-2 bg-purple-700 text-white font-semibold text-center border-b border-r border-purple-800 last:border-r-0">PS/RE</th>
                                <th class="py-1 px-2 bg-purple-700 text-white font-semibold text-center border-b border-purple-800">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in teknisiPerformance" :key="item.id">
                                <td class="py-1 px-2 text-center border-r border-gray-300">{{ index + 1 }}.</td>
                                <td class="py-1 px-2 border-r border-gray-300">{{ item.teknisi }}</td>
                                <td class="py-1 px-2 text-center border-r border-gray-300">{{ item.RE }}</td>
                                <td class="py-1 px-2 text-center border-r border-gray-300">{{ item.CANCEL }}</td>
                                <td class="py-1 px-2 text-center border-r border-gray-300">{{ item.FALLOUT }}</td>
                                <td class="py-1 px-2 text-center border-r border-gray-300">{{ item.OGP_SURVEY }}</td>
                                <td class="py-1 px-2 text-center border-r border-gray-300">{{ item.OGP_TARIK }}</td>
                                <td class="py-1 px-2 text-center border-r border-gray-300">{{ item.PROSET }}</td>
                                <td class="py-1 px-2 text-center border-r border-gray-300">{{ item.PS }}</td>
                                <td class="py-1 px-2 text-center border-r border-gray-300">{{ item.PS_RE }}</td>
                                <td class="py-1 px-2 text-center">
                                    <button @click="openForm(item)" class="bg-yellow-500 hover:bg-yellow-700 text-white text-sm py-1 px-2 rounded mr-2">Edit</button>
                                    <button @click="deleteTeknisiPerformance(item.id)" class="bg-red-500 hover:bg-red-700 text-white text-sm py-1 px-2 rounded">Hapus</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-else class="text-center text-gray-600">Tidak ada data performa teknisi ditemukan.</p>
                </div>
            </div>

            <Transition
                enter-active-class="transition ease-out duration-300 transform-gpu"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition ease-in duration-200 transform-gpu"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div v-if="showForm" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white p-6 rounded-lg shadow-xl w-1/2">
                        <h2 class="text-xl font-bold mb-4">{{ editingItem ? 'Edit Performa Teknisi' : 'Tambah Performa Teknisi Baru' }}</h2>
                        <form @submit.prevent="saveTeknisiPerformance" class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label for="teknisi_performance_nama" class="block text-gray-700 text-sm font-bold mb-2">Nama Teknisi:</label>
                                <select id="teknisi_performance_nama" v-model="form.teknisi"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    required>
                                    <option value="">Pilih Teknisi</option>
                                    <option v-for="teknisi in masterTeknisiList" :key="teknisi" :value="teknisi">{{ teknisi }}</option>
                                </select>
                                <p v-if="formErrors.teknisi" class="text-red-500 text-xs mt-1">{{ formErrors.teknisi[0] }}</p>
                            </div>
                            <div class="col-span-1">
                                <label for="re_value" class="block text-gray-700 text-sm font-bold mb-2">RE:</label>
                                <input type="number" id="re_value" v-model.number="form.RE"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    required min="0" />
                                <p v-if="formErrors.RE" class="text-red-500 text-xs mt-1">{{ formErrors.RE[0] }}</p>
                            </div>
                            <div class="col-span-1">
                                <label for="cancel_value" class="block text-gray-700 text-sm font-bold mb-2">CANCEL:</label>
                                <input type="number" id="cancel_value" v-model.number="form.CANCEL"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    required min="0" />
                                <p v-if="formErrors.CANCEL" class="text-red-500 text-xs mt-1">{{ formErrors.CANCEL[0] }}</p>
                            </div>
                            <div class="col-span-1">
                                <label for="fallout_value" class="block text-gray-700 text-sm font-bold mb-2">FALLOUT:</label>
                                <input type="number" id="fallout_value" v-model.number="form.FALLOUT"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    required min="0" />
                                <p v-if="formErrors.FALLOUT" class="text-red-500 text-xs mt-1">{{ formErrors.FALLOUT[0] }}</p>
                            </div>
                            <div class="col-span-1">
                                <label for="ogp_survey_value" class="block text-gray-700 text-sm font-bold mb-2">OGP SURVEY:</label>
                                <input type="number" id="ogp_survey_value" v-model.number="form.OGP_SURVEY"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    required min="0" />
                                <p v-if="formErrors.OGP_SURVEY" class="text-red-500 text-xs mt-1">{{ formErrors.OGP_SURVEY[0] }}</p>
                            </div>
                            <div class="col-span-1">
                                <label for="ogp_tarik_value" class="block text-gray-700 text-sm font-bold mb-2">OGP TARIK:</label>
                                <input type="number" id="ogp_tarik_value" v-model.number="form.OGP_TARIK"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    required min="0" />
                                <p v-if="formErrors.OGP_TARIK" class="text-red-500 text-xs mt-1">{{ formErrors.OGP_TARIK[0] }}</p>
                            </div>
                            <div class="col-span-1">
                                <label for="proset_value" class="block text-gray-700 text-sm font-bold mb-2">PROSET:</label>
                                <input type="number" id="proset_value" v-model.number="form.PROSET"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    required min="0" />
                                <p v-if="formErrors.PROSET" class="text-red-500 text-xs mt-1">{{ formErrors.PROSET[0] }}</p>
                            </div>

                            <div class="col-span-1">
                                <label for="ps_value" class="block text-gray-700 text-sm font-bold mb-2">PS:</label>
                                <input type="number" id="ps_value" v-model.number="form.PS"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    required min="0" />
                                <p v-if="formErrors.PS" class="text-red-500 text-xs mt-1">{{ formErrors.PS[0] }}</p>
                            </div>
                            <div class="col-span-1">
                                <label for="ps_re_value" class="block text-gray-700 text-sm font-bold mb-2">PS/RE (%):</label>
                                <input type="text" id="ps_re_value" v-model="form.PS_RE"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-100 cursor-not-allowed"
                                    required readonly />
                                <p v-if="formErrors.PS_RE" class="text-red-500 text-xs mt-1">{{ formErrors.PS_RE[0] }}</p>
                            </div>
                            <div class="flex items-center justify-between col-span-2 mt-4">
                                <button type="submit"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                    Simpan
                                </button>
                                <button type="button" @click="closeForm"
                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import { db } from '@/firebase';
import { ref as dbRef, onValue, push, update, remove } from 'firebase/database';

const teknisiPerformance = ref([]);
const loading = ref(true);
const error = ref(null);
const showForm = ref(false);
const editingItem = ref(null);
const form = ref({
    teknisi: '', RE: 0, CANCEL: 0, FALLOUT: 0, OGP_SURVEY: 0, OGP_TARIK: 0, PROSET: 0, PS: 0, PS_RE: '0.00%'
});
const formErrors = ref({});

const teknisiPerformanceRef = dbRef(db, 'teknisiPerformanceData');
const masterTeknisiList = ref([]);

const calculatePsRe = () => {
    const ps = form.value.PS || 0;
    const re = form.value.RE || 0;

    if (re > 0) {
        const result = (ps / re) * 100;
        form.value.PS_RE = `${result.toFixed(2)}%`;
    } else {
        form.value.PS_RE = '0.00%'; // Handle division by zero
    }
};

// Watch for changes in PS and RE to automatically update PS_RE
watch([() => form.value.PS, () => form.value.RE], calculatePsRe, { immediate: true });

const fetchTeknisiPerformance = () => {
    onValue(teknisiPerformanceRef, (snapshot) => {
        const data = snapshot.val();
        const loadedData = [];
        if (data) {
            for (let id in data) {
                loadedData.push({ id: id, ...data[id] });
            }
        }
        teknisiPerformance.value = loadedData;
        loading.value = false;
    }, (err) => {
        error.value = 'Gagal memuat data performa teknisi dari Firebase.';
        console.error('Error fetching teknisi performance data from Firebase:', err);
        loading.value = false;
    });
};

const fetchMasterTeknisi = () => {
    const masterTeknisiDbRef = dbRef(db, 'masterTeknisi');
    onValue(masterTeknisiDbRef, (snapshot) => {
        const data = snapshot.val();
        const loadedTeknisi = [];
        if (data) {
            for (let id in data) {
                if (data[id] && data[id].nama) {
                    loadedTeknisi.push(data[id].nama);
                }
            }
        }
        masterTeknisiList.value = loadedTeknisi.sort();
    }, (err) => {
        console.error('Error fetching master teknisi for dropdown:', err);
    });
};

onMounted(() => {
    fetchTeknisiPerformance();
    fetchMasterTeknisi();
});

const openForm = (item = null) => {
    if (item) {
        editingItem.value = item;
        // Ensure numbers are truly numbers by parsing, though v-model.number helps
        form.value = {
            ...JSON.parse(JSON.stringify(item)),
            RE: Number(item.RE),
            CANCEL: Number(item.CANCEL),
            FALLOUT: Number(item.FALLOUT),
            OGP_SURVEY: Number(item.OGP_SURVEY),
            OGP_TARIK: Number(item.OGP_TARIK),
            PROSET: Number(item.PROSET),
            PS: Number(item.PS),
        };
    } else {
        editingItem.value = null;
        form.value = {
            teknisi: '', RE: 0, CANCEL: 0, FALLOUT: 0, OGP_SURVEY: 0, OGP_TARIK: 0, PROSET: 0, PS: 0, PS_RE: '0.00%'
        };
    }
    formErrors.value = {};
    showForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
    editingItem.value = null;
    form.value = {
        teknisi: '', RE: 0, CANCEL: 0, FALLOUT: 0, OGP_SURVEY: 0, OGP_TARIK: 0, PROSET: 0, PS: 0, PS_RE: '0.00%'
    };
    formErrors.value = {};
};

const saveTeknisiPerformance = async () => {
    formErrors.value = {};

    // Basic validation
    if (!form.value.teknisi) formErrors.value.teknisi = ['Nama Teknisi harus diisi.'];
    if (form.value.RE === null || form.value.RE < 0) formErrors.value.RE = ['RE harus angka positif.'];
    if (form.value.CANCEL === null || form.value.CANCEL < 0) formErrors.value.CANCEL = ['CANCEL harus angka positif.'];
    if (form.value.FALLOUT === null || form.value.FALLOUT < 0) formErrors.value.FALLOUT = ['FALLOUT harus angka positif.'];
    if (form.value.OGP_SURVEY === null || form.value.OGP_SURVEY < 0) formErrors.value.OGP_SURVEY = ['OGP SURVEY harus angka positif.'];
    if (form.value.OGP_TARIK === null || form.value.OGP_TARIK < 0) formErrors.value.OGP_TARIK = ['OGP TARIK harus angka positif.'];
    if (form.value.PROSET === null || form.value.PROSET < 0) formErrors.value.PROSET = ['PROSET harus angka positif.'];
    if (form.value.PS === null || form.value.PS < 0) formErrors.value.PS = ['PS harus angka positif.'];

    // PS_RE is now calculated, so we just need to ensure it's not empty, though it shouldn't be if calculation works.
    if (!form.value.PS_RE) formErrors.value.PS_RE = ['PS/RE tidak dapat dihitung. Pastikan nilai RE lebih dari 0.'];


    if (Object.keys(formErrors.value).length > 0) {
        alert('Silakan lengkapi semua field yang wajib diisi dengan benar!');
        return;
    }

    try {
        if (editingItem.value) {
            const itemRef = dbRef(db, `teknisiPerformanceData/${editingItem.value.id}`);
            await update(itemRef, { ...form.value });
            alert('Data performa teknisi berhasil diperbarui!');
        } else {
            await push(teknisiPerformanceRef, {
                ...form.value,
                created_at: new Date().toISOString(),
            });
            alert('Data performa teknisi berhasil ditambahkan!');
        }
        closeForm();
    } catch (e) {
        console.error('Error saving teknisi performance data to Firebase:', e);
        error.value = 'Gagal menyimpan data performa teknisi.';
    }
};

const deleteTeknisiPerformance = async (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus data performa teknisi ini?')) {
        try {
            const itemRef = dbRef(db, `teknisiPerformanceData/${id}`);
            await remove(itemRef);
            alert('Data performa teknisi berhasil dihapus!');
        } catch (e) {
            console.error('Error deleting teknisi performance data from Firebase:', e);
            error.value = 'Gagal menghapus data performa teknisi.';
        }
    }
};
</script>

<style scoped>
/* Tambahkan gaya khusus untuk komponen ini jika diperlukan */
</style>