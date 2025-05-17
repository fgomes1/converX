<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TogetherController extends Controller
{
    public function showForm()
    {
        return view('together_ajax');
    }

    public function chatCompletions(Request $request)
    {
        
        // Verificar se o token está configurado
        if (!env('TOGETHER_API_KEY')) {
            Log::error("[Together] API key not configured");
            return response()->json([
                'status' => 500,
                'error' => 'Token da API Together não configurado',
            ], 500);
        }
        
        // Validar se foi enviado um arquivo
        $request->validate([
            'arquivo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB max
        ]);
        
        // Processar o upload do arquivo usando Storage
        $file = $request->file('arquivo');
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9\.]/', '_', $file->getClientOriginalName());
        $path = $file->storeAs('public/uploads', $filename);
        
        // Construir a URL para o arquivo
        $ngrokUrl = rtrim(env('NGROK_URL', 'https://8978-191-5-48-91.ngrok-free.app'), '/');
        $publicUrl = $ngrokUrl . Storage::url('uploads/' . $filename);
        
        Log::info("[Together] Generated image URL: $publicUrl");
        
        // Verificar se a URL é acessível
        try {
            $testResponse = Http::get($publicUrl);
            Log::info("[Together] URL Test Status: " . $testResponse->status());
            
            if ($testResponse->status() != 200) {
                return response()->json([
                    'status' => 400,
                    'error' => 'A URL da imagem não está acessível (HTTP ' . $testResponse->status() . ')',
                    'image_url' => $publicUrl
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error("[Together] URL Test Error: " . $e->getMessage());
            return response()->json([
                'status' => 400,
                'error' => 'Erro ao acessar a URL da imagem: ' . $e->getMessage(),
                'image_url' => $publicUrl
            ], 400);
        }

        $payload = [
            'model'    => 'meta-llama/Llama-Vision-Free',
            'messages' => [[
                'role'    => 'user',
                'content' => [
                    ['type' => 'text', 'text' => 'quero um formato json dessa tabela'],
                    ['type' => 'image_url', 'image_url' => ['url' => $publicUrl]],
                ],
            ]],
        ];

        Log::debug('[Together] Payload JSON: '.json_encode($payload, JSON_PRETTY_PRINT));

        try {
            $response = Http::withToken(env('TOGETHER_API_KEY'))
                ->timeout(120)
                ->acceptJson()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post('https://api.together.xyz/v1/chat/completions', $payload);

            Log::info("[Together] HTTP status: ".$response->status());
            Log::info("[Together] body: ".$response->body());

            return response()->json([
                'status' => $response->status(),
                'body'   => json_decode($response->body(), true),
                'image_url' => $publicUrl
            ], $response->status());
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("[Together] Connection timeout: " . $e->getMessage());
            return response()->json([
                'status' => 504,
                'error' => 'Timeout ao processar a requisição. A API demorou muito para responder.',
                'image_url' => $publicUrl
            ], 504);
        } catch (\Exception $e) {
            Log::error("[Together] Error: " . $e->getMessage());
            return response()->json([
                'status' => 500,
                'error' => 'Erro ao processar a requisição: ' . $e->getMessage(),
                'image_url' => $publicUrl
            ], 500);
        }
    }
}
