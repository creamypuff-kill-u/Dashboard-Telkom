<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white border-b border-gray-100 fixed w-full z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <div class="shrink-0 flex items-center">
                            <Link :href="route('dashboard')">
                                <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />
                            </Link>
                        </div>

                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                            <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                Dashboard
                            </NavLink>

                            <div class="relative">
                                <button
                                    @click="toggleInputDataDropdown"
                                    class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none transition duration-150 ease-in-out h-full"
                                >
                                    Manajemen Data Aplikasi
                                    <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <div
                                    v-show="showingInputDataDropdown"
                                    @click.away="showingInputDataDropdown = false"
                                    class="absolute right-0 mt-2 w-48 z-[999] rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 transition ease-out duration-100 origin-top-right"
                                >
                                    <div class="py-1 flex flex-col space-y-1">
                                        <NavLink :href="route('admin.data_management.index', { section: 'orders' })" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md" :active="route().current('admin.data_management.index') && route().params.section === 'orders'">
                                            Orders
                                        </NavLink>
                                        <NavLink :href="route('admin.teknisi.index')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md" :active="route().current('admin.teknisi.index')">
                                            Performa Teknisi
                                        </NavLink>
                                        <NavLink :href="route('admin.input_jadwal_teknisi.index')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md" :active="route().current('admin.input_jadwal_teknisi.index')">
                                            Jadwal Teknisi
                                        </NavLink>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center ml-auto">
                        <div class="hidden sm:flex sm:items-center">
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                            >
                                                {{ $page.props.auth.user.name }}
                                                <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <header class="bg-white fixed top-16 w-full z-30 shadow-md" v-if="$slots.header">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main class="pt-24">
            <slot />
        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const showingInputDataDropdown = ref(false);

const toggleInputDataDropdown = () => {
    showingInputDataDropdown.value = !showingInputDataDropdown.value;
};
</script>
