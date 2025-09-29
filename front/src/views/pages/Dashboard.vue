<script>
import { useToast } from 'primevue/usetoast';
import Chart from 'primevue/chart';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import DashboardService from '@/service/DashboardService.js';
import audioSource from '../../assets/audio/NovoChamado.mp3';

export default {
    name: 'ITDashboard',
    components: {
        DataTable,
        Column,
        Tag
    },
    data() {
        return {
            chamados: [],
            indicadores: null,
            mensagensNaoLidas: [],
            statusSistemas: [],
            chamadosCategorias: [],
            lastUpdated: '',
            updateInterval: null,
            previousNewCalls: 0,
            scrollInterval: null,
            scrollSpeed: 50,
            scrollStep: 1,

            chartOptions: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#d1d5db'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#374151',
                        titleColor: '#e5e7eb',
                        bodyColor: '#d1d5db'
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: '#9ca3af'
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: '#9ca3af'
                        }
                    }
                }
            },
            dashboardService: new DashboardService()
        };
    },
    mounted() {
        this.atualizarDashboard();
        this.updateInterval = setInterval(this.atualizarDashboard, 15000);
        // this.startAutoScroll();
    },
    beforeUnmount() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
        }

        if (this.scrollInterval) {
            clearInterval(this.scrollInterval);
        }
    },
    watch: {
        indicadores: {
            handler(newIndicadores, oldIndicadores) {
                if (newIndicadores && newIndicadores.chamados_novos !== undefined) {
                    const currentNewCalls = newIndicadores.chamados_novos;

                    if (oldIndicadores === null) {
                        this.previousNewCalls = currentNewCalls;
                        return;
                    }

                    if (currentNewCalls > this.previousNewCalls) {
                        this.tocarNovoChamado();
                    }

                    this.previousNewCalls = currentNewCalls;
                }
            },
            deep: true
        }
    },
    methods: {
        startAutoScroll() {
            if (this.scrollInterval) {
                clearInterval(this.scrollInterval);
            }

            this.$nextTick(() => {
                const scrollContainer = this.$refs.chamadosScrollContainer;

                if (!scrollContainer) return;

                let isAtBottom = false;

                this.scrollInterval = setInterval(() => {
                    const { scrollTop, scrollHeight, clientHeight } = scrollContainer;

                    if (scrollHeight <= clientHeight) {
                        return;
                    }

                    // Verifica se está no final (com uma pequena margem de erro)
                    if (scrollTop + clientHeight >= scrollHeight - this.scrollStep) {
                        isAtBottom = true;
                    } else {
                        isAtBottom = false;
                    }

                    if (isAtBottom) {
                        // Volta para o topo suavemente ou instantaneamente
                        scrollContainer.scrollTo({ top: 0, behavior: 'smooth' });
                    } else {
                        scrollContainer.scrollTop += this.scrollStep;
                    }
                }, this.scrollSpeed);
            });
        },

        async gerarDados() {
            try {
                const response = await this.dashboardService.buscaIndicadoresGereais();
                if (response.status) {
                    return {
                        chamados: response.chamados,
                        indicadores: response.indicadores,
                        mensagens: response.mensagens_nao_lidas,
                        statusSistemas: response.status_servicos,
                        chamadosCategorias: response.chamados_categorias
                    };
                } else {
                    const toast = useToast();
                    toast.add({ severity: 'error', summary: 'Erro', detail: 'Falha ao carregar dados do dashboard.', life: 5000 });
                    return {
                        chamados: [],
                        indicadores: null,
                        mensagens: [],
                        statusSistemas: [],
                        chamadosCategorias: []
                    };
                }
            } catch (error) {
                const toast = useToast();
                toast.add({ severity: 'error', summary: 'Erro', detail: 'Erro ao buscar dados do dashboard.', life: 5000 });
                return {
                    chamados: [],
                    indicadores: null,
                    mensagens: [],
                    statusSistemas: [],
                    chamadosCategorias: []
                };
            }
        },

        async atualizarDashboard() {
            const dados = await this.gerarDados();
            console.log(dados);
            this.chamados = dados.chamados;
            this.indicadores = dados.indicadores;
            this.mensagensNaoLidas = dados.mensagens;
            this.statusSistemas = dados.statusSistemas;
            this.chamadosCategorias = dados.chamadosCategorias;
            this.lastUpdated = new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        },

        tocarNovoChamado() {
            try {
                const audio = new window.Audio(audioSource);
                audio.play().catch((e) => {
                    if (e.name !== 'NotAllowedError') {
                        console.error('Erro ao tocar o áudio:', e);
                    }
                });
            } catch (error) {
                console.error('Erro ao criar objeto de áudio:', error);
            }
        },

        urgencyColor(prioridade) {
            switch (prioridade) {
                case 'Crítica':
                case 'Alta':
                    return 'danger'; // Cor vermelha/perigo
                case 'Média':
                    return 'warning'; // Cor amarela/aviso
                case 'Baixa':
                    return 'info'; // Cor azul/informação
                default:
                    return 'secondary'; // Cor cinza/secundária
            }
        },

        statusColor(status) {
            switch (status) {
                case 'Novo':
                    return 'info';
                case 'Em andamento':
                    return 'warning';
                case 'Finalizado':
                case 'Reaberto':
                    return 'success';
                default:
                    return 'secondary';
            }
        }
    }
};
</script>

<template>
    <div class="min-h-screen bg-gray-900 text-white font-sans p-8 flex flex-col items-center">
        <div class="w-full max-w-screen-xl">
            <div class="grid grid-cols-12 gap-8 h-full">
                <div class="col-span-10 flex flex-col gap-8">
                    <section class="grid grid-cols-4 gap-6">
                        <div class="bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-700 transition-all hover:border-blue-500 hover:scale-105">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-400">Novos Chamados</p>
                                    <p class="text-4xl font-bold text-blue-300">{{ indicadores?.chamados_novos }}</p>
                                </div>
                                <i class="pi pi-inbox text-4xl text-blue-600 opacity-50"></i>
                            </div>
                        </div>
                        <div class="bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-700 transition-all hover:border-yellow-500 hover:scale-105">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-400">Em Andamento</p>
                                    <p class="text-4xl font-bold text-yellow-300">{{ indicadores?.chamados_em_andamento }}</p>
                                </div>
                                <i class="pi pi-spinner text-4xl text-yellow-600 opacity-50 animate-spin"></i>
                            </div>
                        </div>
                        <div class="bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-700 transition-all hover:border-green-500 hover:scale-105">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-400">Concluídos</p>
                                    <p class="text-4xl font-bold text-green-300">{{ indicadores?.chamados_fechados }}</p>
                                </div>
                                <i class="pi pi-check-circle text-4xl text-green-600 opacity-50"></i>
                            </div>
                        </div>
                        <div class="bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-700 transition-all hover:border-purple-500 hover:scale-105">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-400">Total de Chamados</p>
                                    <p class="text-4xl font-bold text-purple-300">{{ indicadores?.total_chamados }}</p>
                                </div>
                                <i class="pi pi-chart-bar text-4xl text-purple-600 opacity-50"></i>
                            </div>
                        </div>
                    </section>

                    <div class="bg-gray-800 rounded-xl shadow-lg border border-gray-700 overflow-hidden p-4 h-full max-h-[800px]">
                        <DataTable
                            :value="chamados"
                            :paginator="true"
                            :rows="10"
                            :rowsPerPageOptions="[10, 25, 50]"
                            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                            currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} chamados"
                            class="p-datatable-striped"
                            :loading="!chamados.length"
                            emptyMessage="Nenhum chamado encontrado."
                        >
                            <Column field="id" header="ID" style="width: 4%"></Column>
                            <Column field="dt_abertura" header="Data Abertura" style="width: 10%"></Column>
                            <Column field="titulo" header="Título" style="width: 50%"></Column>
                            <Column field="categoria" header="Categoria" style="width: 10%"></Column>
                            <Column field="prioridade" header="Urgência" style="width: 10%">
                                <template #body="{ data }">
                                    <Tag :severity="urgencyColor(data.prioridade)" :value="data.prioridade" />
                                </template>
                            </Column>
                            <Column field="solicitante" header="Solicitante" style="width: 15%"></Column>
                            <Column field="tecnico" header="Responsável" style="width: 15%"></Column>
                            <Column field="status" header="Status" style="width: 10%">
                                <template #body="{ data }">
                                    <Tag :severity="statusColor(data.status)" :value="data.status" />
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>

                <div class="col-span-2 flex flex-col gap-6">
                    <div class="flex justify-between items-end">
                        <p class="text-gray-400 text-lg">
                            Última atualização: <span class="font-bold text-white">{{ lastUpdated }}</span>
                        </p>
                    </div>

                    <div class="bg-gray-800 rounded-xl shadow-lg border border-gray-700 overflow-hidden p-4 flex-grow">
                        <div ref="chamadosScrollContainer" class="overflow-y-hidden">
                            <div class="grid gap-4">
                                <div
                                    v-for="item in statusSistemas"
                                    :key="item.nome"
                                    :class="{ 'p-3 rounded-lg flex items-center gap-3 transition-colors': true, 'bg-green-700': item.status === 'Online', 'bg-red-700 border-2 border-red-400 blink-animation': item.status === 'Offline' }"
                                >
                                    <i :class="{ 'text-2xl': true, 'pi pi-check-circle': item.status === 'Online', 'pi pi-times-circle': item.status === 'Offline' }"></i>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold">{{ item.nome }}</p>
                                        <p :class="{ 'text-xs font-medium': true, 'text-green-200': item.status === 'Online', 'text-red-200': item.status === 'Offline' }">
                                            {{ item.status }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.p-datatable-thead > tr > th {
    color: #9ca3af !important;
}
.p-datatable-table .p-datatable-thead th {
    background-color: #1f2937 !important;
    border-bottom: 1px solid #374151 !important;
}
.p-datatable-tbody > tr {
    background-color: #1f2937 !important;
    color: #d1d5db !important;
}

.p-paginator {
    background-color: #1f2937 !important;
    border-top: 1px solid #374151 !important;
    color: #d1d5db !important;
}

.p-paginator .p-paginator-page,
.p-paginator .p-paginator-first,
.p-paginator .p-paginator-prev,
.p-paginator .p-paginator-next,
.p-paginator .p-paginator-last {
    background-color: #1f2937 !important;
    color: #d1d5db !important;
    border: none !important;
    border-radius: 6px !important;
}

.p-paginator .p-paginator-page:hover,
.p-paginator .p-paginator-first:hover,
.p-paginator .p-paginator-prev:hover,
.p-paginator .p-paginator-next:hover,
.p-paginator .p-paginator-last:hover {
    background-color: #374151 !important;
    color: #60a5fa !important;
}

.p-paginator .p-paginator-page.p-highlight {
    background-color: #2563eb !important;
    color: #fff !important;
}

.p-datatable-tbody > tr > td {
    color: #d1d5db !important;
}

.p-datatable-tbody > tr > td {
    border: none !important;
}
.p-datatable-striped .p-datatable-tbody > tr:nth-child(even) {
    background-color: #1f2937 !important;
}
.p-datatable-striped .p-datatable-tbody > tr:nth-child(odd) {
    background-color: #111827 !important;
}

.blink-animation {
    animation: blink 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes blink {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.2;
    }
}

.min-h-screen {
    min-height: 100vh;
}
.shadow-lg {
    box-shadow:
        0 10px 15px -3px rgba(0, 0, 0, 0.1),
        0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.p-tag {
    font-weight: bold;
    font-size: 0.75rem; /* Menor para caber na tabela */
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    line-height: 1;
}

.p-tag-danger {
    background-color: #b91c1c; /* Red 700 */
    color: #fca5a5; /* Red 300 */
}
.p-tag-warning {
    background-color: #92400e; /* Amber 700 */
    color: #fcd34d; /* Amber 300 */
}
.p-tag-info {
    background-color: #1d4ed8; /* Blue 700 */
    color: #93c5fd; /* Blue 300 */
}
.p-tag-success {
    background-color: #065f46; /* Green 700 */
    color: #a7f3d0; /* Green 300 */
}
.p-tag-secondary {
    background-color: #374151; /* Gray 700 */
    color: #d1d5db; /* Gray 300 */
}

.p-paginator .p-dropdown {
    background-color: #374151 !important;
    border: 1px solid #4b5563 !important;
    color: #d1d5db !important;
}
</style>
