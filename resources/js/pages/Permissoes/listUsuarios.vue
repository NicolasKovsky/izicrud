<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Permissoes', href: '/permissoes' },
];

const headerTitle = 'Permissoes';
const headerDescription = 'Gerencie seus permissoes aqui.';

const props = defineProps<{
    usuarios: { id: number; name: string; permissao: { id: number; nome: string } | null }[];
    sidebarNavItems: { title: string; href: string }[];
    itens: { current_page: number; last_page: number };
}>();

const showAlertState = ref(false);
const searchQuery = ref('');
const alertMessage = ref('');
const alertVariant = ref<'success' | 'warning' | 'destructive'>('success');
const showDeleteDialog = ref(false);
const itemToDelete = ref<number | null>(null);

const sortColumn = ref<string | null>(null);
const sortDirection = ref<'asc' | 'desc'>('asc');

const filteredUsuarios = computed(() => {
    const query = searchQuery.value.toLowerCase();
    return props.usuarios.filter(usuario =>
        usuario.name.toLowerCase().includes(query) ||
        (usuario.permissao?.nome || '').toLowerCase().includes(query)
    );
});

function toggleSort(column: string) {
    if (sortColumn.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = column;
        sortDirection.value = 'asc';
    }
}

function showAlert(message: string, variant: 'success' | 'warning' | 'destructive' = 'success'): void {
    alertMessage.value = message;
    alertVariant.value = variant;
    showAlertState.value = true;
    setTimeout(() => showAlertState.value = false, 3000);
}

function confirmDelete(itemId: number): void {
    itemToDelete.value = itemId;
    showDeleteDialog.value = true;
}

function deleteItem(): void {
    if (itemToDelete.value !== null) {
        router.delete(`/permissoes/${itemToDelete.value}`, {
            onSuccess: () => {
                showAlert('Permissoes excluído com sucesso!', 'success');
                showDeleteDialog.value = false;
                itemToDelete.value = null;
            },
            onError: () => {
                showAlert('Erro ao excluir o permissoes.', 'destructive');
                showDeleteDialog.value = false;
            },
        });
    }
}

function goToPage(page: number) {
    router.get('/permissoes', { page }, { preserveState: true, preserveScroll: true });
}

const currentPage = computed(() => props.itens.current_page);
const lastPage = computed(() => props.itens.last_page);
const canGoPrevious = computed(() => currentPage.value > 1);
const canGoNext = computed(() => currentPage.value < lastPage.value);
</script>

<template>
    <Head title="Usuários" />

    <AppLayout :breadcrumbs="breadcrumbs" :headerTitle="'Usuários'" :headerDescription="'Gerencie seus usuários aqui.'" :sidebarNavItems="props.sidebarNavItems">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <Alert v-if="showAlertState" class="mb-4" :class="{
                'bg-green-100 border-green-500 text-green-900': alertVariant === 'success',
                'bg-yellow-100 border-yellow-500 text-yellow-900': alertVariant === 'warning',
                'bg-red-100 border-red-500 text-red-900': alertVariant === 'destructive',
            }">
                <AlertTitle>Ação Realizada</AlertTitle>
                <AlertDescription>{{ alertMessage }}</AlertDescription>
            </Alert>

            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
                <div class="mb-4">
                    <input v-model="searchQuery" type="text" placeholder="Pesquisar usuários..." class="w-full px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="cursor-pointer" @click="toggleSort('name')">Nome<span v-if="sortColumn === 'name'" class="ml-2">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span></TableHead>
                            <TableHead class="cursor-pointer" @click="toggleSort('permissao')">Permissão<span v-if="sortColumn === 'permissao'" class="ml-2">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="(usuario, index) in filteredUsuarios" :key="index">
                            <TableCell>{{ usuario.name }}</TableCell>
                            <TableCell>{{ usuario.permissao?.nome || 'Sem Permissão' }}</TableCell>
                            
                        </TableRow>
                    </TableBody>
                </Table>

                <div class="flex items-center justify-between px-4 py-2">
                    <div>
                        <p class="text-sm text-gray-700">
                            Mostrando {{ filteredUsuarios.length }} de {{ props.usuarios.length }} usuários
                        </p>
                    </div>
                </div>
            </div>

            <Dialog v-model:open="showDeleteDialog">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Confirmar Exclusão</DialogTitle>
                        <DialogDescription>
                            Tem certeza de que deseja excluir este usuário? Esta ação não pode ser desfeita.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" @click="showDeleteDialog = false">Cancelar</Button>
                        <Button variant="destructive" @click="deleteItem">Excluir</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>


