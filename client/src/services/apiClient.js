import axios from 'axios';

/*
|##########################################################################
| # Axios HTTP Client + Interceptors                                      #
|##########################################################################
|
| ########################################################################
| # Axios: Biblioteca HTTP client baseada em Promises                   #
| ########################################################################
| Fetch nativo vs Axios:
|   fetch()  → moderno, nativo, mas:
|              - não transforma JSON automaticamente
|              - não lança erro se HTTP 4xx/5xx (apenas erro de rede)
|              - sem interceptors fáceis, sem cancelamento, etc.
|   axios()  → biblioteca madura, muitos recursos:
|              - response.data já vem com JSON parseado
|              - lança exceção para status != 2xx
|              - interceptors de request/response (GLOBAL)
|              - timeout, cancelamento via AbortController, etc.
|
| BaseURL:
|   Todas as requisições feitas com apiClient.post('/login')
|   são enviadas para http://localhost:8000/api/login.
|   Não precisa repetir o prefixo toda hora.
|
| ########################################################################
| # Interceptors do Axios = "Middleware no cliente"                      #
| ########################################################################
| Como o nome diz, intercepta TODAS as requisições ANTES de serem enviadas,
| e TODAS as respostas ANTES de chegarem ao seu .then/.catch.
|
| • INTERCEPTOR DE REQUEST:
|     ↳ Adiciona automaticamente o HEADER Authorization: Bearer <token>
|       em TODAS as requisições.
|     ↳ Token vem do localStorage (persistência no navegador do usuário).
|
| • INTERCEPTOR DE RESPONSE:
|     ↳ Se receber HTTP 401 Unauthorized:
|       - Significa que o token expirou ou é inválido.
|       - Limpamos localStorage e redirecionamos para /login
|         (usuário precisa relogar).
|
| Isso é feito GLOBALMENTE — você NÃO precisa fazer em cada requisição!
|##########################################################################
*/

const apiClient = axios.create({
  baseURL: process.env.REACT_APP_API_URL || 'http://localhost:8000/api',
  /* Tempo máximo de espera para resposta antes de dar timeout (20s).
   * Evita requisições "penduradas" se o backend cair. */
  timeout: 20000,
  // Content-Type JSON por padrão
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
  /*
   * 🛡️ withCredentials = true
   *   Necessário quando supports_credentials = true no CORS do Laravel.
   *   Permite enviar cookies (se for SPA stateful) ou headers de auth
   *   com segurança via cross-origin.
   */
});

/*
|--------------------------------------------------------------------------
| INTERCEPTOR DE REQUEST (executa ANTES de sair do cliente)
|--------------------------------------------------------------------------
| Toda vez que formos fazer POST/GET/PUT/DELETE...:
|   1) Procuramos o token JWT/Bearer no localStorage.
|   2) Se existir, adicionamos o cabeçalho Authorization.
|
| O Laravel Sanctum, ao receber o request:
|   Authorization: Bearer 3|abcXYZ...
|     → procura o hash na tabela personal_access_tokens
|     → retorna o usuário dono do token
|     → disponibiliza via Auth::user() ou auth()->user()
|--------------------------------------------------------------------------
*/
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      // eslint-disable-next-line no-param-reassign
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  },
);

/*
|--------------------------------------------------------------------------
| INTERCEPTOR DE RESPONSE (executa DEPOIS de receber resposta do backend)
|--------------------------------------------------------------------------
| - Se HTTP 4xx/5xx → .catch do interceptor.
| - Caso particular: 401 Unauthorized → logout automático, pois o token
|   expirou, foi revogado ou está inválido.
|--------------------------------------------------------------------------
*/
apiClient.interceptors.response.use(
  // Resposta OK (200, 201, ...) → apenas repassa
  (response) => response,

  // Resposta de ERRO (400, 401, 403, 404, 422, 500...)
  (error) => {
    /* 401 Unauthorized = falha de autenticação.
     *   - Limpamos o token e dados do usuário
     *   - Redirecionamos para /login
     */
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('auth_user');
      // Não há navegação aqui — o AuthContext observa token e atualiza state.
      // Para forçar o redirecionamento em qualquer componente, emitimos evento.
      window.dispatchEvent(new Event('app:force-logout'));
    }
    return Promise.reject(error);
  },
);

export default apiClient;
