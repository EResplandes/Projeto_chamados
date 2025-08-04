<script>
import { ref } from 'vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import MenuSuperior from '@/components/MenuSuperior.vue';
import { useToast } from 'primevue';
import Skeleton from 'primevue/skeleton';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import UsuariosService from '../../service/UsuariosService.js';

export default {
    name: 'GerenciamentoUsuarios',
    components: {
        Button,
        DataTable,
        Column,
        Tag,
        Dialog,
        MenuSuperior,
        InputText,
        Dropdown
    },
    data() {
        return {
            usuarios: [],
            carregando: true,
            visibleDialog: false,
            novaSenha: null,
            usuarioEditando: null,
            modoEdicao: false,
            roles: [
                // { label: 'Administrador', value: 'admin' },
                { label: 'Técnico', value: 'tecnico' },
                { label: 'Usuário', value: 'solicitante' }
            ],
            filtroStatus: null,
            filtroRole: null,
            toast: useToast(),
            novoUsuario: {
                nome: '',
                email: '',
                tipo_usuario: 'usuario'
            },
            usuariosService: new UsuariosService(),
            dialogSenhaNova: false
        };
    },
    mounted() {
        this.buscarUsuarios();
        this.carregando = false; // Simulando carregamento
    },
    methods: {
        buscarUsuarios() {
            this.carregando = true;
            this.usuariosService
                .buscaUsuarios()
                .then((data) => {
                    this.usuarios = data.usuarios;
                    this.carregando = false;
                })
                .catch(() => {
                    this.mensagemFalha('Erro ao carregar usuários');
                    this.carregando = false;
                });
        },

        abrirDialogNovoUsuario() {
            this.novoUsuario = {
                nome: '',
                email: '',
                role: 'usuario',
                status: 'ativo'
            };
            this.modoEdicao = false;
            this.visibleDialog = true;
        },

        abrirDialogEditar(usuario) {
            this.usuarioEditando = { ...usuario };
            this.modoEdicao = true;
            this.visibleDialog = true;
        },

        salvarUsuario() {
            this.usuariosService
                .criarUsuario(this.novoUsuario)
                .then((data) => {
                    this.mensagemSucesso('Usuário criado com sucesso!');
                    this.novaSenha = data.senha; // Armazena a senha gerada
                    this.dialogSenhaNova = true;
                    this.buscarUsuarios();
                    this.visibleDialog = false;
                })
                .catch(() => {
                    this.mensagemFalha('Erro ao criar usuário');
                });
        },

        alterarStatusUsuario(usuarioId, novoStatus) {
            switch (novoStatus) {
                case 'ativo':
                    novoStatus = 1;
                    break;
                case 'inativo':
                    novoStatus = 0;
                    break;
                default:
                    this.mensagemFalha('Status inválido');
                    return;
            }

            this.usuariosService
                .alterarStatusUsuario(usuarioId, novoStatus)
                .then(() => {
                    this.mensagemSucesso('Status do usuário atualizado!');
                    this.buscarUsuarios();
                })
                .catch(() => {
                    this.mensagemFalha('Erro ao alterar status');
                });
        },

        resetarSenha(usuarioId) {
            this.usuariosService
                .resetarSenha(usuarioId)
                .then((data) => {
                    this.buscarUsuarios();
                    this.mensagemSucesso('Senha resetada com sucesso!');
                    this.dialogSenhaNova = true;
                    this.novaSenha = data.senha;
                })
                .catch(() => {
                    this.mensagemFalha('Erro ao resetar senha');
                });
        },

        mensagemSucesso(mensagem) {
            this.toast.add({ severity: 'success', summary: 'Sucesso', detail: mensagem, life: 3000 });
        },

        mensagemFalha(mensagem) {
            this.toast.add({ severity: 'error', summary: 'Erro', detail: mensagem, life: 3000 });
        }
    },
    computed: {
        usuariosFiltrados() {
            let filtrados = this.usuarios;

            if (this.filtroStatus) {
                filtrados = filtrados.filter((u) => u.status === this.filtroStatus.value);
            }

            if (this.filtroRole) {
                filtrados = filtrados.filter((u) => u.role === this.filtroRole.value);
            }

            return filtrados;
        }
    },
    setup() {
        const statusColor = (status) => {
            switch (status) {
                case 1:
                    return 'success';
                case 0:
                    return 'danger';
            }
        };

        const roleColor = (role) => {
            switch (role) {
                case 'Tecnico':
                    return 'contrast';
                case 'solicitante':
                    return 'info';
                case 'usuario':
                    return null;
                default:
                    return null;
            }
        };

        const verificaStatus = (role) => {
            switch (role) {
                case 1:
                    return 'Ativo';
                case 0:
                    return 'Desativado';
            }
        };

        return {
            statusColor,
            roleColor,
            verificaStatus
        };
    }
};
</script>

<template>
    <Toast />

    <div>
        <div v-if="carregando" class="fixed inset-0 flex items-center justify-center bg-white z-50">
            <div class="loader ease-linear rounded-full border-8 border-t-8 border-gray-200 h-16 w-16"></div>
        </div>
    </div>

    <!-- Modal de Edição/Criação de Usuário -->
    <Dialog @hide="visibleDialog = false" v-model:visible="visibleDialog" modal :header="modoEdicao ? 'Editar Usuário' : 'Novo Usuário'" :style="{ width: '40rem' }" :closable="true">
        <div class="grid gap-4">
            <div class="field">
                <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                <InputText v-model="novoUsuario.name" id="nome" class="w-full" placeholder="Nome completo" />
            </div>

            <div class="field">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                <InputText v-model="novoUsuario.email" id="email" class="w-full" placeholder="E-mail" :disabled="modoEdicao" />
            </div>

            <div class="field">
                <label for="perfil" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Usuário</label>
                <Dropdown v-model="novoUsuario.tipo_usuario" id="Perfil" :options="roles" optionLabel="label" optionValue="value" placeholder="Selecione o status" class="w-full" />
            </div>
        </div>

        <template #footer>
            <Button severity="danger" label="Cancelar" icon="pi pi-times" @click="visibleDialog = false" class="p-button-text" />
            <Button :label="modoEdicao ? 'Atualizar' : 'Criar'" icon="pi pi-check" @click="salvarUsuario" severity="success" />
        </template>
    </Dialog>

    <!-- Modal que informa a senha -->
    <Dialog v-model:visible="dialogSenhaNova" modal header="Nova senha!" :style="{ width: '25rem' }">
        <div class="flex flex-col gap-4">
            <p class="text-gray-600">
                A nova senha é: <strong>{{ novaSenha }}</strong>
            </p>
            <Button label="Fechar" icon="pi pi-check" @click="dialogSenhaNova = false" class="p-button-success" />
        </div>
    </Dialog>

    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 font-sans text-gray-800">
        <MenuSuperior />

        <main class="container mx-auto px-4 py-6 max-w-7xl">
            <section class="mb-8">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                            Gerenciamento de Usuários
                            <span class="text-3xl">👥</span>
                        </h1>
                        <p class="text-gray-500">Administre os usuários do sistema</p>
                    </div>

                    <div class="flex items-center gap-2 flex-col md:flex-row md:items-center md:gap-4">
                        <Button label="Novo Usuário" icon="pi pi-plus" severity="success" @click="abrirDialogNovoUsuario" class="p-button-sm" />
                    </div>
                </div>
            </section>

            <!-- Filtros
            <section class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="field">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <Dropdown v-model="filtroStatus" :options="statusOptions" optionLabel="label" optionValue="value" placeholder="Filtrar por status" class="w-full" :showClear="true" />
                    </div>

                    <div class="field">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Perfil</label>
                        <Dropdown v-model="filtroRole" :options="roles" optionLabel="label" optionValue="value" placeholder="Filtrar por perfil" class="w-full" :showClear="true" />
                    </div>

                    <div class="field flex items-end">
                        <Button
                            label="Limpar Filtros"
                            icon="pi pi-filter-slash"
                            severity="secondary"
                            @click="
                                filtroStatus = null;
                                filtroRole = null;
                            "
                            class="p-button-sm w-full"
                        />
                    </div>
                </div>
            </section> -->

            <!-- Tabela de Usuários -->
            <section class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <h2 class="text-lg font-semibold text-gray-800">Lista de Usuários</h2>
                        <Tag :value="usuariosFiltrados.length" severity="info" class="text-xs font-medium" />
                    </div>
                </div>

                <DataTable
                    :value="usuariosFiltrados"
                    paginator
                    :rows="10"
                    :rowsPerPageOptions="[5, 10, 20, 50]"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords}"
                    responsiveLayout="scroll"
                    class="p-datatable-sm border-none p-6"
                    stripedRows
                >
                    <Column field="id" header="#" style="width: 60px" headerClass="font-medium text-gray-600 text-xs uppercase" />
                    <Column field="name" header="Nome" headerClass="font-medium text-gray-600 text-xs uppercase" />
                    <Column field="email" header="E-mail" headerClass="font-medium text-gray-600 text-xs uppercase" />
                    <Column field="tipo_usuario" header="Perfil" headerClass="font-medium text-gray-600 text-xs uppercase">
                        <template #body="{ data }">
                            <Tag :value="data.tipo_usuario" :severity="roleColor(data.tipo_usuario)" class="text-xs font-medium capitalize" />
                        </template>
                    </Column>
                    <Column field="ativo" header="Status" headerClass="font-medium text-gray-600 text-xs uppercase">
                        <template #body="{ data }">
                            <Tag :value="verificaStatus(data.ativo)" :severity="statusColor(data.ativo)" class="text-xs font-medium capitalize" />
                        </template>
                    </Column>
                    <Column field="created_at" header="Dt. Criação" headerClass="font-medium text-gray-600 text-xs uppercase" />
                    <Column header="Ações" style="width: 180px" headerClass="font-medium text-gray-600 text-xs uppercase">
                        <template #body="{ data }">
                            <div class="flex items-center gap-1">
                                <Button @click.prevent="resetarSenha(data.id)" icon="pi pi-key" class="p-button-rounded p-button-text p-button-sm text-amber-500 hover:bg-amber-50" v-tooltip.top="'Resetar Senha'" />

                                <Button
                                    @click.prevent="alterarStatusUsuario(data.id, data.ativo == 1 ? 'inativo' : 'ativo')"
                                    :icon="data.ativo === 1 ? 'pi pi-ban' : 'pi pi-check'"
                                    :class="['p-button-rounded p-button-text p-button-sm', data.ativo == 1 ? 'text-red-500 hover:bg-red-50' : 'text-green-500 hover:bg-green-50']"
                                    v-tooltip.top="data.ativo === 1 ? 'Inativar' : 'Ativar'"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </section>
        </main>
    </div>
</template>

<style>
.p-datatable .p-datatable-thead > tr > th {
    background-color: #f9fafb;
}

.p-datatable .p-datatable-tbody > tr:hover {
    background-color: #f8fafc !important;
}

.p-paginator {
    border-top: 1px solid #f3f4f6 !important;
    border-radius: 0 0 12px 12px !important;
}

.loader {
    border-top-color: #3498db;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}
</style>
