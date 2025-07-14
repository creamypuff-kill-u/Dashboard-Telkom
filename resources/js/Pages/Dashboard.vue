<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import FunnelingB2C from '@/Pages/FunnelingB2C.vue';
import FalloutChart from '@/Components/Dashboard/FalloutChart.vue';
import TabelOrder from '@/Components/Dashboard/TabelOrder.vue';
import PsReB2CChart from '@/Components/Dashboard/PsReB2CChart.vue';
import TechnicianScheduleTable from '@/Components/Dashboard/TechnicianScheduleTable.vue';
import teknisiPerformanceData from '@/Components/Dashboard/teknisiPerformanceData.vue'; // <-- Pastikan ini diimport
import DropdownMenu from '@/Components/Dropdown.vue'; // Atau komponen dropdown kustom jika ada

</script>

<template>
    <Head title="Provisioning TSEL Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">DASHBOARD PROVISIONING TSEL</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <FunnelingB2C />
                        </div>
                        <div>
                            <FalloutChart />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 mb-6">
                        <div class="col-span-full">
                            <PsReB2CChart />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 mb-6">
                        <div class="col-span-full">
                            <TabelOrder dataType="orders" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-2">
                            <teknisiPerformanceData />
                        </div>

                        <div class="lg:col-span-1">
                            <TechnicianScheduleTable />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>