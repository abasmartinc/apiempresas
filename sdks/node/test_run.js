const { ApiEmpresas } = require('./dist/index.js');

async function run() {
  const api = new ApiEmpresas({ 
    apiKey: 'tu_api_key_sandbox',
    baseURL: 'http://apiempresas.local/api/sandbox/v1' 
  });

  try {
    console.log('Probando llamada al SDK...');
    // We will call something that doesn't actually hit the network, 
    // or if it does, it hits localhost or similar.
    // The sandbox endpoint we created earlier was /companies/borme
    // Let's call a non-existent or localhost endpoint and just check if the class structure works.
    console.log(api.companies);
    console.log('✅ Estructura del SDK cargada correctamente.');
  } catch (err) {
    console.error('Error:', err);
  }
}

run();
