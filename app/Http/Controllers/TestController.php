<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Intervention\Image\Facades\Image;


class TestController extends Controller
{

    public function testImage()
{
    try {
        $image = Image::make(public_path('example.jpg'))->resize(300, 300);
        $image->save(public_path('example_resized.jpg'));
        return response()->json(['success' => 'Image processed successfully']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
}
    public function testUpload(Request $request)
    {
        // Validar se recebeu arquivo:
        $request->validate([
            'arquivo' => 'required|file'
        ]);

        // Obter o arquivo do request:
        $file = $request->file('arquivo');

        // Retornar informações do arquivo para debug (nome, MIME, tamanho).
        return response()->json([
            'original_name' => $file->getClientOriginalName(),
            'mime_type'     => $file->getMimeType(),
            'size_bytes'    => $file->getSize(),
        ]);
    }
}
