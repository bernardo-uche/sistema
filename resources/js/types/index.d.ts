import { Proveedor } from './index.d';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}
export interface SharedData extends PageProps{
    name: string;
    quote: { message: string; author: string};
    auth: Auth;
    
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;


export interface Employe {
    id: number;
    name: string;
    email: string | null;
    position: string;
    salary: number;
}
//para interfas de cliente

export interface Cliente {
    id: number;
    nombre: string;
    telefono: number;
    direccion: string;


}
// INTERFAS PARA PROVEEDOR
export interface Proveedor {
    id: number;
    nombre: string;
    telefono: number;
    direccion: string;

}

export interface Categoria {
    id: number;
    nombre: string;
}

//interfaces para productos

export interface Producto {
    id: number;
    nombre: string;
    stock: number;
    proveedor: Proveedor;
    precio_unitario: number;
    costo_unitario: number;
    fecha_vencimiento: string;
    categoria: Categoria;
    estado: string;
}