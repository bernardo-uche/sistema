<script setup lang="ts">

import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const name = "Dental Celestial";
const quote = page.props.quote;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
  <div
    class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 
           lg:max-w-none lg:grid-cols-2 lg:px-0"
  >
    <!-- LADO IZQUIERDO (IMAGEN + LOGO + FRASE) -->
    <div
      class="relative hidden h-full flex-col p-10 text-white lg:flex dark:border-r"
    >
      <!-- Imagen de fondo -->
      <div
        class="absolute inset-0 bg-cover bg-center"
        style="background-image: url('https://images.squarespace-cdn.com/content/v1/5b91733c9772ae9bb38e47c0/ae84e082-2a6c-4645-87b0-43ab29f149e1/curiosidadesdentales');"
      ></div>
      <!-- Capa oscura encima de la imagen -->
      <div class="absolute inset-0 bg-black/60"></div>

      <!-- Logo + Nombre -->
      <Link
        :href="route('home')"
        class="relative z-20 flex items-center text-lg font-medium"
      >
        <!-- Aquí tu logo -->
        <DentalLogo class="mr-2 size-10" />
        {{ name }}
      </Link>

      <!-- Frase motivacional -->
      <div v-if="quote" class="relative z-20 mt-auto">
        <blockquote class="space-y-2">
          <p class="text-lg">&ldquo;{{ quote.message }}&rdquo;</p>
          <footer class="text-sm text-neutral-300">
            {{ quote.author }}
          </footer>
        </blockquote>
      </div>
    </div>

    <!-- LADO DERECHO (FORMULARIO LOGIN) -->
    <div class="lg:p-8">
      <div
        class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]"
      >
        <div class="flex flex-col space-y-2 text-center">
          <h1 class="text-xl font-medium tracking-tight" v-if="title">
            {{ title }}
          </h1>
          <p class="text-sm text-muted-foreground" v-if="description">
            {{ description }}
          </p>
        </div>
        <slot />
      </div>
    </div>
  </div>
</template>