<!-- filepath: /home/fgomes/converX/resources/views/ocr_result.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado do OCR</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .json-output {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
            white-space: pre-wrap;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Resultado do OCR</h1>
        @if (isset($error))
            <p class="error">Erro: {{ $error }}</p>
        @else
            <h2>Resposta da API:</h2>
            <div class="json-output">
                {{ json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
            </div>
        @endif
    </div>
</body>
</html>