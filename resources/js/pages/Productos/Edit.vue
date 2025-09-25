<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, watchEffect } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

// Props desde Laravel
const props = defineProps<{
  producto: {
    id: number
    nombre: string
    stock: number
    precio_unitario: number
    costo_unitario: number
    fecha_vencimiento: string | null
    proveedor_id: number | null
    categoria_id: number | null
    estado: string
  }
  proveedores: { id: number; nombre: string }[]
  categorias: { id: number; nombre: string }[]
}>()

type BreadcrumbItem = { title: string; href: string };
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Productos', href: '/productos' },
  { title: 'Editar', href: '#' },
];

// Formulario (inicializado con props)
const form = reactive<Partial<{
  nombre: string
  stock: number
  precio_unitario: number
  costo_unitario: number
  fecha_vencimiento: string | undefined
  proveedor_id: number | null
  categoria_id: number | null
  estado: string
}>>({})

watchEffect(() => {
  if (props.producto) {
    form.nombre = props.producto.nombre ?? ''
    form.stock = props.producto.stock ?? 0
    form.precio_unitario = props.producto.precio_unitario ?? 0
    form.costo_unitario = props.producto.costo_unitario ?? 0
    form.fecha_vencimiento = props.producto.fecha_vencimiento ?? undefined
    form.proveedor_id = props.producto.proveedor_id ?? null
    form.categoria_id = props.producto.categoria_id ?? null
    form.estado = props.producto.estado ?? 'Activo'
  }
})

const submit = () => {
  router.put(`/productos/${props.producto.id}`, form)
}
</script>

<template>
  <Head title="Editar Producto" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-1 flex-col gap-4 rounded-xl p-4">
      <h1 class="text-2xl font-bold">Editar Producto</h1>

      <form @submit.prevent="submit" class="max-w-lg space-y-6">
        <!-- Nombre -->
        <div class="space-y-2">
          <Label for="nombre">Nombre</Label>
          <Input
            id="nombre"
            v-model="form.nombre"
            type="text"
            placeholder="Ingrese el nombre del producto"
            required
          />
        </div>

        <!-- Stock -->
        <div class="space-y-2">
          <Label for="stock">Stock</Label>
          <Input
            id="stock"
            v-model="form.stock"
            type="number"
            min="0"
            required
          />
        </div>

        <!-- Precio Unitario -->
        <div class="space-y-2">
          <Label for="precio_unitario">Precio Unitario</Label>
          <Input
            id="precio_unitario"
            v-model="form.precio_unitario"
            type="number"
            step="0.01"
            min="0"
            required
          />
        </div>

        <!-- Costo Unitario -->
        <div class="space-y-2">
          <Label for="costo_unitario">Costo Unitario</Label>
          <Input
            id="costo_unitario"
            v-model="form.costo_unitario"
            type="number"
            step="0.01"
            min="0"
            required
          />
        </div>

        <!-- Fecha Vencimiento -->
        <div class="space-y-2">
          <Label for="fecha_vencimiento">Fecha de Vencimiento</Label>
          <Input
            id="fecha_vencimiento"
            v-model="form.fecha_vencimiento"
            type="date"
          />
        </div>

        <!-- Proveedor -->
        <div class="space-y-2">
          <Label for="proveedor_id">Proveedor</Label>
          <select
            id="proveedor_id"
            v-model.number="form.proveedor_id"
            class="w-full border rounded p-2"
            required
          >
            <option :value="null" disabled>Seleccione un proveedor</option>
            <option
              v-for="proveedor in proveedores"
              :key="proveedor.id"
              :value="proveedor.id"
            >
              {{ proveedor.nombre }}
            </option>
          </select>
        </div>

        <!-- Categoría -->
        <div class="space-y-2">
          <Label for="categoria_id">Categoría</Label>
          <select
            id="categoria_id"
            v-model.number="form.categoria_id"
            class="w-full border rounded p-2"
            required
          >
            <option :value="null" disabled>Seleccione una categoría</option>
            <option
              v-for="categoria in categorias"
              :key="categoria.id"
              :value="categoria.id"
            >
              {{ categoria.nombre }}
            </option>
          </select>
        </div>

        <!-- Estado -->
        <div class="space-y-2">
          <Label for="estado">Estado</Label>
          <select
            id="estado"
            v-model="form.estado"
            class="w-full border rounded p-2"
            required
          >
            <option value="Activo">Activo</option>
            <option value="Inactivo">Inactivo</option>
          </select>
        </div>

        <!-- Botones -->
        <div class="flex gap-4">
          <Button type="submit" class="bg-indigo-500 hover:bg-indigo-600">
            Actualizar
          </Button>
          <Button as="a" href="/productos" variant="outline">
            Cancelar
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
