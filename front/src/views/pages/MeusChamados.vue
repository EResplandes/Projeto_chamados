<script>
import { ref } from 'vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import MenuSuperior from '@/components/MenuSuperior.vue';
import ChamadosService from '../../service/ChamadosService';
import CategoriasService from '@/service/CategoriasService';
import ChatService from '@/service/ChatService';
import { useToast } from 'primevue';
import Skeleton from 'primevue/skeleton';

export default {
    name: 'MeusChamados',
    components: {
        Button,
        DataTable,
        Column,
        Tag,
        Dialog,
        MenuSuperior
    },
    data() {
        return {
            chamados: [],
            mensagens: [],
            indicadores: [],
            carregando: true,
            visible: false,
            isDragging: false,
            imagemSelecionada: null,
            visibleChat: false,
            carregandoMensagens: true,
            usuario_id: JSON.parse(localStorage.getItem('usuario'))?.id,
            mensagemChat: null,
            erroMensagem: false,
            fileKey: 0,
            chamadoSelecionadoId: null,
            categorias: [],
            usuarioLogado: JSON.parse(localStorage.getItem('usuario'))?.name,
            urgencias: [
                { label: 'Baixa', value: 'Baixa' },
                { label: 'Média', value: 'Média' },
                { label: 'Alta', value: 'Alta' },
                { label: 'Crítica', value: 'Crítica' }
            ],
            formChamado: {},
            chamadosService: new ChamadosService(),
            categoriasService: new CategoriasService(),
            chatService: new ChatService(),
            toast: useToast()
        };
    },
    mounted() {
        // Busca todas categorias
        this.categoriasService.bucaCategorias().then((data) => {
            this.categorias = data.categorias;
        });

        // Busca todos os chamados do usuário logado
        this.chamadosService.buscaChamados().then((data) => {
            this.chamados = data.chamados;
        });

        // Busca indicadores do usuário logado
        this.chamadosService.indicadoresUsuario().then((data) => {
            this.indicadores = data.indicadores;
            this.carregando = false;
        });
    },
    methods: {
        buscaChamados() {
            this.carregando = true;
            this.chamadosService.buscaChamados().then((data) => {
                this.chamados = data.chamados;
                this.carregando = false;
            });
            // Busca indicadores do usuário logado
            this.chamadosService.indicadoresUsuario().then((data) => {
                this.indicadores = data.indicadores;
                this.carregando = false;
            });
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
        abrirChamado() {
            this.chamadosService.abrirChamado(this.formChamado).then((data) => {
                if (data.status == 'Chamado registrado com sucesso!') {
                    this.mensagemSucesso(data.status);
                    this.carregandoMensagens = false;
                    this.buscaChamados();
                } else {
                    this.mensagemFalha('Ocorreu algum erro ao registrar o chamado.');
                }
                this.visible = false;
                this.formChamado = {};
            });
            this.visible = true;
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

        uploadPdf() {
            this.formChamado.pdf = this.$refs.pdf.files[0];
        },

        mensagemSucesso(mensagem) {
            this.toast.add({ severity: 'info', summary: 'Mensagem', detail: mensagem, life: 3000 });
        },
        mensagemFalha(mensagem) {
            this.toast.add({ severity: 'danger', summary: 'Mensagem', detail: mensagem, life: 3000 });
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
        }
    },
    setup() {
        const visible = ref(false);
        const chat = ref(false);
        const form = ref({});

        const statusColor = (status) => {
            switch (status) {
                case 'Novo':
                    return 'success';
                case 'Em Andamento':
                    return 'warning';
                case 'Finalizado':
                    return 'success';
                case 'Reaberto':
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
            form,
            statusColor,
            priorityColor
        };
    }
};
</script>

<template>
    <Toast />

    <!-- Modal de Chat para acompanhar -->
    <Dialog @hide="onFecharChat()" v-model:visible="visibleChat" modal header="Atendimento" :style="{ width: '50rem' }" :closable="true">
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
                <div v-for="mensagem in mensagens" :key="mensagem.id" :class="['p-2 rounded-md max-w-[80%]', mensagem.usuario_id === usuario_id ? 'bg-blue-100 self-end text-right' : 'bg-gray-100 self-start text-left']">
                    <p class="text-sm text-gray-700 mb-1">
                        <strong>{{ mensagem.usuario_id === usuario_id ? 'Você' : mensagem.usuario }}</strong>
                    </p>
                    <!-- Aqui a condicional para mostrar imagem ou texto -->
                    <div class="text-sm text-gray-600">
                        <template v-if="mensagem.mensagem === 'Imagem'">
                            <img :src="mensagem.imagem ? `http://api-ticket/storage/${mensagem.imagem}` : mensagem.urlImagem" alt="Imagem enviada" class="max-w-xs max-h-48 rounded" />
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
            <div class="relative w-full" @dragover.prevent="onDragOver" @dragleave.prevent="onDragLeave" @drop.prevent="onDrop" :class="{ 'border-2 border-dashed border-blue-400': isDragging }">
                <InputText :class="['w-full', { 'p-invalid': erroMensagem }]" v-model="mensagemChat" placeholder="Digite sua mensagem ou arraste uma imagem" :style="{ color: 'red' }" />
            </div>
            <Button @click.prevent="enviarMensagem()" severity="success" icon="pi pi-send" />
        </div>
    </Dialog>

    <!-- Cadastro do Chamado -->
    <Dialog v-model:visible="visible" modal header="Abertura de Chamado" :style="{ width: '35rem' }">
        <template #header>
            <div class="inline-flex items-center justify-center gap-2">
                <i class="pi pi-ticket text-xl"></i>
                <span class="font-bold whitespace-nowrap">Novo Chamado</span>
            </div>
        </template>

        <div class="mb-4">
            <label for="titulo" class="font-semibold block mb-1">Título <span class="text-red-500">*</span></label>
            <InputText id="titulo" v-model="formChamado.titulo" class="w-full" />
        </div>

        <div class="mb-4">
            <label for="descricao" class="font-semibold block mb-1">Descrição <span class="text-red-500">*</span></label>
            <Textarea id="descricao" v-model="formChamado.descricao" class="w-full" rows="4" />
        </div>

        <div class="mb-4">
            <label for="urgencia" class="font-semibold block mb-1">Urgência <span class="text-red-500">*</span></label>
            <Dropdown id="urgencia" v-model="formChamado.urgencia" :options="urgencias" optionLabel="label" placeholder="Selecione a urgência" class="w-full" />
        </div>

        <div class="mb-4">
            <label for="categoria" class="font-semibold block mb-1">Categoria <span class="text-red-500">*</span></label>
            <Dropdown id="categoria" v-model="formChamado.categoria" :options="categorias" optionLabel="categoria" placeholder="Selecione a categoria" class="w-full" />
        </div>

        <div class="mb-4">
            <label for="anexo" class="font-semibold block mb-1">Anexo</label>
            <FileUpload style="background-color: #004285" :key="fileKey" chooseLabel="Selecionar Arquivo" @change="uploadPdf" mode="basic" type="file" ref="pdf" name="demo[]" accept=".pdf,.docx" :maxFileSize="999999999"></FileUpload>
        </div>

        <template #footer>
            <Button label="Cancelar" text severity="secondary" @click="visible = false" />
            <Button style="background-color: #004285" label="Enviar Chamado" @click="abrirChamado()" />
        </template>
    </Dialog>

    <div>
        <!-- Preloader -->
        <div v-if="carregando" class="fixed inset-0 flex items-center justify-center bg-white z-50">
            <div class="loader ease-linear rounded-full border-8 border-t-8 border-gray-200 h-16 w-16"></div>
        </div>
    </div>

    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 font-sans text-gray-800">
        <MenuSuperior />

        <!-- Conteúdo Principal -->
        <main class="container mx-auto px-4 py-6 max-w-7xl">
            <!-- Cabeçalho com Saudação -->
            <section class="mb-8">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">Olá, {{ usuarioLogado }} <span class="text-3xl">👋</span></h1>
                        <p class="text-gray-500">Aqui está o resumo dos seus chamados</p>
                    </div>
                    <Button style="background-color: #004285" @click.prevent="visible = true" label="Abrir Novo Chamado" icon="pi pi-plus" class="p-button-sm" />
                </div>
            </section>

            <!-- Cards de Status -->
            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-all hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 mb-1">Abertos</p>
                            <p class="text-2xl font-bold text-gray-800">{{ this.indicadores?.chamados_abertos }}</p>
                        </div>
                        <div class="bg-blue-100/50 text-blue-600 rounded-lg p-3">
                            <i class="pi pi-inbox text-xl" />
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <span class="text-xs text-blue-600 font-medium">Voces tem {{ this.indicadores?.chamados_abertos }} chamados abertos</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-all hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 mb-1">Em Andamento</p>
                            <p class="text-2xl font-bold text-gray-800">{{ this.indicadores?.chamados_andamento }}</p>
                        </div>
                        <div class="bg-amber-100/50 text-amber-600 rounded-lg p-3">
                            <i class="pi pi-spinner text-xl" />
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <span class="text-xs text-amber-600 font-medium">Vocês tem {{ this.indicadores?.chamados_andamento }} chamados em andamento</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-all hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 mb-1">Concluídos</p>
                            <p class="text-2xl font-bold text-gray-800">{{ this.indicadores?.chamados_fechados }}</p>
                        </div>
                        <div class="bg-green-100/50 text-green-600 rounded-lg p-3">
                            <i class="pi pi-check-circle text-xl" />
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <span class="text-xs text-green-600 font-medium">Vocês tem {{ this.indicadores?.chamados_fechados }} chamados concluídos</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-all hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 mb-1">Total</p>
                            <p class="text-2xl font-bold text-gray-800">{{ this.indicadores?.total_chamados }}</p>
                        </div>
                        <div class="bg-purple-100/50 text-purple-600 rounded-lg p-3">
                            <i class="pi pi-chart-bar text-xl" />
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <span class="text-xs text-purple-600 font-medium">Vocês tem {{ this.indicadores?.total_chamados }} chamados</span>
                    </div>
                </div>
            </section>

            <!-- Tabela de Chamados -->
            <section class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden p-6">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <h2 class="text-lg font-semibold text-gray-800">Meus Chamados</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button style="color: #004285" icon="pi pi-filter" label="Filtrar" class="p-button-text p-button-sm text-gray-600 border border-gray-200" />
                    </div>
                </div>

                <DataTable
                    :value="chamados"
                    paginator
                    :rows="5"
                    :rowsPerPageOptions="[5, 10, 20]"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords}"
                    responsiveLayout="scroll"
                    class="p-datatable-sm border-none"
                    stripedRows
                >
                    <Column field="id" header="#" style="width: 60px" headerClass="font-medium text-gray-600 text-xs uppercase" />
                    <Column field="titulo" header="Título" headerClass="font-medium text-gray-600 text-xs uppercase">
                        <template #body="{ data }">
                            <div class="font-medium">{{ data.titulo }}</div>
                            <div class="text-xs text-gray-500">#{{ data.id }} • {{ data.categoria }}</div>
                        </template>
                    </Column>
                    <Column field="descricao" header="Descrição" headerClass="font-medium text-gray-600 text-xs uppercase">
                        <template #body="{ data }">
                            <div class="font-medium">{{ data.descricao }}</div>
                        </template>
                    </Column>
                    <Column field="status" header="Status" headerClass="font-medium text-gray-600 text-xs uppercase">
                        <template #body="{ data }">
                            <Tag :value="data.status" :severity="statusColor(data.status)" class="text-xs font-medium" />
                        </template>
                    </Column>
                    <Column field="prioridade" header="Prioridade" headerClass="font-medium text-gray-600 text-xs uppercase">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <Tag :value="data.prioridade" :severity="priorityColor(data.prioridade)" class="text-xs font-medium" />
                                <i v-if="data.prioridade === 'Alta'" class="pi pi-exclamation-circle text-red-500 text-xs" />
                            </div>
                        </template>
                    </Column>
                    <Column field="tecnico" header="Técnico" headerClass="font-medium text-gray-600 text-xs uppercase">
                        <template #body="{ data }">
                            <div class="font-medium">{{ data.tecnico }}</div>
                        </template>
                    </Column>
                    <Column field="dt_abertura" header="Data" headerClass="font-medium text-gray-600 text-xs uppercase" />
                    <Column header="Ações" style="width: 100px" headerClass="font-medium text-gray-600 text-xs uppercase">
                        <template #body="{ data }">
                            <div class="flex items-center gap-1">
                                <Button @click.prevent="visualizarChat(data.id)" style="color: #004285" icon="pi pi-comments" class="p-button-rounded p-button-text p-button-sm text-blue-500 hover:bg-blue-50" v-tooltip.top="'Visualizar'" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </section>
        </main>
    </div>
</template>

<style>
/* Estilos personalizados */
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
