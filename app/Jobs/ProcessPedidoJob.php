<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;

class ProcessPedidoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $nameJob = 'prc_pedidos_' . now();

        Redis::lpush('logs', json_encode([
            'message' => 'Iniciando o job ' . $nameJob . ' com: ' . count($this->data) . ' pedidos.' ,
            'timestamp' => now(),
        ]));

        foreach ($this->data as $pedido) {
            $pedidoRef = $pedido['ref'];
            $status = $pedido['status'];
    
            // Define a URL de acordo com o status
            $url = $status === 0 
                ? "https://dominio.exemplo/pedidos/{$pedidoRef}/cancelado" 
                : "https://dominio.exemplo/pedidos/{$pedidoRef}/pendente";
    
            try {

                Redis::lpush('logs', json_encode([
                    'pedido_ref' => $pedidoRef,
                    'status' => $status,
                    'url' => $url,
                    'message' => 'Sendo enviado.',
                    'timestamp' => now(),
                ]));

                $response = Http::post($url);
    
                // Log no Redis
                Redis::lpush('logs', json_encode([
                    'pedido_ref' => $pedidoRef,
                    'status' => $status,
                    'url' => $url,
                    'response' => $response->body(),
                    'timestamp' => now(),
                ]));
            } catch (\Exception $e) {
                // Log de erro no Redis
                Redis::lpush('logs', json_encode([
                    'pedido_ref' => $pedidoRef,
                    'status' => $status,
                    'url' => $url,
                    'response' => 'Erro no envio: ' . $e->getMessage(),
                    'timestamp' => now(),
                ]));
            }
        }

        Redis::lpush('logs', json_encode([
            'message' => 'Job ' . $nameJob . ' encerrado.' ,
            'timestamp' => now(),
        ]));
    }
}
