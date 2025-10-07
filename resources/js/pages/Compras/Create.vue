<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import type { AppPageProps, BreadcrumbItem } from '@/types'

// Props que vendrán desde Laravel (proveedores y productos)
type CreatePageProps = AppPageProps<{
  proveedores: { id: number; nombre: string }[]
  productos: { id: number; nombre: string; precio_unitario: number }[]
}>
const { props } = usePage<CreatePageProps>()

// Migas de pan
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Compras', href: '/compras' },
  { title: 'Crear', href: '#' },
]

// Estado del formulario
const proveedorId = ref<number|null>(null)
const fecha = ref<string>('')
// Inicializar fecha con hoy (YYYY-MM-DD)
fecha.value = new Date().toISOString().slice(0, 10)
const detalles = ref<{ producto_id: number|null; cantidad: number; precio_unitario: number }[]>([
  { producto_id: null, cantidad: 1, precio_unitario: 0 }
])

// Función para agregar fila de producto
const addDetalle = () => {
  detalles.value.push({ producto_id: null, cantidad: 1, precio_unitario: 0 })
}

// Función para eliminar fila de producto
const removeDetalle = (index:number) => {
  detalles.value.splice(index,1)
}

// Calcular total
const total = computed(()=>{
  return detalles.value.reduce((acc, d)=>{
    if (d.producto_id && d.cantidad > 0) {
      return acc + (d.cantidad * d.precio_unitario)
    }
    return acc
  },0)
})

// Enviar datos al backend
const submitCompra = () => {
  router.post('/compras', {
    proveedor_id: proveedorId.value,
    fecha: fecha.value,
    detalles: detalles.value,
  },{
    onSuccess:()=>{ 
      alert('✅ Compra registrada y stock actualizado') 
    },
    onError:(err)=>{ console.error(err) }
  })
}
</script>

<template>
  <Head title="Registrar Compra" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 -xl p-4 bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-300 dark:border-sidebar-border md:min-h-min">
      <h2 class="text-2xl font-semibold mb-4">Registrar Compra</h2>

      <!-- Selección Proveedor -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <Label for="proveedor_id">Proveedor</Label>
          <select id="proveedor_id" v-model="proveedorId" class="w-full border rounded p-2 bg-white dark:bg-gray-800">
            <option disabled value="">Seleccione un proveedor</option>
            <option v-for="prov in props.proveedores" :key="prov.id" :value="prov.id">
              {{ prov.nombre }}
            </option>
          </select>
        </div>
        <div>
          <Label for="fecha">Fecha</Label>
          <Input class=" bg-white dark:bg-gray-800" id="fecha" type="date" v-model="fecha" />
        </div>
      </div>

      <!-- Tabla Detalle -->
      <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-300 bg-white dark:bg-gray-800">
          <thead class="bg-gray-100">
            <tr>
              <th class="border p-2">Producto</th>
              <th class="border p-2">Cantidad</th>
              <th class="border p-2">Precio Unitario</th>
              <th class="border p-2">Subtotal</th>
              <th class="border p-2">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(detalle,index) in detalles" :key="index">
              <!-- Producto -->
              <td class="border p-2">
                <select v-model="detalle.producto_id" class="w-full border rounded p-1">
                  <option disabled value="">Seleccione</option>
                  <option v-for="prod in props.productos" :key="prod.id" :value="prod.id">
                    {{ prod.nombre }}
                  </option>
                </select>
              </td>

              <!-- Cantidad -->
              <td class="border p-2">
                <Input type="number" v-model.number="detalle.cantidad" min="1" class="w-20" />
              </td>

              <!-- Precio Unitario -->
              <td class="border p-2">
                <Input type="number" v-model.number="detalle.precio_unitario" step="0.01" class="w-28" />
              </td>

              <!-- Subtotal -->
              <td class="border p-2 text-right">
                {{ (detalle.cantidad * detalle.precio_unitario).toFixed(2) }}
              </td>

              <!-- Acciones -->
              <td class="border p-2 text-center">
                <Button size="sm" class="bg-rose-500 text-white hover:bg-rose-700"
                        @click="removeDetalle(index)">
                  Eliminar
                </Button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Botón añadir producto -->
      <Button @click="addDetalle" class="bg-indigo-500 text-white hover:bg-indigo-700 w-fit">
        + Añadir Producto
      </Button>

      <!-- Total -->
      <div class="text-right text-lg font-bold">
        Total: {{ new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(total) }}
      </div>

      <!-- Botón Guardar -->
      <div class="flex justify-end mt-4">
        <Button @click="submitCompra" class="bg-green-600 text-white hover:bg-green-700">
          Guardar Compra
        </Button>
      </div>
    </div>
  </AppLayout>
</template>
