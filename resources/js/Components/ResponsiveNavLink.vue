<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
    // Prop baru untuk alignment teks
    align: { 
        type: String,
        default: 'left', // Default ke kiri
        validator: (value) => ['left', 'center', 'right'].includes(value), 
    },
});

const classes = computed(() => {
    const baseClasses = 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-base font-medium transition duration-150 ease-in-out';
    const activeClasses = 'border-indigo-400 text-indigo-700 bg-indigo-50 focus:outline-none focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700';
    const inactiveClasses = 'text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300';
    
    // Logika untuk menerapkan kelas alignment
    let alignmentClass = '';
    if (props.align === 'right') {
        alignmentClass = 'text-right'; // Menerapkan text-right
    } else if (props.align === 'center') {
        alignmentClass = 'text-center';
    } else { 
        alignmentClass = 'text-left'; // Default atau 'left'
    }

    return [
        baseClasses,
        props.active ? activeClasses : inactiveClasses,
        alignmentClass // Tambahkan kelas alignment ke daftar kelas
    ].join(' '); 
});
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>