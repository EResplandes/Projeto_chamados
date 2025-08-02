<script>
import { ref } from 'vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import MenuSuperior from '@/components/MenuSuperior.vue';
import ChamadosService from '../../service/ChamadosService';
// import CategoriasService from '@/service/CategoriasService';
import ChatService from '@/service/ChatService';
import { useToast } from 'primevue';
import Skeleton from 'primevue/skeleton';
import Dropdown from 'primevue/dropdown';

export default {
    name: 'ChamadosTecnico',
    components: {
        Button,
        DataTable,
        Column,
        Tag,
        Dialog,
        MenuSuperior,
        Dropdown
    },
    data() {
        return {
            chamados: [],
            meusChamados: [],
            novosChamados: [],
            intervalId: null,
            proximaAtualizacaoEm: 180,
            cronometroId: null,
            mensagens: [],
            indicadores: [],
            carregando: true,
            visibleChat: false,
            carregandoMensagens: true,
            usuario_id: JSON.parse(localStorage.getItem('usuario'))?.id,
            mensagemChat: null,
            erroMensagem: false,
            chamadoSelecionadoId: null,
            usuarioLogado: JSON.parse(localStorage.getItem('usuario'))?.name,
            statusOptions: [
                { label: 'Novo', value: 'Novo' },
                { label: 'Em Andamento', value: 'Em Andamento' },
                { label: 'Pendente', value: 'Pendente' },
                { label: 'Resolvido', value: 'Resolvido' },
                { label: 'Fechado', value: 'Fechado' }
            ],
            filtroStatus: null,
            chamadosService: new ChamadosService(),
            chatService: new ChatService(),
            toast: useToast()
        };
    },
    mounted() {
        this.buscarChamados();

        this.intervalId = setInterval(() => {
            this.buscarChamados();
            this.proximaAtualizacaoEm = 180;
        }, 180000);

        this.cronometroId = setInterval(() => {
            if (this.proximaAtualizacaoEm > 0) {
                this.proximaAtualizacaoEm--;
            }
        }, 1000);

    },
    beforeUnmount() {
        clearInterval(this.intervalId);
        clearInterval(this.cronometroId);
    },
    methods: {
        buscarChamados() {
            // Busca indicadores
            this.chamadosService.indicadoresAdmin().then((data) => {
                this.indicadores = data.indicadores;
            });

            // Busca chamados
            this.chamadosService.buscaChamadosAdmin().then((data) => {
                this.novosChamados = data.novos_chamados;
                this.meusChamados = data.meus_chamados;
                this.carregando = false;
            });
        },

        visualizarChamado(id) {
            this.chamadoSelecionadoId = id;
            this.buscarMensagens(id);
            this.visibleChat = true;
        },


        visualizarChat(id) {
            this.chamadoSelecionadoId = id;
            this.chatService.buscaChat(id).then((data) => {
                if (data.status === 'success') {
                    this.mensagens = data.mensagens;
                    this.carregandoMensagens = false;
                } else {
                    this.mensagemFalha('Erro ao carregar o chat.');
                }
            });

            this.visibleChat = true;
        },

        formatarTempo(segundos) {
            const min = String(Math.floor(segundos / 60)).padStart(2, '0');
            const sec = String(segundos % 60).padStart(2, '0');
            return `${min}:${sec}`;
        },

        buscaMensagens() {
            this.chatService.buscaChat(this.chamadoSelecionadoId).then((data) => {
                if (data.status === 'success') {
                    this.mensagens = data.mensagens;
                    this.carregandoMensagens = false;
                } else {
                    this.mensagemFalha('Erro ao carregar o chat.');
                }
            });
        },

        onFecharChat() {
            this.visibleChat = false;
            this.mensagens = [];
            this.carregandoMensagens = true;
            this.chamadoSelecionadoId = null;
            this.mensagemChat = '';
        },

        onDragOver() {
            this.isDragging = true;
        },
        onDragLeave() {
            this.isDragging = false;
        },
        onDrop(event) {
            this.isDragging = false;
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                if (file.type.startsWith('image/')) {
                    this.imagemSelecionada = file;

                    this.chatService.enviarAnexo(file, this.chamadoSelecionadoId).then((data) => {
                        this.buscaMensagens(this.chamadoSelecionadoId);
                    });
                    this.mensagemSucesso(`Imagem "${file.name}" adicionada.`);
                } else {
                    this.mensagemFalha('Somente arquivos de imagem são permitidos.');
                }
            }
        },

        enviarMensagem() {
            this.erroMensagem = false;

            if (!this.mensagemChat) {
                this.erroMensagem = true;
                return;
            }

            this.chatService.enviarMensagem(this.mensagemChat, this.chamadoSelecionadoId).then((data) => {
                if (data.status === 'success') {
                    this.mensagemChat = '';
                    this.buscaMensagens(this.chamadoSelecionadoId);
                } else {
                    this.mensagemFalha('Erro ao enviar a mensagem.');
                }
            });
        },

        onFecharChat() {
            this.visibleChat = false;
            this.mensagens = [];
            this.carregandoMensagens = true;
            this.chamadoSelecionadoId = null;
            this.mensagemChat = '';
        },

        assumirChamado(id) {
            // Simulação de assumir chamado
            const chamado = this.novosChamados.find((c) => c.id === id);
            if (chamado) {
                chamado.status = 'Em andamento';
                this.meusChamados.push(chamado);
                this.novosChamados = this.novosChamados.filter((c) => c.id !== id);
                this.mensagemSucesso(`Chamado #${id} assumido com sucesso!`);

                this.chamadosService.assumeChamado(chamado.id, this.usuario_id).then((data) => {
                    this.buscarChamados();
                });
            }
        },

        atualizarStatus(chamado, novoStatus) {
            // Simulação de atualização de status
            chamado.status = novoStatus;
            this.mensagemSucesso(`Status do chamado #${chamado.id} atualizado para ${novoStatus}`);
        },

        mensagemSucesso(mensagem) {
            this.toast.add({ severity: 'success', summary: 'Sucesso', detail: mensagem, life: 3000 });
        },

        mensagemFalha(mensagem) {
            this.toast.add({ severity: 'error', summary: 'Erro', detail: mensagem, life: 3000 });
        }
    },
    computed: {
        chamadosFiltrados() {
            if (!this.filtroStatus) return this.chamadosAtribuidos;
            return this.chamadosAtribuidos.filter((c) => c.status === this.filtroStatus.value);
        }
    },
    setup() {
        const statusColor = (status) => {
            switch (status) {
                case 'Novo':
                    return 'info';
                case 'Em andamento':
                    return 'warning';
                case 'Resolvido':
                    return 'success';
                case 'Fechado':
                    return 'success';
                case 'Pendente':
                    return 'danger';
                default:
                    return 'secondary';
            }
        };

        const priorityColor = (prioridade) => {
            switch (prioridade) {
                case 'Alta':
                    return 'danger';
                case 'Média':
                    return 'warning';
                case 'Baixa':
                    return 'info';
                case 'Crítica':
                    return 'danger';
                default:
                    return 'info';
            }
        };

        return {
            statusColor,
            priorityColor
        };
    }
};
</script>

<template>
    <Toast />

    <!-- Modal de Chat para acompanhar -->
    <Dialog @hide="onFecharChat()" v-model:visible="visibleChat" modal header="Atendimento" :style="{ width: '50rem' }"
        :closable="true">
        <!-- Mensagens fixas no HTML -->
        <div class="flex flex-col gap-3 max-h-[400px] overflow-y-auto px-2 py-1">
            <!-- Preloader enquanto carrega -->
            <template v-if="carregandoMensagens">
                <div v-for="n in 5" :key="n" class="flex flex-col gap-2">
                    <Skeleton height="1rem" width="30%" />
                    <Skeleton height="2rem" width="100%" />
                    <Skeleton height="0.75rem" width="20%" />
                </div>
            </template>

            <!-- Mensagens reais -->
            <template v-else>
                <div v-for="mensagem in mensagens" :key="mensagem.id"
                    :class="['p-2 rounded-md max-w-[80%]', mensagem.usuario_id === usuario_id ? 'bg-blue-100 self-end text-right' : 'bg-gray-100 self-start text-left']">
                    <p class="text-sm text-gray-700 mb-1">
                        <strong>{{ mensagem.usuario_id === usuario_id ? 'Você' : mensagem.usuario }}</strong>
                    </p>
                    <!-- Aqui a condicional para mostrar imagem ou texto -->
                    <div class="text-sm text-gray-600">
                        <template v-if="mensagem.mensagem === 'Imagem'">
                            <img :src="mensagem.imagem ? `http://localhost:8000/storage/${mensagem.imagem}` : mensagem.urlImagem"
                                alt="Imagem enviada" class="max-w-xs max-h-48 rounded" />
                        </template>
                        <template v-else>
                            {{ mensagem.mensagem }}
                        </template>
                    </div>
                    <p class="text-xs text-gray-600 mt-1">{{ mensagem.enviado_em }}</p>
                </div>
            </template>
        </div>

        <!-- Campo de envio de mensagem -->
        <div class="flex items-center gap-2 mt-4">
            <div class="relative w-full" @dragover.prevent="onDragOver" @dragleave.prevent="onDragLeave"
                @drop.prevent="onDrop" :class="{ 'border-2 border-dashed border-blue-400': isDragging }">
                <InputText :class="['w-full', { 'p-invalid': erroMensagem }]" v-model="mensagemChat"
                    placeholder="Digite sua mensagem ou arraste uma imagem" :style="{ color: 'red' }" />
            </div>
            <Button @click.prevent="enviarMensagem()" severity="success" icon="pi pi-send" />
        </div>
    </Dialog>

    <div>
        <div v-if="carregando" class="fixed inset-0 flex items-center justify-center bg-white z-50">
            <div class="loader ease-linear rounded-full border-8 border-t-8 border-gray-200 h-16 w-16"></div>
        </div>
    </div>

    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 font-sans text-gray-800">
        <MenuSuperior />

        <main class="container mx-auto px-4 py-6 max-w-7xl">
            <section class="mb-8">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">Olá, {{ usuarioLogado }}
                            <span class="text-3xl">👨‍💻</span>
                        </h1>
                        <p class="text-gray-500">Painel de gerenciamento de chamados</p>
                    </div>

                    <div class="flex items-center gap-2 flex-col md:flex-row md:items-center md:gap-4">
                        <p class="text-xs text-gray-800">
                            Atualização automática em:
                            <Tag severity="success" class="text-xs font-medium ml-2"> {{
                                formatarTempo(proximaAtualizacaoEm)
                            }}
                            </Tag>
                        </p>
                    </div>
                </div>
            </section>

            <!-- Cards de Status -->
            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                <div
                    class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-all hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 mb-1">Novos Chamados</p>
                            <p class="text-2xl font-bold text-gray-800">{{ indicadores.novos_chamados }}</p>
                        </div>
                        <div class="bg-blue-100/50 text-blue-600 rounded-lg p-3">
                            <i class="pi pi-inbox text-xl" />
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <span class="text-xs text-blue-600 font-medium">Chamados não atribuídos</span>
                    </div>
                </div>

                <div
                    class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-all hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 mb-1">Atribuídos a Mim</p>
                            <p class="text-2xl font-bold text-gray-800">{{ indicadores.meus_chamados }}</p>
                        </div>
                        <div class="bg-amber-100/50 text-amber-600 rounded-lg p-3">
                            <i class="pi pi-user text-xl" />
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <span class="text-xs text-amber-600 font-medium">Seus chamados em aberto</span>
                    </div>
                </div>

                <div
                    class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-all hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 mb-1">Em Andamento</p>
                            <p class="text-2xl font-bold text-gray-800">{{ indicadores.em_andamento }}</p>
                        </div>
                        <div class="bg-purple-100/50 text-purple-600 rounded-lg p-3">
                            <i class="pi pi-spinner text-xl" />
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <span class="text-xs text-purple-600 font-medium">Chamados sendo atendidos</span>
                    </div>
                </div>

                <div
                    class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-all hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 mb-1">Resolvidos</p>
                            <p class="text-2xl font-bold text-gray-800">{{ indicadores.resolvidos }}</p>
                        </div>
                        <div class="bg-green-100/50 text-green-600 rounded-lg p-3">
                            <i class="pi pi-check-circle text-xl" />
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <span class="text-xs text-green-600 font-medium">Chamados finalizados</span>
                    </div>
                </div>
            </section>

            <!-- Novos Chamados -->
            <section class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <h2 class="text-lg font-semibold text-gray-800">Novos Chamados</h2>
                        <Tag :value="indicadores.novos_chamados" severity="info" class="text-xs font-medium" />
                    </div>
                </div>

                <DataTable :value="novosChamados" paginator :rows="5" :rowsPerPageOptions="[5, 10]"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords}" responsiveLayout="scroll"
                    class="p-datatable-sm border-none p-6" stripedRows>
                    <Column field="id" header="#" style="width: 60px"
                        headerClass="font-medium text-gray-600 text-xs uppercase" />
                    <Column field="titulo" header="Título" headerClass="font-medium text-gray-600 text-xs uppercase">
                        <template #body="{ data }">
                            <div class="font-medium">{{ data.titulo }}</div>
                            <div class="text-xs text-gray-500"># {{ data.categoria }}</div>
                        </template>
                    </Column>
                    <Column field="descricao" header="Descrição"
                        headerClass="font-medium text-gray-600 text-xs uppercase" />
                    <Column field="solicitante" header="Solicitante"
                        headerClass="font-medium text-gray-600 text-xs uppercase" />
                    <Column field="prioridade" header="Prioridade"
                        headerClass="font-medium text-gray-600 text-xs uppercase">
                        <template #body="{ data }">
                            <Tag :value="data.prioridade" :severity="priorityColor(data.prioridade)"
                                class="text-xs font-medium" />
                        </template>
                    </Column>
                    <Column field="dt_abertura" header="Data Abertura"
                        headerClass="font-medium text-gray-600 text-xs uppercase" />
                    <Column header="Ações" style="width: 120px"
                        headerClass="font-medium text-gray-600 text-xs uppercase">
                        <template #body="{ data }">
                            <div class="flex items-center gap-1">
                                <Button @click.prevent="assumirChamado(data.id)" icon="pi pi-user-plus"
                                    class="p-button-rounded p-button-text p-button-sm text-green-500 hover:bg-green-50"
                                    v-tooltip.top="'Assumir Chamado'" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </section>

            <!-- Chamados Atribuídos -->
            <section class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <h2 class="text-lg font-semibold text-gray-800">Meus Chamados</h2>
                        <Tag :value="indicadores.meus_chamados" severity="warning" class="text-xs font-medium" />
                    </div>
                </div>

                <DataTable :value="meusChamados" paginator :rows="5" :rowsPerPageOptions="[5, 10, 20]"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords}" responsiveLayout="scroll"
                    class="p-datatable-sm border-none p-6" stripedRows>
                    <Column field="id" header="#" style="width: 60px"
                        headerClass="font-medium text-gray-600 text-xs uppercase" />
                    <Column field="titulo" header="Título" headerClass="font-medium text-gray-600 text-xs uppercase">
                        <template #body="{ data }">
                            <div class="font-medium">{{ data.titulo }}</div>
                            <div class="text-xs text-gray-500"># {{ data.categoria }}</div>
                        </template>
                    </Column>
                    <Column field="descricao" header="Descrição"
                        headerClass="font-medium text-gray-600 text-xs uppercase" />
                    <Column field="solicitante" header="Solicitante"
                        headerClass="font-medium text-gray-600 text-xs uppercase" />
                    <Column field="status" header="Status" headerClass="font-medium text-gray-600 text-xs uppercase">
                        <template #body="{ data }">
                            <!-- <Tag :value="data.status" :severity="statusColor(data.status)"
                                class="text-xs font-medium" /> -->
                            <Dropdown v-model="data.status_id" :options="statusOptions" optionLabel="label"
                                optionValue="value" placeholder="Status" class="w-full md:w-14rem text-xs"
                                @change="alterarStatus(data.id, data.status_id)" />
                        </template>

                    </Column>
                    <Column field="dt_abertura" header="Data Abertura"
                        headerClass="font-medium text-gray-600 text-xs uppercase" />
                    <Column header="Ações" style="width: 180px"
                        headerClass="font-medium text-gray-600 text-xs uppercase">
                        <template #body="{ data }">
                            <div class="flex items-center gap-1">
                                <Button @click.prevent="visualizarChat(data.id)" icon="pi pi-comments"
                                    class="p-button-rounded p-button-text p-button-sm text-blue-500 hover:bg-blue-50"
                                    v-tooltip.top="'Chat'" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </section>
        </main>
    </div>
</template>

<style>
.p-datatable .p-datatable-thead>tr>th {
    background-color: #f9fafb;
}

.p-datatable .p-datatable-tbody>tr:hover {
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
