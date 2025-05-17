<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OCR Upload</title>
</head>
<body>
    <h1>Upload de Arquivo para OCR</h1>
    <form action="{{ url('/ocr-upload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <label for="arquivo">Selecione um arquivo:</label>
        <input type="file" name="arquivo" id="arquivo" required>
        <button type="submit">Enviar</button>
    </form>
</body>
</html>