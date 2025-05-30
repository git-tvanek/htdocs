<script setup>
import { ref, onMounted } from 'vue';
import Chart from 'chart.js/auto';
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    stats: Object,
    recentUsers: Array,
    recentActivities: Array,
});

const page = usePage();

// Chart data
const userGrowthData = ref(null);
const roleDistributionData = ref(null);

// Load chart data
onMounted(async () => {
    try {
        const response = await axios.get(route('api.dashboard.charts'));
        userGrowthData.value = response.data.userGrowth;
        roleDistributionData.value = response.data.roleDistribution;
        
        // Initialize charts
        initializeUserGrowthChart();
        initializeRoleDistributionChart();
    } catch (error) {
        console.error('Error loading chart data:', error);
    }
});

const initializeUserGrowthChart = () => {
    if (!userGrowthData.value) return;
    
    const ctx = document.getElementById('userGrowthChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: userGrowthData.value.labels,
            datasets: [{
                label: 'Nové registrace',
                data: userGrowthData.value.data,
                borderColor: 'rgb(79, 70, 229)',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
};

const initializeRoleDistributionChart = () => {
    if (!roleDistributionData.value) return;
    
    const ctx = document.getElementById('roleDistributionChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: roleDistributionData.value.labels,
            datasets: [{
                data: roleDistributionData.value.data,
                backgroundColor: [
                    'rgb(79, 70, 229)',
                    'rgb(59, 130, 246)',
                    'rgb(34, 197, 94)',
                    'rgb(251, 146, 60)',
                    'rgb(236, 72, 153)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
};

// Helper functions
const formatDateTime = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString('cs-CZ');
};

const getActivityIcon = (activity) => {
    const icons = {
        'Created user': '👤',
        'Updated user': '✏️',
        'Deleted user': '🗑️',
        'Created role': '🎭',
        'Updated role': '✏️',
        'Deleted role': '🗑️',
        'Login': '🔐',
        'Logout': '🚪'
    };
    return icons[activity.description] || '📝';
};

const getActivityColor = (activity) => {
    if (activity.description.includes('Deleted')) return 'text-red-600';
    if (activity.description.includes('Created')) return 'text-green-600';
    if (activity.description.includes('Updated')) return 'text-blue-600';
    return 'text-gray-600';
};
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <!-- Total Users -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Celkem uživatelů
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            {{ stats.total_users }}
                                        </div>
                                        <div v-if="stats.new_users_today > 0" class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                                            +{{ stats.new_users_today }} dnes
                                        </div>
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Users -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Aktivní uživatelé
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            {{ stats.active_users }}
                                        </div>
                                        <div class="ml-2 text-sm text-gray-500">
                                            {{ Math.round((stats.active_users / stats.total_users) * 100) }}%
                                        </div>
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Roles -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Počet rolí
                                    </dt>
                                    <dd class="text-2xl font-semibold text-gray-900">
                                        {{ stats.total_roles }}
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Permissions -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Počet oprávnění
                                    </dt>
                                    <dd class="text-2xl font-semibold text-gray-900">
                                        {{ stats.total_permissions }}
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
                    <!-- User Growth Chart -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Růst uživatelů
                            </h3>
                            <div class="h-64">
                                <canvas id="userGrowthChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Role Distribution Chart -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Rozdělení rolí
                            </h3>
                            <div class="h-64">
                                <canvas id="roleDistributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Users & Activities -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- Recent Users -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Nejnovější uživatelé
                            </h3>
                            <div class="flow-root">
                                <ul class="-my-5 divide-y divide-gray-200">
                                    <li v-for="user in recentUsers" :key="user.id" class="py-4">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex-shrink-0">
                                                <img class="h-8 w-8 rounded-full" :src="user.profile_photo_url" :alt="user.name">
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">
                                                    {{ user.name }}
                                                </p>
                                                <p class="text-sm text-gray-500 truncate">
                                                    {{ user.email }}
                                                </p>
                                            </div>
                                            <div>
                                                <span 
                                                    v-for="role in user.roles" 
                                                    :key="role.id"
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                                >
                                                    {{ role.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Poslední aktivity
                            </h3>
                            <div class="flow-root">
                                <ul class="-mb-8">
                                    <li v-for="(activity, index) in recentActivities" :key="activity.id">
                                        <div class="relative pb-8">
                                            <span v-if="index !== recentActivities.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center ring-8 ring-white">
                                                        <span class="text-sm">{{ getActivityIcon(activity) }}</span>
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm" :class="getActivityColor(activity)">
                                                            {{ activity.description }}
                                                            <span v-if="activity.causer" class="font-medium text-gray-900">
                                                                - {{ activity.causer.name }}
                                                            </span>
                                                        </p>
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                        <time :datetime="activity.created_at">
                                                            {{ new Date(activity.created_at).toLocaleString('cs-CZ') }}
                                                        </time>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>