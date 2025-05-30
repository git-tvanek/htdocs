<script setup>
import { ref, computed, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import axios from 'axios';

const props = defineProps({
    users: Object,
    roles: Array,
    filters: Object,
});

const page = usePage();
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const searchQuery = ref(props.filters?.search || '');
const selectedUser = ref(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: [],
    active: true,
    blocked: false,
});

const permissions = computed(() => page.props.auth.user.permissions || []);

const canCreate = computed(() => 
    permissions.value.includes('users.create') || page.props.auth.user.roles.includes('admin')
);

const canEdit = computed(() => 
    permissions.value.includes('users.update') || page.props.auth.user.roles.includes('admin')
);

const canDelete = computed(() => 
    permissions.value.includes('users.delete') || page.props.auth.user.roles.includes('admin')
);

const canManageAdvanced = computed(() => 
    page.props.auth.user.roles.includes('admin')
);

// Debounced search
const searchDebounce = ref(null);
watch(searchQuery, (value) => {
    clearTimeout(searchDebounce.value);
    searchDebounce.value = setTimeout(() => {
        router.get(route('admin.users.index'), { search: value }, {
            preserveState: true,
            preserveScroll: true,
        });
    }, 300);
});

const openCreateModal = () => {
    form.reset();
    showCreateModal.value = true;
};

const openEditModal = (user) => {
    selectedUser.value = user;
    form.name = user.name;
    form.email = user.email;
    form.roles = user.roles.map(r => r.id);
    form.active = user.active;
    form.blocked = user.blocked;
    form.password = '';
    form.password_confirmation = '';
    showEditModal.value = true;
};

const openDeleteModal = (user) => {
    selectedUser.value = user;
    showDeleteModal.value = true;
};

const createUser = () => {
    form.post(route('admin.users.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        },
    });
};

const updateUser = () => {
    form.put(route('admin.users.update', selectedUser.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            form.reset();
        },
    });
};

const deleteUser = () => {
    router.delete(route('admin.users.destroy', selectedUser.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            selectedUser.value = null;
        },
    });
};

const toggleActive = async (user) => {
    try {
        await axios.post(route('admin.users.toggle-active', user.id));
        router.reload({ only: ['users'] });
    } catch (error) {
        console.error('Error toggling active status:', error);
    }
};

const toggleBlocked = async (user) => {
    try {
        if (user.blocked) {
            await axios.post(route('admin.users.unblock', user.id));
        } else {
            await axios.post(route('admin.users.block', user.id));
        }
        router.reload({ only: ['users'] });
    } catch (error) {
        console.error('Error toggling blocked status:', error);
    }
};

const disable2FA = async (user) => {
    if (confirm('Opravdu chcete vypnout 2FA pro tohoto uživatele?')) {
        try {
            await axios.post(route('admin.users.disable-2fa', user.id));
            router.reload({ only: ['users'] });
        } catch (error) {
            console.error('Error disabling 2FA:', error);
        }
    }
};

const forcePasswordReset = async (user) => {
    if (confirm('Opravdu chcete vynutit reset hesla pro tohoto uživatele?')) {
        try {
            await axios.post(route('admin.users.force-password-reset', user.id));
            router.reload({ only: ['users'] });
        } catch (error) {
            console.error('Error forcing password reset:', error);
        }
    }
};
</script>

<template>
    <AppLayout title="Správa uživatelů">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Správa uživatelů
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <!-- Header with search and create button -->
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex-1 mr-4">
                                <TextInput
                                    v-model="searchQuery"
                                    type="search"
                                    placeholder="Hledat uživatele..."
                                    class="w-full max-w-md"
                                />
                            </div>
                            <PrimaryButton v-if="canCreate" @click="openCreateModal">
                                Přidat uživatele
                            </PrimaryButton>
                        </div>

                        <!-- Users table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Jméno
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Role
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Stav
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            2FA
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Poslední přihlášení
                                        </th>
                                        <th class="relative px-6 py-3">
                                            <span class="sr-only">Akce</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="user in users.data" :key="user.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img 
                                                        class="h-10 w-10 rounded-full" 
                                                        :src="user.profile_photo_url" 
                                                        :alt="user.name"
                                                    >
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ user.name }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ user.email }}</div>
                                            <div v-if="user.email_verified_at" class="text-xs text-green-600">
                                                Ověřeno
                                            </div>
                                            <div v-else class="text-xs text-red-600">
                                                Neověřeno
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span 
                                                v-for="role in user.roles" 
                                                :key="role.id"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-1"
                                            >
                                                {{ role.name }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span v-if="user.blocked" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Blokován
                                            </span>
                                            <span v-else-if="!user.active" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                Neaktivní
                                            </span>
                                            <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Aktivní
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span v-if="user.two_factor_confirmed_at" class="text-green-600">
                                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                            <span v-else class="text-gray-400">
                                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div v-if="user.last_login_at">
                                                {{ new Date(user.last_login_at).toLocaleString('cs-CZ') }}
                                                <div class="text-xs text-gray-400">{{ user.last_login_ip }}</div>
                                            </div>
                                            <div v-else>-</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <button
                                                    v-if="canEdit"
                                                    @click="openEditModal(user)"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Upravit
                                                </button>
                                                
                                                <div v-if="canManageAdvanced" class="relative inline-block text-left">
                                                    <button
                                                        class="text-gray-600 hover:text-gray-900"
                                                        @click="$event.currentTarget.nextElementSibling.classList.toggle('hidden')"
                                                    >
                                                        •••
                                                    </button>
                                                    <div class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                                                        <div class="py-1" role="menu">
                                                            <button
                                                                @click="toggleActive(user)"
                                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left"
                                                            >
                                                                {{ user.active ? 'Deaktivovat' : 'Aktivovat' }}
                                                            </button>
                                                            <button
                                                                @click="toggleBlocked(user)"
                                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left"
                                                            >
                                                                {{ user.blocked ? 'Odblokovat' : 'Blokovat' }}
                                                            </button>
                                                            <button
                                                                v-if="user.two_factor_confirmed_at"
                                                                @click="disable2FA(user)"
                                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left"
                                                            >
                                                                Vypnout 2FA
                                                            </button>
                                                            <button
                                                                @click="forcePasswordReset(user)"
                                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left"
                                                            >
                                                                Vynutit reset hesla
                                                            </button>
                                                            <hr class="my-1">
                                                            <button
                                                                v-if="canDelete && user.id !== page.props.auth.user.id"
                                                                @click="openDeleteModal(user)"
                                                                class="block px-4 py-2 text-sm text-red-700 hover:bg-gray-100 w-full text-left"
                                                            >
                                                                Smazat
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="users.links.length > 3" class="mt-6">
                            <div class="flex justify-center">
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <Link
                                        v-for="link in users.links"
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

        <!-- Create User Modal -->
        <DialogModal :show="showCreateModal" @close="showCreateModal = false">
            <template #title>
                Vytvořit nového uživatele
            </template>

            <template #content>
                <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6">
                        <InputLabel for="name" value="Jméno" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div class="col-span-6">
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="form.errors.email" class="mt-2" />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <InputLabel for="password" value="Heslo" />
                        <TextInput
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="form.errors.password" class="mt-2" />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <InputLabel for="password_confirmation" value="Potvrzení hesla" />
                        <TextInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            required
                        />
                    </div>

                    <div class="col-span-6">
                        <InputLabel value="Role" />
                        <div class="mt-2 space-y-2">
                            <label v-for="role in roles" :key="role.id" class="flex items-center">
                                <input
                                    type="checkbox"
                                    :value="role.id"
                                    v-model="form.roles"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                >
                                <span class="ml-2 text-sm text-gray-600">{{ role.name }}</span>
                            </label>
                        </div>
                        <InputError :message="form.errors.roles" class="mt-2" />
                    </div>
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="showCreateModal = false">
                    Zrušit
                </SecondaryButton>

                <PrimaryButton
                    class="ml-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="createUser"
                >
                    Vytvořit
                </PrimaryButton>
            </template>
        </DialogModal>

        <!-- Edit User Modal -->
        <DialogModal :show="showEditModal" @close="showEditModal = false">
            <template #title>
                Upravit uživatele
            </template>

            <template #content>
                <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6">
                        <InputLabel for="edit-name" value="Jméno" />
                        <TextInput
                            id="edit-name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div class="col-span-6">
                        <InputLabel for="edit-email" value="Email" />
                        <TextInput
                            id="edit-email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="form.errors.email" class="mt-2" />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <InputLabel for="edit-password" value="Nové heslo (nepovinné)" />
                        <TextInput
                            id="edit-password"
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.password" class="mt-2" />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <InputLabel for="edit-password_confirmation" value="Potvrzení hesla" />
                        <TextInput
                            id="edit-password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                        />
                    </div>

                    <div class="col-span-6">
                        <InputLabel value="Role" />
                        <div class="mt-2 space-y-2">
                            <label v-for="role in roles" :key="role.id" class="flex items-center">
                                <input
                                    type="checkbox"
                                    :value="role.id"
                                    v-model="form.roles"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                >
                                <span class="ml-2 text-sm text-gray-600">{{ role.name }}</span>
                            </label>
                        </div>
                        <InputError :message="form.errors.roles" class="mt-2" />
                    </div>

                    <div class="col-span-6">
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                v-model="form.active"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            >
                            <span class="ml-2 text-sm text-gray-600">Aktivní účet</span>
                        </label>
                    </div>
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="showEditModal = false">
                    Zrušit
                </SecondaryButton>

                <PrimaryButton
                    class="ml-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="updateUser"
                >
                    Uložit
                </PrimaryButton>
            </template>
        </DialogModal>

        <!-- Delete User Modal -->
        <DialogModal :show="showDeleteModal" @close="showDeleteModal = false">
            <template #title>
                Smazat uživatele
            </template>

            <template #content>
                <p v-if="selectedUser">
                    Opravdu chcete smazat uživatele <strong>{{ selectedUser.name }}</strong>? 
                    Tato akce je nevratná.
                </p>
            </template>

            <template #footer>
                <SecondaryButton @click="showDeleteModal = false">
                    Zrušit
                </SecondaryButton>

                <DangerButton
                    class="ml-3"
                    @click="deleteUser"
                >
                    Smazat
                </DangerButton>
            </template>
        </DialogModal>
    </AppLayout>
</template>