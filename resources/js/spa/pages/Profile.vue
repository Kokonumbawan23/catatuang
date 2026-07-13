<template>
    <div class="py-10 pb-24 sm:pb-0 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="p-4 sm:p-8 bg-white dark:bg-slate-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Profile Information</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Update your account's profile information and email address.</p>
                        </header>

                        <form @submit.prevent="updateProfile" class="mt-6 space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                <input
                                    id="name"
                                    v-model="profileForm.name"
                                    type="text"
                                    required
                                    autofocus
                                    autocomplete="name"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3"
                                />
                                <p v-if="profileErrors.name" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ profileErrors.name }}</p>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                <input
                                    id="email"
                                    v-model="profileForm.email"
                                    type="email"
                                    required
                                    autocomplete="username"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3"
                                />
                                <p v-if="profileErrors.email" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ profileErrors.email }}</p>
                            </div>

                            <div class="flex items-center gap-4">
                                <button
                                    type="submit"
                                    :disabled="profileLoading"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-500 focus:ring ring-indigo-300 disabled:opacity-50 transition"
                                >
                                    Save
                                </button>

                                <p v-if="profileSuccess" class="text-sm text-gray-600 dark:text-gray-400">Saved.</p>
                            </div>
                        </form>
                    </section>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-slate-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Update Password</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Ensure your account is using a long, random password to stay secure.</p>
                        </header>

                        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current Password</label>
                                <input
                                    id="current_password"
                                    v-model="passwordForm.current_password"
                                    type="password"
                                    required
                                    autocomplete="current-password"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3"
                                />
                                <p v-if="passwordErrors.current_password" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ passwordErrors.current_password }}</p>
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">New Password</label>
                                <input
                                    id="password"
                                    v-model="passwordForm.password"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3"
                                />
                                <p v-if="passwordErrors.password" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ passwordErrors.password }}</p>
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                                <input
                                    id="password_confirmation"
                                    v-model="passwordForm.password_confirmation"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3"
                                />
                                <p v-if="passwordErrors.password_confirmation" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ passwordErrors.password_confirmation }}</p>
                            </div>

                            <div class="flex items-center gap-4">
                                <button
                                    type="submit"
                                    :disabled="passwordLoading"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-500 focus:ring ring-indigo-300 disabled:opacity-50 transition"
                                >
                                    Save
                                </button>

                                <p v-if="passwordSuccess" class="text-sm text-gray-600 dark:text-gray-400">Saved.</p>
                            </div>
                        </form>
                    </section>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-slate-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Push Notifications</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Aktifkan notifikasi push untuk menerima alert limit saldo di perangkat Anda.</p>
                        </header>

                        <div class="mt-6 space-y-4">
                            <div v-if="!pushSupported" class="p-3 bg-amber-50 dark:bg-amber-900/30 border border-amber-400 dark:border-amber-600 rounded-md">
                                <p class="text-sm text-amber-700 dark:text-amber-300">Push notifications tidak didukung oleh browser ini.</p>
                            </div>
                            <div v-else class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Status Notifikasi</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ notificationPermission }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        v-if="!pushSubscribed && notificationPermission === 'granted'"
                                        @click="enablePush"
                                        :disabled="pushLoading"
                                        class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 transition"
                                    >
                                        Aktifkan
                                    </button>
                                    <button
                                        v-else-if="pushSubscribed"
                                        @click="disablePush"
                                        :disabled="pushLoading"
                                        class="inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded-md text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50 transition"
                                    >
                                        Nonaktifkan
                                    </button>
                                    <button
                                        v-else
                                        @click="requestPermission"
                                        :disabled="pushLoading"
                                        class="inline-flex items-center px-3 py-1.5 bg-gray-600 border border-transparent rounded-md text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 disabled:opacity-50 transition"
                                    >
                                        Izinkan
                                    </button>
                                </div>
                            </div>
                            <div v-if="pushSubscribed" class="flex items-center gap-2">
                                <button
                                    @click="testPush"
                                    :disabled="testLoading"
                                    class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline disabled:opacity-50"
                                >
                                    Kirim Notifikasi Test
                                </button>
                                <p v-if="testSuccess" class="text-xs text-green-600 dark:text-green-400">Notifikasi terkirim!</p>
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-slate-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section class="space-y-6">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Delete Account</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.</p>
                        </header>

                        <button
                            @click="showDeleteModal = true"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-500 focus:ring ring-red-300 disabled:opacity-50 transition"
                        >
                            Delete Account
                        </button>
                    </section>
                </div>
            </div>

        </div>

        <Transition name="modal">
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 dark:bg-black opacity-75"></div>
                    </div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="p-6">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Are you sure you want to delete your account?</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.</p>

                            <div class="mt-6">
                                <label for="delete_password" class="sr-only">Password</label>
                                <input
                                    id="delete_password"
                                    v-model="deleteForm.password"
                                    type="password"
                                    placeholder="Password"
                                    class="mt-1 block w-3/4 rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3"
                                />
                                <p v-if="deleteError" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ deleteError }}</p>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <button
                                    @click="showDeleteModal = false"
                                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-slate-600 focus:outline-none focus:border-indigo-500 focus:ring ring-indigo-300 transition"
                                >
                                    Cancel
                                </button>
                                <button
                                    @click="deleteAccount"
                                    :disabled="deleteLoading"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-500 focus:ring ring-red-300 disabled:opacity-50 transition"
                                >
                                    Delete Account
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { usePushNotification } from '../composables/usePushNotification';

const router = useRouter();
const authStore = useAuthStore();
const { isSupported, permission, isSubscribed, subscribe, unsubscribe, testNotification } = usePushNotification();

const profileForm = reactive({
    name: '',
    email: '',
});
const passwordForm = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
});
const deleteForm = reactive({
    password: '',
});

const profileErrors = ref({});
const passwordErrors = ref({});
const deleteError = ref('');

const profileLoading = ref(false);
const passwordLoading = ref(false);
const deleteLoading = ref(false);

const profileSuccess = ref(false);
const passwordSuccess = ref(false);
const showDeleteModal = ref(false);

const pushSupported = isSupported;
const notificationPermission = permission;
const pushSubscribed = isSubscribed;
const pushLoading = ref(false);
const testLoading = ref(false);
const testSuccess = ref(false);

const requestPermission = async () => {
    if (!('Notification' in window)) return;
    const result = await Notification.requestPermission();
    if (result === 'granted') {
        await enablePush();
    }
};

const enablePush = async () => {
    pushLoading.value = true;
    try {
        await subscribe();
    } catch (error) {
        console.error('Failed to enable push:', error);
    } finally {
        pushLoading.value = false;
    }
};

const disablePush = async () => {
    pushLoading.value = true;
    try {
        await unsubscribe();
    } catch (error) {
        console.error('Failed to disable push:', error);
    } finally {
        pushLoading.value = false;
    }
};

const testPush = async () => {
    testLoading.value = true;
    testSuccess.value = false;
    try {
        await testNotification();
        testSuccess.value = true;
        setTimeout(() => { testSuccess.value = false; }, 3000);
    } catch (error) {
        console.error('Failed to send test notification:', error);
    } finally {
        testLoading.value = false;
    }
};

const fetchProfile = async () => {
    try {
        const response = await axios.get('/api/profile');
        profileForm.name = response.data.user.name;
        profileForm.email = response.data.user.email;
    } catch (error) {
        console.error('Failed to fetch profile:', error);
    }
};

const updateProfile = async () => {
    profileLoading.value = true;
    profileErrors.value = {};
    profileSuccess.value = false;
    try {
        const response = await axios.patch('/api/profile', profileForm);
        profileSuccess.value = true;
        authStore.user = response.data.user;
        setTimeout(() => { profileSuccess.value = false; }, 2000);
    } catch (error) {
        if (error.response?.data?.errors) {
            profileErrors.value = error.response.data.errors;
        } else {
            profileErrors.value = { general: error.response?.data?.message || 'Failed to update profile.' };
        }
    } finally {
        profileLoading.value = false;
    }
};

const updatePassword = async () => {
    passwordLoading.value = true;
    passwordErrors.value = {};
    passwordSuccess.value = false;
    try {
        await axios.put('/api/profile/password', passwordForm);
        passwordSuccess.value = true;
        passwordForm.current_password = '';
        passwordForm.password = '';
        passwordForm.password_confirmation = '';
        setTimeout(() => { passwordSuccess.value = false; }, 2000);
    } catch (error) {
        if (error.response?.data?.errors) {
            passwordErrors.value = error.response.data.errors;
        } else {
            passwordErrors.value = { general: error.response?.data?.message || 'Failed to update password.' };
        }
    } finally {
        passwordLoading.value = false;
    }
};

const deleteAccount = async () => {
    deleteLoading.value = true;
    deleteError.value = '';
    try {
        await axios.delete('/api/profile', { data: deleteForm });
        await authStore.logout();
        router.push({ name: 'login' });
    } catch (error) {
        deleteError.value = error.response?.data?.errors?.password?.[0] || error.response?.data?.message || 'Failed to delete account.';
    } finally {
        deleteLoading.value = false;
    }
};

onMounted(() => {
    fetchProfile();
});
</script>
