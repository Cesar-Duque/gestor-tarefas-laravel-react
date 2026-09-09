import apiClient from './apiClient';

/*
|##########################################################################
| # AuthService.js — Serviço de Autenticação no Frontend                  #
|##########################################################################
|
| ########################################################################
| # Separação em Camadas (Services / API Layer)                          #
| ########################################################################
| Sem services, cada componente faria apiClient.post('/login', ...)
| diretamente. Problemas:
|   - Se a URL mudar, precisa alterar em TODOS os componentes.
|   - Duplicação de lógica de parse de response/errors.
|
| SOLUÇÃO: Services são a "camada de abstração" sobre a API REST.
| Componentes chamam funções SEM conhecer URLs, headers, HTTP verbs.
|
| ########################################################################
| # Persistência com localStorage                                        #
| ########################################################################
| LocalStorage = armazenamento CHAVE-VALOR no navegador do usuário,
| sem data de expiração (persiste mesmo fechando a aba).
| Regras importantes:
|   • SÓ armazene DADOS NÃO SENSÍVEIS. (Não armazene senhas!)
|   • Token Bearer é OK aqui, MAS esteja ciente de XSS risk:
|       XSS = Cross-site Scripting → JS malicioso injetado pode ler localStorage.
|   → Sempre use HTTPS em produção, sanitize inputs, etc.
|
| Alternativas: sessionStorage (expira com a aba) ou cookies httpOnly.
|##########################################################################
*/
class AuthService {
  /*
   * REGISTRO de novo usuário.
   * POST /api/register
   * Salva token + user no localStorage em caso de sucesso.
   * Retorna Promise<User + token>.
   */
  async register(data) {
    const response = await apiClient.post('/register', data);
    const { user, token } = response.data;
    this.setAuthData(user, token);
    return { user, token };
  }

  /*
   * LOGIN.
   * POST /api/login
   * Retorna 200 com token OU 401 se credenciais inválidas.
   */
  async login(data) {
    const response = await apiClient.post('/login', data);
    const { user, token } = response.data;
    this.setAuthData(user, token);
    return { user, token };
  }

  /*
   * LOGOUT (no backend + limpa local).
   * POST /api/logout → invalida o token no servidor.
   * Depois limpa storage local.
   */
  async logout() {
    try {
      await apiClient.post('/logout');
    } finally {
      // Limpa SEMPRE, mesmo se backend falhar (ex: já foi deletado)
      localStorage.removeItem('auth_token');
      localStorage.removeItem('auth_user');
    }
  }

  /* Busca dados do usuário logado (usado após refresh F5). */
  async me() {
    const response = await apiClient.get('/user');
    const user = response.data;
    this.setAuthUser(user);
    return user;
  }

  /* ------------- Helpers Storage (puros, sem API) ------------------- */

  setAuthData(user, token) {
    localStorage.setItem('auth_token', token);
    localStorage.setItem('auth_user', JSON.stringify(user));
  }

  setAuthUser(user) {
    localStorage.setItem('auth_user', JSON.stringify(user));
  }

  /* Lê token do localStorage (ou null). */
  getToken() {
    return localStorage.getItem('auth_token');
  }

  /* Lê usuário parseado (ou null). */
  getUser() {
    const raw = localStorage.getItem('auth_user');
    try {
      return raw ? JSON.parse(raw) : null;
    } catch (e) {
      return null;
    }
  }

  /* Verifica rápida: tem token armazenado? (não valida expiração, por simplicidade) */
  isAuthenticated() {
    return !!this.getToken();
  }
}

// Singleton: apenas UMA instância do serviço em toda a app
export default new AuthService();
