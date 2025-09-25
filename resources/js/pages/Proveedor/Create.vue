<script setup lang="ts">
// Página para crear un nuevo Proveedor.
// Usa un formulario reactivo simple y envía datos al backend con Inertia router.post.
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from "vue";
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type BreadcrumbItem = { title: string; href: string };
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Proveedor', href: '/proveedor' },
    { title: 'Create', href: '#' },
];

// Formulario Cliente (coincide con el modelo Cliente.php)
const form = ref<Partial<{ nombre: string; telefono: string; direccion: string }>>({
    nombre: '',
    telefono: '',
    direccion: '',
});

const resetForm = () => {
    form.value = { nombre: '', telefono: '', direccion: '' };
};

const submit = () => {
    // Envía POST a resource('proveedor') -> proveedor.store
    router.post('/proveedor', form.value, { onSuccess: resetForm });
};
</script>

<template>
    <Head title="Crear Proveedor" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 rounded-xl p-4">
            <h1 class="text-2xl font-bold">Registrar Nuevo Proovedor</h1>

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
                        placeholder="Ingrese la dirección del cliente"
                        required
                    />
                </div>

                <!-- Botones -->
                <div class="flex gap-4">
                    <Button type="submit" class="bg-indigo-500 hover:bg-indigo-600">
                        Guardar
                    </Button>
                    <Button as="a" href="/proveedor" variant="outline">
                        Cancelar
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
