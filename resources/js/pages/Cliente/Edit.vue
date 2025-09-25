<script setup lang="ts">
// Página de edición de Cliente.
// Recibe los datos de "cliente" como props desde Laravel (Inertia::render) y
// rellena un formulario reactivo que se envía con router.put.
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { onMounted, reactive } from 'vue';

type BreadcrumbItem = { title: string; href: string };
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Cliente', href: '/cliente' },
    { title: 'Editar', href: '#' },
];

// Obtenemos props pasados desde Laravel a través de Inertia usePage().
const page: any = usePage();
const cliente = page.props.cliente;

// Formulario reactivo (se llena con los datos del cliente)
const form = reactive<{ id?: number; nombre: string; telefono: string; direccion: string }>({
    id: undefined,
    nombre: '',
    telefono: '',
    direccion: '',
});

// Cargar datos en el formulario al montar el componente.
onMounted(() => {
    if (cliente) {
        form.id = cliente.id;
        form.nombre = cliente.nombre;
        form.telefono = cliente.telefono;
        form.direccion = cliente.direccion;
    }
});

// Enviar actualización mediante PUT a la ruta resource('cliente') -> cliente.update
const submit = () => {
    if (!form.id) return;

    router.put(`/cliente/${form.id}`, form, {
        onSuccess: () => {
            console.log('Cliente actualizado correctamente');
        },
    });
};
</script>

<template>
    <Head title="Editar Cliente" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 rounded-xl p-4">
            <h1 class="text-2xl font-bold">Editar Cliente</h1>

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
                        Actualizar
                    </Button>
                    <Button as="a" href="/cliente" variant="outline">
                        Cancelar
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
