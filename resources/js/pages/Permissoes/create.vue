<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { ref } from 'vue';
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

const props = defineProps<{
    item?: { id: number; nome: string; nivel: number; descricao: string; ativo: boolean };
    sidebarNavItems: { title: string; href: string }[];
}>();

const isEditing = ref(!!props.item);

const showAlertState = ref(false);
const alertMessage = ref('');
const alertVariant = ref<'success' | 'warning' | 'destructive'>('success');

const form = useForm({
    nome: props.item?.nome || '',
    nivel: props.item?.nivel || 0,
    descricao: props.item?.descricao || '',
    ativo: props.item?.ativo || false
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
    if (isEditing.value) {
        form.put(`/permissoes/${props.item?.id}`, {
            onSuccess: () => {
                showAlert('Permissoes atualizado com sucesso!', 'success');
                formErrors.value = {};
            },
            onError: (errors) => {
                formErrors.value = errors as unknown as Record<string, string[]>;
                const errorMessages = Object.values(formErrors.value).flat().join(', ');
                showAlert(`Erro ao atualizar o permissoes: ${errorMessages}`, 'destructive');
            },
        });
    } else {
        form.post('/permissoes', {
            onSuccess: () => {
                showAlert('Permissoes criado com sucesso!', 'success');
                formErrors.value = {};
            },
            onError: (errors) => {
                formErrors.value = errors as unknown as Record<string, string[]>;
                const errorMessages = Object.values(formErrors.value).flat().join(', ');
                showAlert(`Erro ao criar o permissoes: ${errorMessages}`, 'destructive');
            },
        });
    }
}
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
                    <Label for="nome">Nome do Grupo</Label>
                    <Input id="nome" v-model="form.nome" type="text" placeholder="Digite Nome do Grupo" />
                </div>
                <div>
                    <Label for="nivel">Nivel de Acesso</Label>
                    <Input id="nivel" v-model.number="form.nivel" type="number" step="1" placeholder="Digite Nivel de Acesso" />
                </div>
                <div>
                    <Label for="descricao">Descrição do Grupo</Label>
                    <Textarea id="descricao" v-model="form.descricao" placeholder="Digite Descrição do Grupo" rows="4" />
                </div>
                <div class="flex items-center space-x-2">
                    <Checkbox id="ativo" v-model="form.ativo" />
                    <Label for="ativo">Está ativo?</Label>
                </div>
                        <Button type="submit" class="my-4" :disabled="form.processing">
                            {{ isEditing ? 'Atualizar Permissoes' : 'Criar Permissoes' }}
                        </Button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>