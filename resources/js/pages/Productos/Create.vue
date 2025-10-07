<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from "vue";
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

// Props que vienen desde Laravel (proveedores y categorias)
defineProps<{
    proveedores: { id: number; nombre: string }[];
    categorias: { id: number; nombre: string }[];
}>();

type BreadcrumbItem = { title: string; href: string };
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Productos', href: '/productos' },
    { title: 'Crear', href: '#' },
];

// Formulario Producto
const form = ref<Partial<{
    nombre: string;
    stock: number;
    precio_unitario: number;
    costo_unitario: number;
    fecha_vencimiento: string;
    proveedor_id: number | null;
    categoria_id: number | null;
    estado: string;
}>>({
    nombre: '',
    stock: 0,
    precio_unitario: 0,
    costo_unitario: 0,
    fecha_vencimiento: '',
    proveedor_id: null,
    categoria_id: null,
    estado: 'Activo',
});

const resetForm = () => {
    form.value = {
        nombre: '',
        stock: 0,
        precio_unitario: 0,
        costo_unitario: 0,
        fecha_vencimiento: '',
        proveedor_id: null,
        categoria_id: null,
        estado: 'Activo',
    };
};

const submit = () => {
    router.post('/productos', form.value, { onSuccess: resetForm });
};
</script>

<template>
    <Head title="Crear Producto" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 -xl p-4 bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-300 dark:border-sidebar-border md:min-h-min">
            <h1 class="text-2xl font-bold">Registrar Nuevo Producto</h1>

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
                        required
                    />
                </div>

                <!-- Proveedor -->
                <div class="space-y-2">
                    <Label for="proveedor_id">Proveedor</Label>
                    <select
                        id="proveedor_id"
                        v-model="form.proveedor_id"
                        class="w-full border rounded p-2"
                        required
                    >
                        <option value="" disabled>Seleccione un proveedor</option>
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
                        v-model="form.categoria_id"
                        class="w-full border rounded p-2"
                        required
                    >
                        <option value="" disabled>Seleccione una categoría</option>
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
                        Guardar
                    </Button>
                    <Button as="a" href="/productos" variant="outline">
                        Cancelar
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
