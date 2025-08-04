<script>
import { ref } from 'vue';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import UsuariosService from '../service/UsuariosService.js';

export default {
    name: 'LoginPage',
    components: {
        InputText,
        Password,
        Button
    },
    data() {
        return {
            tipo_usuario: JSON.parse(localStorage.getItem('usuario'))?.tipo_usuario,
            primeiro_acesso: null,
            dialogPrimeiroAcesso: false,
            form: {
                novaSenha1: '',
                novaSenha2: ''
            },
            erro_senhas: false,
            usuariosService: new UsuariosService()
        };
    },
    mounted() {
        let acesso = (this.primeiro_acesso = JSON.parse(localStorage.getItem('usuario'))?.primeiro_acesso);

        let token = localStorage.getItem('token');

        if (acesso == 1) {
            this.dialogPrimeiroAcesso = true;
        } else {
            this.dialogPrimeiroAcesso = false;
        }

        if (token == null) {
            this.$router.push({ name: 'landing' });
        }
    },
    methods: {
        logout() {
            localStorage.clear();
            this.$router.push({ name: 'landing' });
        },

        alteraSenhaAtual() {
            if (this.form.novaSenha1 !== this.form.novaSenha2) {
                this.erro_senhas = true;
                return;
            } else {
                this.erro_senhas = false;
            }

            this.usuariosService
                .alteraSenhaAtual(this.form.novaSenha1)
                .then((data) => {
                    if (data.status == 'success') {
                        this.dialogPrimeiroAcesso = false;
                        let usuario = JSON.parse(localStorage.getItem('usuario'));

                        if (usuario) {
                            usuario.primeiro_acesso = 0;
                            localStorage.setItem('usuario', JSON.stringify(usuario));
                            this.mensagemSucesso('Senha alterada com sucesso!');
                        }
                    } else {
                        this.erro_senhas = true;
                    }
                })
                .catch((error) => {});
        },
        /*************  ✨ Windsurf Command ⭐  *************/
        /**
         * Displays a success message as a toast notification.
         *
         * @param {string} mensagem - The message to display in the toast notification.
         */

        /*******  dcd1cb0b-85bb-4773-8369-cb35d344f18e  *******/
        mensagemSucesso(mensagem) {
            this.toast.add({ severity: 'info', summary: 'Mensagem', detail: mensagem, life: 3000 });
        }
    }
};
</script>

<template>
    <Toast />
    <Dialog :closable="false" v-model:visible="dialogPrimeiroAcesso" modal header="Mudança de Senha" :style="{ width: '40rem', borderRadius: '12px' }" headerClass="dialog-header-blue">
        <hr class="border-blue-200" />
        <span class="text-gray-600 block mb-5 text-sm"> Bem-vindo(a)! Para garantir a segurança da sua conta, é necessário definir uma nova senha no seu primeiro acesso. </span>
        <hr class="border-blue-200 mb-4" />

        <div class="mb-3">
            <label for="email" class="font-semibold block mb-2 text-gray-800">Digite uma nova senha:</label>
            <Password id="password1" v-model="form.novaSenha1" placeholder="Digite sua senha..." :feedback="false" :toggleMask="true" class="w-full mb-3 custom-password" inputClass="w-full" toggleMaskClass="text-gray-500"></Password>
        </div>

        <div class="mb-5">
            <label for="email" class="font-semibold block mb-2 text-gray-800">Digite a mesma senha:</label>
            <Password id="password1" v-model="form.novaSenha2" placeholder="Digite sua senha..." :feedback="false" :toggleMask="true" class="w-full mb-3 custom-password" inputClass="w-full" toggleMaskClass="text-gray-500"></Password>
        </div>

        <div v-if="erro_senhas" class="mb-5">
            <Message severity="error" class="border-red-300 bg-red-50 text-red-700"> As senhas digitadas devem ser iguais. </Message>
        </div>

        <div class="flex justify-end gap-2">
            <Button class="w-full" type="button" label="Salvar" severity="info" @click="alteraSenhaAtual()" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)"></Button>
        </div>
    </Dialog>

    <header class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md border-b border-gray-100 px-6 py-3 flex justify-between items-center sticky top-0 z-50">
        <nav class="hidden md:flex items-center gap-6">
            <!-- Logo -->
            <div class="flex items-center gap-3">
                <div class="bg-blue-600 text-white p-2 rounded-lg">
                    <i class="pi pi-ticket text-xl" />
                </div>
                <div class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-400 bg-clip-text text-transparent">Ticket<span class="text-gray-900 dark:text-white">PRO</span></div>
            </div>

            <!-- Menus -->
            <ul class="flex items-center gap-4 ml-6 text-sm font-medium text-gray-700 dark:text-gray-200">
                <li><a v-if="tipo_usuario == 'tecnico' || tipo_usuario == 'solicitante'" href="/meus-chamados" class="hover:text-blue-600">Meus Chamados</a></li>
                <li><a v-if="tipo_usuario == 'tecnico'" href="/atendimento-chamados" class="hover:text-blue-600">Atendimento</a></li>
                <li><a v-if="tipo_usuario == 'tecnico'" href="/admin" class="hover:text-blue-600">Usuários</a></li>
            </ul>
        </nav>

        <!-- Botão sair -->
        <div class="flex items-center gap-3">
            <Button style="color: white; background-color: #004285" @click.prevent="logout()" label="Sair" icon="pi pi-sign-out" class="p-button-sm p-button-text border border-gray-200 hover:border-blue-200 hover:text-blue-600" />
        </div>
    </header>
</template>
