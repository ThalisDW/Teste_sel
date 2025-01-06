<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPedidoJob;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $data =$request->pedidos;
        $chunkSize = 200;

        // Divide os dados para alta volumetria e evitar killed do job
        collect($data)->chunk($chunkSize)->each(function ($chunk) {
            // Despacha o Job para cada chunk
            ProcessPedidoJob::dispatch($chunk);
        });

        return response()->json(['message' => 'Pedido recebido e em processamento.'], 200);
    }
}
