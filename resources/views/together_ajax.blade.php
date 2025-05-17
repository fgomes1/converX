{{-- resources/views/together_ajax.blade.php --}}
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Together AJAX</title>
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script>
    axios.defaults.headers.common['X-CSRF-TOKEN'] =
      document.querySelector('meta[name="csrf-token"]').content;
  </script>
</head>
<body>
  <h1>Envio AJAX</h1>
  <input type="file" id="arquivo" accept=".png,.jpg,.jpeg,.pdf" />
  <button id="btnEnviar">Enviar</button>
  <button id="btnTestarFixo">Testar com Imagem Fixa</button>
  <h2>Resposta:</h2>
  <pre id="resultado"></pre>

  <div>
  <label for="prompt">Instrução para a AI:</label>
  <input type="text" id="prompt" value="quero um formato json dessa tabela" style="width: 100%; padding: 8px; margin-bottom: 10px;">
</div>

  <script>
  // Botão para enviar arquivo (mantido por compatibilidade)
  document.getElementById('btnEnviar').addEventListener('click', () => {
    const fileInput = document.getElementById('arquivo');
    if (!fileInput.files.length) return alert('Selecione um arquivo');

    const formData = new FormData();
    formData.append('arquivo', fileInput.files[0]);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    // Mostrar indicador de carregamento
    document.getElementById('resultado').innerHTML = 
      '<div style="padding: 10px; background: #f8f9fa; border-left: 4px solid #007bff;">Processando imagem, por favor aguarde...</div>';

    axios.post('/together/chat', formData, { withCredentials: true })
      .then(res => {
        console.log('URL da imagem:', res.data.image_url);
        document.getElementById('resultado').innerText =
          JSON.stringify(res.data.body, null, 2);
      })
      .catch(err => {
        console.error('Erro completo:', err.response?.data || err);
        document.getElementById('resultado').innerText = 
          `Erro ${err.response?.status || ''}: ${JSON.stringify(err.response?.data || err.message, null, 2)}`;
      });
  });

  // Botão para testar com imagem fixa
  document.getElementById('btnTestarFixo').addEventListener('click', () => {
    // Mostrar indicador de carregamento
    document.getElementById('resultado').innerHTML = 
      '<div style="padding: 10px; background: #f8f9fa; border-left: 4px solid #007bff;">Processando imagem fixa, por favor aguarde...</div>';
    
    // Enviar apenas o token CSRF para o endpoint
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    
    axios.post('/together/chat', formData, { withCredentials: true })
      .then(res => {
        console.log('URL da imagem fixa:', res.data.image_url);
        document.getElementById('resultado').innerText =
          JSON.stringify(res.data.body, null, 2);
      })
      .catch(err => {
        console.error('Erro completo:', err.response?.data || err);
        document.getElementById('resultado').innerText = 
          `Erro ${err.response?.status || ''}: ${JSON.stringify(err.response?.data || err.message, null, 2)}`;
      });
  });

  
  </script>
</body>
</html>
