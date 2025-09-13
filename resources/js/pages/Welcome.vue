<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';



</script>

<template>
  <Head title="Welcome">
    <link rel="preconnect" href="https://rsms.me/" />
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
  </Head>

  <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] text-[#1b1b18] p-6 lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
    
    <!-- 🔹 NAVBAR -->
    <header class="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl">
      <nav class="flex items-center justify-end gap-4">
        <Link
          v-if="$page.props.auth.user"
          :href="route('dashboard')"
          class="inline-block rounded-md border border-[#19140035] px-5 py-1.5 text-sm font-medium leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
        >
          Dashboard
        </Link>
        <template v-else>
          <Link
            :href="route('login')"
            class="inline-block rounded-md border border-transparent px-5 py-1.5 text-sm font-medium leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
          >
            Log in
          </Link>
          <Link
            :href="route('register')"
            class="inline-block rounded-md border border-[#19140035] px-5 py-1.5 text-sm font-medium leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
          >
            Register
          </Link>
        </template>
      </nav>
    </header>

    <!-- 🔹 MAIN CONTENT -->
    <div class="flex w-full items-center justify-center lg:grow">
      <main class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-2xl shadow-lg lg:max-w-5xl lg:flex-row">
        
        <!-- 🟢 TEXTO DERECHA -->
        <div
          class="flex-1 rounded-b-2xl bg-white p-8 text-[15px] leading-relaxed lg:rounded-l-none lg:rounded-r-2xl lg:p-16 dark:bg-[#161615] dark:text-[#EDEDEC]"
        >
          <h1 class="mb-3 text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">
            Bienvenido a <span class="text-[#f53003]">Dental Celestial</span>
          </h1>
          <!--<p class="mb-6 text-[#706f6c] dark:text-[#A1A09A]">
            Administra compras, ventas categorías y gestiona tu clínica de manera profesional
            desde nuestro sistema web.
          </p>-->

          <ul class="space-y-3">
            <li class="flex items-center gap-2">
              <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#f53003] text-white text-xs">✔</span>
              <span>Gestión Compras y Ventas</span>
            </li>
            <li class="flex items-center gap-2">
              <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#f53003] text-white text-xs">✔</span>
              <span>Administración de categorías</span>
            </li>
            <li class="flex items-center gap-2">
              <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#f53003] text-white text-xs">✔</span>
              <span>Reportes</span>
            </li>
          </ul>

          <div class="mt-8">
            <a
              href="#"
              class="inline-block rounded-lg bg-[#f53003] px-6 py-2 text-white font-medium shadow hover:bg-[#d92600] transition"
            >
              Explorar el sistema
            </a>
          </div>
        </div>

        <!-- 🟢 IMAGEN IZQUIERDA (Logo con animación) -->
        <div
          class="relative flex items-center justify-center bg-blue-100 p-8 lg:w-[420px] dark:bg-[#1D0002]"
        >
          <img
            src="/dental.jpg"
            alt="Logo Dental Celestial"
            class="h-auto w-auto object-cover animate-fade-up"
          />
        </div>
      </main>
    </div>

  </div>
</template>

<style>
@keyframes fadeUp {
  0% {
    opacity: 0;
    transform: translateY(40px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}
.animate-fade-up {
  animation: fadeUp 1s ease-out forwards;
}
</style>
