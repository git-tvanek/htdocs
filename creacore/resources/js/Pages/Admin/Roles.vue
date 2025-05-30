<script setup>
import { ref, computed } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    roles: Object,
    permissions: Array,
});

const page = usePage();
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedRole = ref(null);

const form = useForm({
    name: '',
    permissions: [],
});

const userPermissions = computed(() => page.props.auth.user.permissions || []);

const canCreate = computed(() => 
    userPermissions.value.includes('roles.create') || page.props.auth.user.roles.includes('admin')
);

const canEdit = computed(() => 
    userPermissions.value.includes('roles.update') || page.props.auth.user.roles.includes('admin')
);

const canDelete = computed(() => 
    userPermissions.value.includes('roles.delete') || page.props.auth.user.roles.includes('admin')
);

// Group permissions by resource
const groupedPermissions = computed(() => {
    const grouped = {};
    props.permissions.forEach(permission => {
        const [resource, action] = permission.name.split('.');
        if (!grouped[resource]) {
            grouped[resource] = [];
        }
        grouped[resource].push({
            id: permission.id,
            name: permission.name,
            action: action || 'other',
        });
    });
    return grouped;
});

const openCreateModal = () => {
    form.reset();
    showCreateModal.value = true;
};

const openEditModal = (role) => {
    selectedRole.value = role;
    form.name = role.name;
    form.permissions = role.permissions.map(p => p.id);
    showEditModal.value = true;
};

const openDeleteModal = (role) => {
    selectedRole.value = role;
    showDeleteModal.value = true;
};

const createRole = () => {
    form.post(route('admin.roles.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        },
    });
};

const updateRole = () => {
    form.put(route('admin.roles.update', selectedRole.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            form.reset();
        },
    });
};

const deleteRole = () => {
    router.delete(route('admin.roles.destroy', selectedRole.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            selectedRole.value = null;
        },
    });
};

const toggleAllPermissions = (resource) => {
    const resourcePermissions = groupedPermissions.value[resource];
    const allSelected = resourcePermissions.every(p => form.permissions.includes(p.id));
    
    if (allSelected) {
        form.permissions = form.permissions.filter(id => 
            !resourcePermissions.some(p => p.id === id)
        );
    } else {
        const newPermissions = resourcePermissions.map(p => p.id);
        form.permissions = [...new Set([...form.permissions, ...newPermissions])];
    }
};

const isResourceFullySelected = (resource) => {
    const resourcePermissions = groupedPermissions.value[resource];
    return resourcePermissions.every(p => form.permissions.includes(p.id));
};

const isResourcePartiallySelected = (resource) => {
    const resourcePermissions = groupedPermissions.value[resource];
    const selectedCount = resourcePermissions.filter(p => form.permissions.includes(p.id)).length;
    return selectedCount > 0 && selectedCount < resourcePermissions.length;
};
</script>

<template>
    <AppLayout title="Správa rolí">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Správa rolí
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <!-- Header with create button -->
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900">
                                Seznam rolí
                            </h3>
                            <PrimaryButton v-if="canCreate" @click="openCreateModal">
                                Přidat roli
                            </PrimaryButton>
                        </div>

                        <!-- Roles table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Název
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Počet uživatelů
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Oprávnění
                                        </th>
                                        <th class="relative px-6 py-3">
                                            <span class="sr-only">Akce</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="role in roles.data" :key="role.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ role.name }}
                                            </div>
                                            <div v-if="role.name === 'admin'" class="text-xs text-gray-500">
                                                Systémová role
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ role.users_count }} uživatelů
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap gap-1">
                                                <span 
                                                    v-for="permission in role.permissions.slice(0, 5)" 
                                                    :key="permission.id"
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800"
                                                >
                                                    {{ permission.name }}
                                                </span>
                                                <span 
                                                    v-if="role.permissions.length > 5"
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800"
                                                >
                                                    +{{ role.permissions.length - 5 }} dalších
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <button
                                                    v-if="canEdit"
                                                    @click="openEditModal(role)"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Upravit
                                                </button>
                                                <button
                                                    v-if="canDelete && role.name !== 'admin'"
                                                    @click="openDeleteModal(role)"
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    Smazat
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="roles.links.length > 3" class="mt-6">
                            <div class="flex justify-center">
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <Link
                                        v-for="link in roles.links"
                                        :key="link.label"
                                        :href="link.url"
                                        :class="[
                                            'relative inline-flex items-center px-4 py-2 text-sm font-medium',
                                            link.active
                                                ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                            !link.url && 'cursor-not-allowed opacity-50',
                                            link.label.includes('Previous') && 'rounded-l-md',
                                            link.label.includes('Next') && 'rounded-r-md',
                                        ]"
                                        :disabled="!link.url"
                                        v-html="link.label"
                                    />
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Role Modal -->
        <DialogModal :show="showCreateModal || showEditModal" @close="showCreateModal = showEditModal = false">
            <template #title>
                {{ showCreateModal ? 'Vytvořit novou roli' : 'Upravit roli' }}
            </template>

            <template #content>
                <div class="space-y-6">
                    <div>
                        <InputLabel for="name" value="Název role" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel value="Oprávnění" />
                        <div class="mt-2 space-y-4">
                            <div v-for="(permissions, resource) in groupedPermissions" :key="resource" class="border rounded-lg p-4">
                                <div class="flex items-center mb-2">
                                    <input
                                        type="checkbox"
                                        :checked="isResourceFullySelected(resource)"
                                        :indeterminate="isResourcePartiallySelected(resource)"
                                        @change="toggleAllPermissions(resource)"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    >
                                    <label class="ml-2 font-medium text-gray-700 capitalize">
                                        {{ resource }}
                                    </label>
                                </div>
                                <div class="ml-6 grid grid-cols-2 gap-2">
                                    <label v-for="permission in permissions" :key="permission.id" class="flex items-center">
                                        <input
                                            type="checkbox"
                                            :value="permission.id"
                                            v-model="form.permissions"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        >
                                        <span class="ml-2 text-sm text-gray-600">{{ permission.action }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <InputError :message="form.errors.permissions" class="mt-2" />
                    </div>
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="showCreateModal = showEditModal = false">
                    Zrušit
                </SecondaryButton>

                <PrimaryButton
                    class="ml-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="showCreateModal ? createRole() : updateRole()"
                >
                    {{ showCreateModal ? 'Vytvořit' : 'Uložit' }}
                </PrimaryButton>
            </template>
        </DialogModal>

        <!-- Delete Role Modal -->
        <DialogModal :show="showDeleteModal" @close="showDeleteModal = false">
            <template #title>
                Smazat roli
            </template>

            <template #content>
                <p v-if="selectedRole">
                    Opravdu chcete smazat roli <strong>{{ selectedRole.name }}</strong>? 
                    Tato akce je nevratná a všichni uživatelé s touto rolí ji ztratí.
                </p>
            </template>

            <template #footer>
                <SecondaryButton @click="showDeleteModal = false">
                    Zrušit
                </SecondaryButton>

                <DangerButton
                    class="ml-3"
                    @click="deleteRole"
                >
                    Smazat
                </DangerButton>
            </template>
        </DialogModal>
    </AppLayout>
</template>