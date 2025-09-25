<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import type { BreadcrumbItem } from '@/types'
import type { AppPageProps } from '@/types'
import axios from 'axios'

// Recibimos el id vía props de la ruta Inertia (routes/web.php)
const page = usePage<AppPageProps<{ id: number }>>()
const id = page.props.id as number

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Categorías', href: '/categoria' },
  { title: 'Editar', href: `/categoria/${id}/edit` },
]

const nombre = ref('')
const loading = ref(false)
const errorMsg = ref<string | null>(null)

const fetchCategoria = async () => {
  try {
    const { data } = await axios.get(`/api/categoria/${id}`)
    nombre.value = data?.data?.nombre ?? ''
  } catch (e: any) {
    errorMsg.value = e?.response?.data?.message || 'No se pudo cargar la categoría'
  }
}

const submit = async () => {
  if (!nombre.value.trim()) {
    errorMsg.value = 'El nombre es requerido'
    return
  }
  loading.value = true
  errorMsg.value = null
  try {
    await axios.put(`/api/categoria/${id}`, { nombre: nombre.value.trim() })
    router.visit('/categoria', { replace: true })
  } catch (e: any) {
    errorMsg.value = e?.response?.data?.message || 'Error al actualizar categoría'
  } finally {
    loading.value = false
  }
}

onMounted(fetchCategoria)
</script>

<template>
  <Head title="Editar Categoría" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-1 flex-col gap-4 rounded-xl p-4 max-w-lg">
      <h1 class="text-2xl font-bold">Editar Categoría</h1>

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
