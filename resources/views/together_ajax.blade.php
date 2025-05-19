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

  <style>
  .table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1rem;
    font-size: 0.9rem;
  }
  
  .table-bordered {
    border: 1px solid #dee2e6;
  }
  
  .table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, 0.05);
  }
  
  .table th, .table td {
    padding: 0.75rem;
    vertical-align: top;
    border-top: 1px solid #dee2e6;
  }
  
  .table-bordered th, .table-bordered td {
    border: 1px solid #dee2e6;
  }
  
  .thead-dark th {
    color: white;
    background-color: #343a40;
    border-color: #454d55;
  }
  
  .table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.1);
  }
  </style>
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

<div>
  <details>
    <summary>Visualizar JSON bruto</summary>
    <pre id="json-raw" style="background: #f8f9fa; padding: 10px; max-height: 300px; overflow: auto;"></pre>
  </details>
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
        
        // Mostrar o JSON bruto em um elemento oculto ou minimizado
        document.getElementById('json-raw').innerText = JSON.stringify(res.data.body, null, 2);
        
        // Formatar e exibir como tabela
        formatJsonToTable(res.data.body);
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
        
        // Se adicionar o elemento, use:
        document.getElementById('json-raw').innerText = JSON.stringify(res.data.body, null, 2);
        
        // Formatar e exibir como tabela (ao invés de mostrar JSON bruto)
        formatJsonToTable(res.data.body);
      })
      .catch(err => {
        console.error('Erro completo:', err.response?.data || err);
        document.getElementById('resultado').innerText = 
          `Erro ${err.response?.status || ''}: ${JSON.stringify(err.response?.data || err.message, null, 2)}`;
      });
  });

  function formatJsonToTable(data) {
  // Extrair o conteúdo do corpo da resposta
  const jsonContent = data.choices[0].message.content;
  
  try {
    // Tentar parsear o JSON da resposta
    const parsedData = JSON.parse(jsonContent);
    
    // Criar a estrutura da tabela
    let tableHtml = `
      <table class="table table-striped table-bordered">
        <thead class="thead-dark">
          <tr>
            <th>Exame</th>
            <th>Metodologia</th>
            <th>Resultado</th>
          </tr>
        </thead>
        <tbody>
    `;
    
    // Obter as chaves dos exames para iterar
    const exames = Object.keys(parsedData.Exame);
    
    // Para cada exame, adicionar uma linha na tabela
    exames.forEach(exame => {
      tableHtml += `
        <tr>
          <td>${exame}</td>
          <td>${parsedData.Exame[exame]}</td>
          <td>${getResultadoForExame(parsedData, exame)}</td>
        </tr>
      `;
    });
    
    tableHtml += `
        </tbody>
      </table>
    `;
    
    // Inserir a tabela HTML no resultado
    document.getElementById('resultado').innerHTML = tableHtml;
    
  } catch (error) {
    console.error('Erro ao parsear o JSON:', error);
    document.getElementById('resultado').innerText = 
      "Erro ao formatar o JSON: " + error.message + "\n\n" + jsonContent;
  }
}

// Função auxiliar para obter o resultado correspondente ao exame
function getResultadoForExame(data, exameName) {
  // Pegar o índice do exame na lista
  const index = Object.keys(data.Exame).indexOf(exameName);
  // Pegar o resultado correspondente (assumindo que estão na mesma ordem)
  const resultados = Array.from(data.Resultado);
  return resultados[index] || "N/A";
}
  </script>
</body>
</html>
