<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TogetherController extends Controller
{
    /**
     * Chamada simples (não‐streaming) ao Together API.
     */
    public function chatCompletions(Request $request)
    {
        // Você já deve gerar sua $imageUrl igual antes
        $imageUrl = $request->input('image_url');

        $payload = [
            'model'    => 'meta-llama/Llama-Vision-Free',
            'messages' => [
                [
                    'role'    => 'user',
                    'content' => [
                        ['type' => 'text',      'text'      => 'quero um formato json dessa tabela'],
                        ['type' => 'image_url', 'image_url' => ['url' => $imageUrl]],
                    ],
                ],
            ],
            // 'stream' => false, // opcional, default é false
        ];

        $response = Http::withToken(env('TOGETHER_API_KEY'))
            ->acceptJson()
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post('https://api.together.xyz/v1/chat/completions', $payload);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json([
            'error'  => $response->body(),
            'status' => $response->status(),
        ], $response->status());
    }

    /**
     * Streaming “SSE style” usando Guzzle (stream = true).
     */
    public function streamCompletions(Request $request)
    {
        $imageUrl = $request->input('image_url');

        $client = new Client([
            'base_uri' => 'https://api.together.xyz/v1',
            'timeout'  => 120,
            // 'stream'   => true, // vemos abaixo no post()
        ]);

        $response = $client->post('/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer '.env('TOGETHER_API_KEY'),
                'Content-Type'  => 'application/json',
                'Accept'        => 'text/event-stream',
            ],
            'json'   => [
                'model'    => 'meta-llama/Llama-Vision-Free',
                'messages' => [
                    [
                        'role'    => 'user',
                        'content' => [
                            ['type' => 'text',      'text'      => 'quero um formato json dessa tabela'],
                            ['type' => 'image_url', 'image_url' => ['url' => $imageUrl]],
                        ],
                    ],
                ],
                'stream' => true,
            ],
            'stream' => true,
        ]);

        $body = $response->getBody();

        return new StreamedResponse(function() use ($body) {
            while (!$body->eof()) {
                echo $body->read(1024);
                // Para envio imediato
                flush();
            }
        }, 200, [
            'Content-Type'        => 'text/event-stream',
            'Cache-Control'       => 'no-cache',
            'X-Accel-Buffering'   => 'no',
            'Connection'          => 'keep-alive',
        ]);
    }
}
