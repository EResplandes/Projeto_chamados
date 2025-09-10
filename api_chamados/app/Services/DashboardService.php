<?php

namespace App\Services;

use App\Http\Resources\ChamadosAdminResource;
use App\Http\Resources\ChamadosResource;
use App\Http\Resources\ChatResource;
use App\Models\Chamados;
use App\Models\Chat;
use Illuminate\Support\Facades\DB;

class ServicoStatusChecker
{
    /**
     * Verifica o status de uma URL ou IP e retorna true se estiver online.
     * Otimizado para não demorar.
     *
     * @param string $host URL ou endereço IP a ser verificado.
     * @return bool
     */
    public static function verificarStatus($host)
    {
        // Se for um endereço IP, tenta pingar de forma otimizada
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $os = strtolower(PHP_OS);
            $command = '';

            // Verifica o sistema operacional para usar o comando ping correto
            if (strpos($os, 'win') === 0) {
                // Comando para Windows: -n 1 (1 pacote), -w 1000 (timeout de 1000ms)
                $command = "ping -n 1 -w 1000 " . escapeshellarg($host);
            } else {
                // Comando para Linux/Unix: -c 1 (1 pacote), -W 1 (timeout de 1s)
                $command = "ping -c 1 -W 1 " . escapeshellarg($host);
            }

            exec($command, $output, $status);
            return $status === 0;
        }

        // Se for uma URL, tenta conectar com cURL (já otimizado com timeouts)
        $ch = curl_init($host);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // Tempo para tentar a conexão
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);         // Tempo máximo para a requisição
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 300;
    }

    /**
     * Verifica o status de múltiplos serviços e retorna um array de objetos.
     *
     * @param array $servicos Array de serviços a serem verificados.
     * @return array
     */
    public static function verificarServicos(array $servicos)
    {
        $resultados = [];
        foreach ($servicos as $nome => $endereco) {
            $resultados[] = [
                'nome' => $nome,
                'status' => self::verificarStatus($endereco) ? 'Online' : 'Offline'
            ];
        }
        return $resultados;
    }
}

// Classe DashboardService original
class DashboardService
{
    public function indicadoresGerais()
    {
        // Removido DB::beginTransaction() pois é uma operação de leitura
        // DB::beginTransaction();

        try {
            // Buscar todos indicadores
            $indicadores = [
                'total_chamados' => DB::table('chamados')->count(),
                'chamados_novos' => DB::table('chamados')->where('status_id', 1)->count(),
                'chamados_em_andamento' => DB::table('chamados')->where('status_id', 2)->count(),
                'chamados_fechados' => DB::table('chamados')->where('status_id', 3)->count(),
            ];

            // Buscar últimos 15 chamados
            $chamados = ChamadosAdminResource::collection(
                Chamados::orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
            );

            // Buscar chamados por categoria
            $chamados_categorias = Chamados::query()
                ->select('categorias.categoria as categoria', DB::raw('count(chamados.id) as total'))
                ->join('categorias', 'chamados.categoria_id', '=', 'categorias.id')
                ->groupBy('categorias.categoria')
                ->get();

            // Buscar mensagens não lidas
            $mensagens_nao_lidas = ChatResource::collection(
                Chat::where('lida', false)
                    ->orderBy('created_at', 'desc')
                    ->get()
            );

            // Verificar serviços externos
            $servicos = [
                'Link' => 'https://link.gruporialma.com.br',
                'Protheus' => '10.0.11.184',
                'Servidor Principal' => '10.0.10.171',
                'Servidor Secundario' => '10.0.9.3',
                'Minerion' => '10.0.10.240',
                'Dexion' => '10.0.10.247',
                'FileServer' => '10.0.10.236',
                'AD' => '10.0.8.144',
                'DNS' => '10.0.8.133',
                'Firewall' => '10.0.11.254'
            ];

            $status_servicos = ServicoStatusChecker::verificarServicos($servicos);

            return [
                'status_servicos' => $status_servicos,
                'status' => true,
                'indicadores' => $indicadores,
                'chamados' => $chamados,
                'chamados_categorias' => $chamados_categorias,
                'mensagens_nao_lidas' => $mensagens_nao_lidas,
                'http_code' => 200,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'error' => 'Erro ao buscar indicadores: ' . $e->getMessage(),
                'http_code' => 500,
            ];
        }
    }
}
