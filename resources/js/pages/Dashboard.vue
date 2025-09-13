<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex items-center justify-center text-lg font-semibold">
                    Cliente
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex items-center justify-center text-lg font-semibold">
                    Compras Ventas
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex items-center justify-center text-lg font-semibold">
                    Productos
                </div>  
            </div>
           <div class="flex flex-col md:flex-row gap-4">
    <!-- Productos con stock mínimo -->
    <div class="flex-1 p-4 rounded-lg border border-red-500 bg-red-100 text-red-800 font-semibold">
        <h2 class="text-lg mb-2">PRODUCTOS CON STOCK MINIMO</h2>
        <div id="productos-stock-minimo" class="space-y-2">
            <!-- Aquí se insertarán los productos desde el backend -->
        </div>
    </div>

    <!-- Productos más vendidos -->
    <div class="flex-1 p-4 rounded-lg border border-yellow-500 bg-yellow-100 text-yellow-800 font-semibold">
        <h2 class="text-lg mb-2">PRODUCTOS PROXIMOS A VENCER</h2>
        <div id="productos-mas-vendidos" class="space-y-2">
            <!-- Aquí se insertarán los productos desde el backend -->
        </div>
    </div>
</div>

        </div>
    </AppLayout>
</template>
