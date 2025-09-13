<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'

// Props que vendrán desde Laravel (proveedores y productos)
const { props } = usePage<{
  proveedores: { id: number; nombre: string }[]
  productos: { id: number; nombre: string; precio_unitario: number }[]
}>()

// Estado del formulario
const proveedorId = ref<number|null>(null)
const detalles = ref<{ productoId: number|null; cantidad: number; precio: number }[]>([
  { productoId: null, cantidad: 1, precio: 0 }
])

// Función para agregar fila de producto
const addDetalle = () => {
  detalles.value.push({ productoId: null, cantidad: 1, precio: 0 })
}

// Función para eliminar fila de producto
const removeDetalle = (index:number) => {
  detalles.value.splice(index,1)
}

// Calcular total
const total = computed(()=>{
  return detalles.value.reduce((acc, d)=>{
    if (d.productoId && d.cantidad > 0) {
      return acc + (d.cantidad * d.precio)
    }
    return acc
  },0)
})

// Enviar datos al backend
const submitCompra = () => {
  router.post('/compras', {
    proveedor_id: proveedorId.value,
    detalles: detalles.value
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
  <AppLayout>
    <div class="flex flex-col gap-4 rounded-xl p-6 border border-gray-300">
      <h2 class="text-2xl font-semibold mb-4">Registrar Compra</h2>

      <!-- Selección Proveedor -->
      <div class="mb-4">
        <label class="block mb-2 font-medium">Proveedor</label>
        <select v-model="proveedorId" class="w-full border rounded p-2">
          <option disabled value="">Seleccione un proveedor</option>
          <option v-for="prov in props.proveedores" :key="prov.id" :value="prov.id">
            {{ prov.nombre }}
          </option>
        </select>
      </div>

      <!-- Tabla Detalle -->
      <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-300">
          <thead class="bg-gray-100">
            <tr>
              <th class="border p-2">Producto</th>
              <th class="border p-2">Cantidad</th>
              <th class="border p-2">Precio</th>
              <th class="border p-2">Subtotal</th>
              <th class="border p-2">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(detalle,index) in detalles" :key="index">
              <!-- Producto -->
              <td class="border p-2">
                <select v-model="detalle.productoId" class="w-full border rounded p-1">
                  <option disabled value="">Seleccione</option>
                  <option v-for="prod in props.productos" :key="prod.id" :value="prod.id">
                    {{ prod.nombre }}
                  </option>
                </select>
              </td>

              <!-- Cantidad -->
              <td class="border p-2">
                <input type="number" v-model.number="detalle.cantidad" min="1" class="w-20 border rounded p-1" />
              </td>

              <!-- Precio -->
              <td class="border p-2">
                <input type="number" v-model.number="detalle.precio" step="0.01" class="w-28 border rounded p-1" />
              </td>

              <!-- Subtotal -->
              <td class="border p-2 text-right">
                {{ (detalle.cantidad * detalle.precio).toFixed(2) }}
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
        Total: {{ total.toFixed(2) }} Bs.
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
