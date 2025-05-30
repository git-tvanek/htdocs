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
    permissions: Object,
});

const page = usePage();
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedPermission = ref(null);

const form = useForm({
    name: '',
    guard_name: 'web',
});

const userPermissions = computed(() => page.props.auth.user.permissions || []);

const canCreate = computed(() => 
    userPermissions.value.includes('permissions.create') || page.props.auth.user.roles.includes('admin')
);

const canEdit = computed(() => 
    userPermissions.value.includes('permissions.update') || page.props.auth.user.roles.includes('admin')
);

const canDelete = computed(() => 
    userPermissions.value.includes('permissions.delete') || page.props.auth.user.roles.includes('admin')
);

const openCreateModal = () => {
    form.reset();
    form.guard_name = 'web';
    showCreateModal.value = true;
};

const openEditModal = (permission) => {
    selectedPermission.value = permission;
    form.name = permission.name;
    form.guard_name = permission.guard_name;
    showEditModal.value = true;
};

const openDeleteModal = (permission) => {
    selectedPermission.value = permission;
    showDeleteModal.value = true;
};

const createPermission = () => {
    form.post(route('admin.permissions.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        },
    });
};

const updatePermission = () => {
    form.put(route('admin.permissions.update', selectedPermission.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            form.reset();
        },
    });
};

const deletePermission = () => {
    router.delete(route('admin.permissions.destroy', selectedPermission.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            selectedPermission.value = null;
        },
    });
};

// Helper to format permission name
const formatPermissionName = (name) => {
    const [resource, action] = name.split('.');
    return {
        resource: resource.charAt(0).toUpperCase() + resource.slice(1),
        action: action ? action.replace('-', ' ') : ''
    };
};
</script>

<template>
    <AppLayout title="Správa oprávnění">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Správa oprávnění
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <!-- Header with create button -->
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900">
                                Seznam oprávnění
                            </h3>
                            <PrimaryButton v-if="canCreate" @click="openCreateModal">
                                Přidat oprávnění
                            </PrimaryButton>
                        </div>

                        <!-- Info box -->
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        Oprávnění používejte ve formátu <code class="font-mono bg-blue-100 px-1 rounded">resource.action</code>, 
                                        například <code class="font-mono bg-blue-100 px-1 rounded">users.create</code> nebo 
                                        <code class="font-mono bg-blue-100 px-1 rounded">posts.publish</code>.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Permissions table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Název
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Guard
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Role
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Vytvořeno
                                        </th>
                                        <th class="relative px-6 py-3">
                                            <span class="sr-only">Akce</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="permission in permissions.data" :key="permission.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ permission.name }}
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        {{ formatPermissionName(permission.name).resource }} - 
                                                        {{ formatPermissionName(permission.name).action }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ permission.guard_name }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap gap-1">
                                                <span 
                                                    v-for="role in permission.roles" 
                                                    :key="role.id"
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800"
                                                >
                                                    {{ role.name }}
                                                </span>
                                                <span 
                                                    v-if="permission.roles.length === 0"
                                                    class="text-xs text-gray-500"
                                                >
                                                    Žádné role
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ new Date(permission.created_at).toLocaleDateString('cs-CZ') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <button
                                                    v-if="canEdit"
                                                    @click="openEditModal(permission)"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Upravit
                                                </button>
                                                <button
                                                    v-if="canDelete"
                                                    @click="openDeleteModal(permission)"
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
                        <div v-if="permissions.links.length > 3" class="mt-6">
                            <div class="flex justify-center">
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <Link
                                        v-for="link in permissions.links"
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

        <!-- Create/Edit Permission Modal -->
        <DialogModal :show="showCreateModal || showEditModal" @close="showCreateModal = showEditModal = false">
            <template #title>
                {{ showCreateModal ? 'Vytvořit nové oprávnění' : 'Upravit oprávnění' }}
            </template>

            <template #content>
                <div class="space-y-6">
                    <div>
                        <InputLabel for="name" value="Název oprávnění" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="např. users.create"
                            required
                        />
                        <p class="mt-1 text-sm text-gray-500">
                            Používejte formát resource.action (např. users.create, posts.publish)
                        </p>
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="guard_name" value="Guard" />
                        <select
                            id="guard_name"
                            v-model="form.guard_name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="web">Web</option>
                            <option value="api">API</option>
                        </select>
                        <InputError :message="form.errors.guard_name" class="mt-2" />
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
                    @click="showCreateModal ? createPermission() : updatePermission()"
                >
                    {{ showCreateModal ? 'Vytvořit' : 'Uložit' }}
                </PrimaryButton>
            </template>
        </DialogModal>

        <!-- Delete Permission Modal -->
        <DialogModal :show="showDeleteModal" @close="showDeleteModal = false">
            <template #title>
                Smazat oprávnění
            </template>

            <template #content>
                <p v-if="selectedPermission">
                    Opravdu chcete smazat oprávnění <strong>{{ selectedPermission.name }}</strong>? 
                    Tato akce je nevratná a oprávnění bude odebráno ze všech rolí.
                </p>
            </template>

            <template #footer>
                <SecondaryButton @click="showDeleteModal = false">
                    Zrušit
                </SecondaryButton>

                <DangerButton
                    class="ml-3"
                    @click="deletePermission"
                >
                    Smazat
                </DangerButton>
            </template>
        </DialogModal>
    </AppLayout>
</template>