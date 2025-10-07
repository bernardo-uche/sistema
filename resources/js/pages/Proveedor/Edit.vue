<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { onMounted, reactive } from 'vue';

type BreadcrumbItem = { title: string; href: string };
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Proveedor', href: '/proveedor' },
    { title: 'Editar', href: '#' },
];

// Obtenemos props pasados desde Laravel
const page: any = usePage();
const proveedor = page.props.proveedor;

// Formulario reactivo (se llena con los datos del cliente)
const form = reactive<{ id?: number; nombre: string; telefono: string; direccion: string }>({
    id: undefined,
    nombre: '',
    telefono: '',
    direccion: '',
});

// Cargar datos en el formulario al montar el componente
onMounted(() => {
    if (proveedor) {
        form.id = proveedor.id;
        form.nombre = proveedor.nombre;
        form.telefono = proveedor.telefono;
        form.direccion = proveedor.direccion;
    }
});

// Enviar actualización
const submit = () => {
    if (!form.id) return;

    router.put(`/proveedor/${form.id}`, form, {
        onSuccess: () => {
            console.log('Cliente actualizado correctamente');
        },
    });
};
</script>

<template>
    <Head title="Editar Proveedor" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 -xl p-4 bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-300 dark:border-sidebar-border md:min-h-min">
            <h1 class="text-2xl font-bold">Editar Proovedor</h1>

            <form @submit.prevent="submit" class="max-w-lg space-y-6">
                <!-- Nombre -->
                <div class="space-y-2">
                    <Label for="nombre">Nombre</Label>
                    <Input
                        id="nombre"
                        v-model="form.nombre"
                        type="text"
                        placeholder="Ingrese el nombre del cliente"
                        required
                    />
                </div>

                <!-- Teléfono -->
                <div class="space-y-2">
                    <Label for="telefono">Teléfono</Label>
                    <Input
                        id="telefono"
                        v-model="form.telefono"
                        type="text"
                        placeholder="Ej: 77463268"
                    />
                </div>

                <!-- Dirección -->
                <div class="space-y-2">
                    <Label for="direccion">Dirección</Label>
                    <Input
                        id="direccion"
                        v-model="form.direccion"
                        type="text"
                        placeholder="Ingrese la dirección del prooveedor"
                        required
                    />
                </div>

                <!-- Botones -->
                <div class="flex gap-4">
                    <Button type="submit" class="bg-indigo-500 hover:bg-indigo-600">
                        Actualizar
                    </Button>
                    <Button as="a" href="/proveedor" variant="outline">
                        Cancelar
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
