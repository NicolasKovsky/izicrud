<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { usePage } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sem Permissão',
        href: '/sem-permissao',
    },
];



</script>

<template>
    <Head title="Sem Permissão" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- Exibe a mensagem de permissão -->
            <div class="p-4 text-white bg-red-500 rounded-md">
                <p>Você não tem permissão para acessar esta página.</p>
            </div>

            
        </div>
    </AppLayout>
</template>
