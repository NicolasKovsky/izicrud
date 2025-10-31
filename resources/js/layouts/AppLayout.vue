<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { type NavItem } from '@/types';
import Heading from '@/components/Heading.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Separator } from '@/components/ui/separator';
import { Button } from '@/components/ui/button';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    headerTitle?: string;
    headerDescription?: string;
    sidebarNavItems?: { title: string; href: string }[]; // Propriedade opcional para a barra lateral
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    headerTitle: '',
    headerDescription: '',
    sidebarNavItems: () => [],
});

interface ZiggyProps {
    location?: string;
}

const page = usePage<{ ziggy?: ZiggyProps }>();
// Computed para tornar o currentPath reativo
const currentPath = computed(() => {
    return page.props.ziggy?.location ? new URL(page.props.ziggy.location).pathname : '';
});


</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6">

            <div class="flex flex-col space-y-8 md:space-y-0 lg:flex-row lg:space-x-12 lg:space-y-0">
                <aside v-if="sidebarNavItems && sidebarNavItems.length" class="w-full lg:w-1/6">
                    <Heading :title="headerTitle" :description="headerDescription" />
                    <nav class="flex flex-col space-x-0 space-y-1">
                        <Button v-for="item in sidebarNavItems" :key="item.href" variant="ghost"
                            :class="['w-full justify-start', { 'bg-muted': currentPath === item.href }]" as-child>
                            <Link :href="item.href">
                            {{ item.title }}
                            </Link>
                        </Button>
                    </nav>
                </aside>

                <Separator class="my-6 md:hidden" />

                <div class="flex-1">
                    <section>
                        <slot />
                    </section>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
