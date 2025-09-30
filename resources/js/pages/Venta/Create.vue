<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import type { AppPageProps, BreadcrumbItem } from '@/types'

// Props que vendrán desde Laravel (clientes y productos)
type CreatePageProps = AppPageProps<{
  cliente: { id: number; nombre: string; telefono: string }[]
  productos: { id: number; nombre: string; precio_unitario: number }[]
}>
const { props } = usePage<CreatePageProps>()

// Migas de pan
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Ventas', href: '/venta' },
  { title: 'Crear', href: '#' },
]

// Estado del formulario
const clienteId = ref<number|null>(null)
const fecha = ref<string>(new Date().toISOString().slice(0, 10))
const detalles = ref<{ producto_id: number|null; cantidad: number; precio_unitario: number }[]>([
  { producto_id: null, cantidad: 1, precio_unitario: 0 }
])

// Funciones para agregar/eliminar productos
const addDetalle = () => {
  detalles.value.push({ producto_id: null, cantidad: 1, precio_unitario: 0 })
}
const removeDetalle = (index:number) => {
  detalles.value.splice(index,1)
}

// Completar precio automáticamente al seleccionar producto
const setPrecio = (detalle:any) => {
  if (detalle.producto_id) {
    const producto = props.productos.find(p => p.id === detalle.producto_id)
    if (producto) {
      detalle.precio_unitario = producto.precio_unitario
    }
  } else {
    detalle.precio_unitario = 0
  }
}

// Watch: cada vez que cambie producto_id en cualquier detalle, actualiza precio
watch(detalles, (newVal) => {
  newVal.forEach(detalle => setPrecio(detalle))
}, { deep: true })

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
const submitVenta = () => {
  router.post('/venta', {
    cliente_id: clienteId.value,
    fecha: fecha.value,
    detalles: detalles.value,
  },{
    onSuccess:()=>{ 
      alert('✅ Venta registrada correctamente y stock actualizado') 
    },
    onError:(err)=>{ console.error(err) }
  })
}
</script>

<template>
  <Head title="Registrar Venta" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-4 rounded-xl p-6 border border-gray-300">
      <h2 class="text-2xl font-semibold mb-4">Registrar Venta</h2>

      <!-- Selección Cliente -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <Label for="cliente_id">Cliente</Label>
          <select id="cliente_id" v-model="clienteId" class="w-full border rounded p-2">
            <option disabled value="">Seleccione un cliente</option>
            <option v-for="cli in props.cliente" :key="cli.id" :value="cli.id">
              {{ cli.nombre }} - {{ cli.telefono }}
            </option>
          </select>
        </div>
        <div>
          <Label for="fecha">Fecha</Label>
          <Input id="fecha" type="date" v-model="fecha" />
        </div>
      </div>

      <!-- Tabla Detalle -->
      <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-300">
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
        <Button @click="submitVenta" class="bg-green-600 text-white hover:bg-green-700">
          Guardar Venta
        </Button>
      </div>
    </div>
  </AppLayout>
</template>
