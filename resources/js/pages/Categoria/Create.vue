<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import type { BreadcrumbItem } from '@/types'
import axios from 'axios'

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Categorías', href: '/categoria' },
  { title: 'Crear', href: '/categoria/create' },
]

const nombre = ref('')
const loading = ref(false)
const errorMsg = ref<string | null>(null)

const submit = async () => {
  if (!nombre.value.trim()) {
    errorMsg.value = 'El nombre es requerido'
    return
  }
  loading.value = true
  errorMsg.value = null
  try {
    await axios.post('/api/categoria', { nombre: nombre.value.trim() })
    // Volver al listado
    router.visit('/categoria', { replace: true })
  } catch (e: any) {
    errorMsg.value = e?.response?.data?.message || 'Error al crear categoría'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <Head title="Crear Categoría" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-1 flex-col gap-4 rounded-xl p-4 max-w-lg">
      <h1 class="text-2xl font-bold">Crear Categoría</h1>

      <div class="space-y-2">
        <Label for="nombre">Nombre</Label>
        <Input id="nombre" v-model="nombre" type="text" placeholder="Nombre de la categoría" />
      </div>

      <p v-if="errorMsg" class="text-sm text-red-600">{{ errorMsg }}</p>

      <div class="flex gap-2">
        <Button :disabled="loading" class="bg-indigo-600 text-white hover:bg-indigo-700" @click="submit">
          {{ loading ? 'Guardando...' : 'Guardar' }}
        </Button>
        <Button as-child variant="outline">
          <Link href="/categoria">Cancelar</Link>
        </Button>
      </div>
    </div>
  </AppLayout>
</template>
