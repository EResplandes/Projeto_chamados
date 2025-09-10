<script>
import { useToast } from 'primevue/usetoast';
import Chart from 'primevue/chart';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import DashboardService from '@/service/DashboardService.js';

export default {
    name: 'ITDashboard',
    components: {
        Chart,
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
            chamadosCategorias: [], // Adicione esta variável para armazenar os dados do gráfico de categorias
            lastUpdated: '',
            updateInterval: null,
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
    },
    beforeUnmount() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
        }
    },
    methods: {
        async gerarDados() {
            try {
                const response = await this.dashboardService.buscaIndicadoresGereais();
                if (response.status) {
                    // Retorne todos os dados necessários
                    return {
                        chamados: response.chamados,
                        indicadores: response.indicadores,
                        mensagens: response.mensagens_nao_lidas,
                        statusSistemas: response.status_servicos,
                        chamadosCategorias: response.chamados_categorias // CORRIGIDO: Retornando o array
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
            this.chamados = dados.chamados;
            this.indicadores = dados.indicadores;
            this.mensagensNaoLidas = dados.mensagens;
            this.statusSistemas = dados.statusSistemas;
            this.chamadosCategorias = dados.chamadosCategorias; // CORRIGIDO: Atribuindo os dados
            this.lastUpdated = new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        },
        urgencyColor(urgencia) {
            switch (urgencia) {
                case 'Crítica':
                case 'Alta':
                    return 'danger';
                case 'Média':
                    return 'warning';
                case 'Baixa':
                    return 'info';
                default:
                    return 'secondary';
            }
        }
    },
    computed: {
        urgencyChartData() {
            const counts = this.chamados.reduce((acc, curr) => {
                acc[curr.urgencia] = (acc[curr.urgencia] || 0) + 1;
                return acc;
            }, {});

            const labels = Object.keys(counts);
            const data = Object.values(counts);

            const backgroundColors = labels.map((label) => {
                switch (label) {
                    case 'Crítica':
                    case 'Alta':
                        return '#ef4444';
                    case 'Média':
                        return '#f59e0b';
                    case 'Baixa':
                        return '#3b82f6';
                    default:
                        return '#d1d5db';
                }
            });

            return {
                labels: labels,
                datasets: [
                    {
                        data: data,
                        backgroundColor: backgroundColors,
                        hoverOffset: 4
                    }
                ]
            };
        },
        categoryChartData() {
            // CORRIGIDO: Agora usa o array this.chamadosCategorias, que vem do backend
            const labels = this.chamadosCategorias.map((item) => item.categoria);
            const data = this.chamadosCategorias.map((item) => item.total);

            return {
                labels: labels,
                datasets: [
                    {
                        label: 'Chamados',
                        data: data,
                        backgroundColor: 'rgba(59, 130, 246, 0.6)',
                        borderColor: '#3b82f6',
                        borderWidth: 1
                    }
                ]
            };
        }
    }
};
</script>

<template>
    <div class="min-h-screen bg-gray-900 text-white font-sans p-8 flex flex-col items-center">
        <div class="w-full max-w-screen-xl">
            <div class="grid grid-cols-12 gap-8 h-full">
                <div class="col-span-8 flex flex-col gap-8">
                    <div class="flex justify-between items-end">
                        <div class="flex items-center gap-4">
                            <h1 class="text-4xl font-extrabold text-blue-400">Dashboard</h1>
                            <span class="text-2xl animate-pulse">📊</span>
                        </div>
                        <p class="text-gray-400 text-lg">
                            Última atualização: <span class="font-bold">{{ lastUpdated }}</span>
                        </p>
                    </div>

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

                    <section class="grid w-full">
                        <DataTable :value="chamados" class="p-datatable-sm" stripedRows>
                            <Column field="titulo" header="Título" headerClass="text-gray-400 text-xs uppercase" bodyClass="text-sm text-white font-medium">
                                <template #body="{ data }">
                                    <div class="font-medium truncate">{{ data.titulo }}</div>
                                    <div class="text-xs text-gray-500">#{{ data.id }} • {{ data.categoria }}</div>
                                </template>
                            </Column>
                            <Column field="urgencia" header="Urgência" headerClass="text-gray-400 text-xs uppercase">
                                <template #body="{ data }">
                                    <Tag :value="data.prioridade" :severity="urgencyColor(data.prioridade)" class="text-xs font-medium" />
                                </template>
                            </Column>
                            <Column field="dt_abertura" header="Abertura" headerClass="text-gray-400 text-xs uppercase" bodyClass="text-xs text-gray-500" />
                            <Column field="tecnico" header="Técnico" headerClass="text-gray-400 text-xs uppercase" bodyClass="text-sm text-gray-300" />
                        </DataTable>
                    </section>

                    <section class="grid w-full bg-gray-800 p-6">
                        <h3 class="text-lg font-bold text-gray-300 mb-4">Status da Infraestrutura</h3>
                        <div class="grid grid-cols-2 gap-4">
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
                    </section>
                </div>

                <div class="col-span-4 flex flex-col gap-8">
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-bold text-gray-200">Chamados Recentes</h2>
                        <span class="text-xl">🔥</span>
                    </div>

                    <div class="bg-gray-800 rounded-xl shadow-lg border border-gray-700 overflow-hidden">
                        <h3 class="text-lg font-bold text-gray-300 mb-4 p-3">Chamados por Categoria</h3>

                        <div class="w-full h-full flex items-center justify-center">
                            <Chart type="bar" :data="categoryChartData" :options="chartOptions" class="w-full" />
                        </div>
                    </div>

                    <div class="bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-700 flex-grow">
                        <h3 class="text-lg font-bold text-gray-300 mb-4">Mensagens Não Lidas</h3>
                        <div v-if="mensagensNaoLidas.length > 0" class="flex flex-col gap-4 overflow-y-auto max-h-[550px] overflow-y">
                            <div v-for="msg in mensagensNaoLidas" :key="msg.id" class="p-3 bg-gray-700 rounded-lg">
                                <p class="text-sm font-semibold text-gray-200">{{ msg.usuario }} no Chamado #{{ msg.chamado.id }}</p>
                                <p class="text-xs text-gray-400 truncate mt-1">{{ msg.mensagem }}</p>
                            </div>
                        </div>
                        <div v-else class="text-center text-gray-500 mt-8">
                            <p>Nenhuma nova mensagem para exibir.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
/* Estilos globais para a tabela PrimeVue */
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
.p-datatable-tbody > tr > td {
    border: none !important;
}
.p-datatable-striped .p-datatable-tbody > tr:nth-child(even) {
    background-color: #1f2937 !important;
}
.p-datatable-striped .p-datatable-tbody > tr:nth-child(odd) {
    background-color: #111827 !important;
}

/* NOVO: Estilo para a animação de piscar */
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

/* Restante do CSS do componente (não foi alterado) */
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
</style>
