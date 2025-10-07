<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

// Importamos iconos de lucide-vue-next
import {  UsersRound, ShoppingCart, Receipt, Package, AlertTriangle, Clock  } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
];
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen bg-gray-100 p-6  dark:bg-gray-800">
    <!-- Título -->
    <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">
      PANEL PRINCIPAL
    </h1>

    <!-- Grid de tarjetas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      
      <!-- Clientes -->
      <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition cursor-pointer flex items-center gap-4">
        <UsersRound class="w-12 h-12 text-blue-600" />
        <div>
          <h2 class="text-xl font-semibold text-gray-800">Clientes</h2>
          <p class="text-gray-500 text-sm">Gestión de clientes registrados</p>
        </div>
      </div>

      <!-- Compras -->
      <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition cursor-pointer flex items-center gap-4">
        <ShoppingCart class="w-12 h-12 text-green-600" />
        <div>
          <h2 class="text-xl font-semibold text-gray-800">Compras</h2>
          <p class="text-gray-500 text-sm">Control de compras a proveedores</p>
        </div>
      </div>

      <!-- Ventas -->
      <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition cursor-pointer flex items-center gap-4">
        <Receipt class="w-12 h-12 text-purple-600" />
        <div>
          <h2 class="text-xl font-semibold text-gray-800">Ventas</h2>
          <p class="text-gray-500 text-sm">Gestión de ventas diarias</p>
        </div>
      </div>

      <!-- Productos -->
      <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition cursor-pointer flex items-center gap-4">
        <Package class="w-12 h-12 text-orange-600" />
        <div>
          <h2 class="text-xl font-semibold text-gray-800">Productos</h2>
          <p class="text-gray-500 text-sm">Administración del catálogo</p>
        </div>
      </div>

      <!-- Stock Mínimo -->
      <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition cursor-pointer flex items-center gap-4">
        <AlertTriangle class="w-12 h-12 text-red-600" />
        <div>
          <h2 class="text-xl font-semibold text-gray-800">Stock Mínimo</h2>
          <p class="text-gray-500 text-sm">Productos con bajo inventario</p>
        </div>
      </div>

      <!-- Stock Próximos a Vencer -->
      <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition cursor-pointer flex items-center gap-4">
        <Clock class="w-12 h-12 text-yellow-600" />
        <div>
          <h2 class="text-xl font-semibold text-gray-800">Stock a Vencer</h2>
          <p class="text-gray-500 text-sm">Productos Proximos a Vencer</p>
        </div>
      </div>

    </div>
  </div>
  </AppLayout>
</template>
