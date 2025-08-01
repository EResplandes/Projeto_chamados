<script>
import { ref } from 'vue';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';

export default {
    name: 'LoginPage',
    components: {
        InputText,
        Password,
        Button
    },
    data() {
        return {};
    },
    mounted() {
        let token = localStorage.getItem('token');

        if (token == null) {
            this.$router.push({ name: 'landing' });
        }
    },
    methods: {
        logout() {
            localStorage.clear();
            this.$router.push({ name: 'landing' });
        }
    }
};
</script>

<template>
    <header class="bg-white/80 dark:bg-gray-800/80 darrop-blur-md border-b border-gray-100 px-6 py-3 flex justify-between items-center sticky top-0 z-50">
        <nav class="hidden md:flex gap-1">
            <div class="flex items-center gap-3">
                <div class="bg-blue-600 text-white p-2 rounded-lg">
                    <i class="pi pi-ticket text-xl" />
                </div>
                <div class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-400 bg-clip-text text-transparent">Ticket<span class="text-gray-900">PRO</span></div>
            </div>
        </nav>
        <div class="flex items-center gap-3">
            <Button style="color: white; background-color: #004285" @click.prevent="logout()" label="Sair" icon="pi pi-sign-out" class="p-button-sm p-button-text border border-gray-200 hover:border-blue-200 hover:text-blue-600" />
        </div>
    </header>
</template>
