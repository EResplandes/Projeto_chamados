<script>
import { ref } from 'vue';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import logo from '../../../public/assets/img/logo_sem_fundo.png';
import AutenticacaoService from '@/service/AutenticacaoService';

export default {
    name: 'LoginPage',
    components: {
        InputText,
        Password,
        Button
    },
    data() {
        return {
            logo: logo,
            form: {
                email: '',
                senha: ''
            },
            erro: {
                email: false,
                senha: false,
                login: false
            },
            autenticacaoService: new AutenticacaoService()
        };
    },
    methods: {
        async logar() {
            if (this.form.email === '') {
                this.erro.email = true;
                return;
            }

            if (this.form.senha === '') {
                this.erro.senha = true;
                return;
            }

            try {
                await this.autenticacaoService.logar(this.form).then((data) => {
                    if (data.token) {
                        localStorage.setItem('token', data.token);
                        localStorage.setItem('usuario', JSON.stringify(data.user));
                        const solicitante = data.user.tipo_usuario;
                        if (solicitante != 'solicitante') {
                            this.$router.push({ name: 'atendimento-chamados' });
                        } else {
                            this.$router.push({ name: 'meus-chamados' });
                        }
                    } else {
                        this.erro.login = true;
                    }
                });
            } catch (error) {
                this.erro.login = true;
                console.error(error);
            }
        }
    }
};
</script>

<template>
    <div class="flex min-h-screen">
        <!-- Lado esquerdo com imagem -->
        <div style="background-color: #004285" class="hidden md:flex w-1/2 bg-blue-800 items-center justify-center p-8">
            <img :src="logo" alt="Login Illustration" class="max-w-md w-full" />
        </div>

        <!-- Lado direito com formulário -->
        <div class="flex w-full md:w-1/2 items-center justify-center bg-white p-8">
            <div class="w-full max-w-sm">
                <h2 class="text-2xl font-bold text-center mb-6">Ticket Pro | Grupo Rialma</h2>

                <form @submit.prevent="handleLogin">
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                        <InputText id="email" v-model="form.email" placeholder="email@gruporialma.com.br" class="w-full" />
                    </div>

                    <div v-if="erro.email" class="mb-4">
                        <Message severity="error">Insira um e-mail!</Message>
                    </div>

                    <div class="mb-4">
                        <label for="senha" class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                        <Password :feedback="false" id="senha" v-model="form.senha" placeholder="******" toggleMask class="w-full" inputClass="w-full" />
                    </div>

                    <div v-if="erro.senha" class="mb-4">
                        <Message severity="error">Insira uma senha!</Message>
                    </div>

                    <div class="flex items-center justify-between mb-4 text-sm">
                        <div class="flex items-center">
                            <input id="remember" type="checkbox" class="mr-2" />
                            <label for="remember" class="text-gray-600">Lembrar-me</label>
                        </div>
                        <a href="#" class="text-blue-600 hover:underline">Esqueceu a senha?</a>
                    </div>

                    <Button @click.prevent="logar()" style="background-color: #004285" label="Entrar" type="submit" class="w-full mb-4 btn btn-info" severity="info" />
                    <div v-if="erro.login" class="mb-4">
                        <Message severity="error">Login ou senha incorreto(s)!</Message>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
