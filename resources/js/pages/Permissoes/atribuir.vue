<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { ref, watch } from 'vue';
import { FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import { useForm } from '@inertiajs/vue3';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem, SelectGroup, SelectLabel } from '@/components/ui/select';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Permissoes', href: '/permissoes' },
];

const headerTitle = 'Permissoes';
const headerDescription = 'Gerencie seus permissoes aqui.';
const isEditing = ref(false); // Define isEditing as a reactive property

const props = defineProps<{
    permissoesOptions: { value: number; label: string }[];
    usuariosOptions: { value: number; label: string, permissaoAtual: number }[];
    sidebarNavItems: { title: string; href: string }[];
}>();

const showAlertState = ref(false);
const alertMessage = ref('');
const alertVariant = ref<'success' | 'warning' | 'destructive'>('success');

const form = useForm<{
    permissoes_id: number | null;
    usuarios_id: number | null;
}>({
    permissoes_id: null,
    usuarios_id: null,
});

const formErrors = ref<Record<string, string[]>>({});
const descricaoMaxLength = 255;

function showAlert(message: string, variant: 'success' | 'warning' | 'destructive' = 'success'): void {
    alertMessage.value = message;
    alertVariant.value = variant;
    showAlertState.value = true;
    setTimeout(() => showAlertState.value = false, 3000);
}

function submitForm() {
    form.post('/permissoes/atribuir', {
        onSuccess: () => {
            showAlert('Permissão atribuída com sucesso!', 'success');
            form.reset();
        },
        onError: (errors) => {
            formErrors.value = errors as unknown as Record<string, string[]>;
            const errorMessages = Object.values(formErrors.value).flat().join(', ');
            showAlert(`Erro ao atribuir a permissão: ${errorMessages}`, 'destructive');
        },
    });
}

watch(() => form.usuarios_id, (newUsuarioId) => {
    const selectedUsuario = props.usuariosOptions.find(usuario => usuario.value === newUsuarioId);
    if (selectedUsuario) {
        form.permissoes_id = selectedUsuario.permissaoAtual;
    } else {
        form.permissoes_id = null;
    }
});
</script>

<template>
    <Head :title="isEditing ? 'Editar Permissoes' : 'Criar Permissoes'" />

    <AppLayout :breadcrumbs="breadcrumbs" :headerTitle="headerTitle" :headerDescription="headerDescription" :sidebarNavItems="props.sidebarNavItems">
        <div class="space-y-6">
            <HeadingSmall :title="isEditing ? 'Editar Permissoes' : 'Criar Novo Permissoes'" description="Gerencie os detalhes do permissoes" />
        </div>
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
                <div class="flex flex-col gap-4 p-4">
                    <h2 class="text-lg font-semibold">{{ isEditing ? 'Editar Permissoes' : 'Criar Novo Permissoes' }}</h2>
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <div>
                            <Label for="usuarios">Usuário</Label>
                            <Select v-model="form.usuarios_id">
                                <SelectTrigger id="usuarios">
                                    <SelectValue placeholder="Selecione um usuário" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>Usuários</SelectLabel>
                                        <SelectItem v-for="usuario in props.usuariosOptions" :key="usuario.value" :value="usuario.value">
                                            {{ usuario.label }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>
                        <div>
                            <Label for="permissoes">Permissão</Label>
                            <Select v-model="form.permissoes_id">
                                <SelectTrigger id="permissoes">
                                    <SelectValue placeholder="Selecione uma permissão" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>Permissões</SelectLabel>
                                        <SelectItem v-for="permissao in props.permissoesOptions" :key="permissao.value" :value="permissao.value">
                                            {{ permissao.label }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>
                        <Button type="submit" class="my-4" :disabled="form.processing">
                            Atribuir Permissão
                        </Button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>