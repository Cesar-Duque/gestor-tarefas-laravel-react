import React, { createContext, useContext, useEffect, useState, useCallback, useMemo } from 'react';
import AuthService from '../services/AuthService';
import { useNavigate } from 'react-router-dom';

/*
|##########################################################################
| # Context API + Hooks = Estado Global sem Redux                          #
|##########################################################################
|
| ########################################################################
| # Prop Drilling Problem vs Context                                     #
| ########################################################################
|
| PROBLEMA:
|   App.js tem o estado do usuário logado.
|   Componentes NETOS/BISELNETOS precisam acessar esse estado.
|   Sem Context, teria que passar props por todos os níveis intermediários:
|     App → Navbar → ProfileDropdown → AvatarDropdownItem
|     🔻 "prop drilling"
|
| SOLUÇÃO: Context API
|   Cria um "COMPARTIMENTO GLOBAL" (Provider) na árvore React.
|   Qualquer componente na árvore, em NÍVEL QUALQUER, usa useContext()
|   para ler/modificar os valores SEM passar props.
|
| Alternativas modernas: Zustand, Redux Toolkit, Jotai.
| Context é nativo do React, serve para casos pequenos/médios.
|
| ########################################################################
| # Hooks do React: useState, useEffect, useCallback, useMemo             #
| ########################################################################
|   useState   → armazenar estado LOCAL de componente (re-renderiza)
|   useEffect  → SIDE EFFECTS: chamadas API, localStorage, events,
|                 subscribe/unsubscribe, timers, etc.
|   useCallback→ memoiza referência de FUNÇÃO (evita re-renders em childs
|                 que usam React.memo)
|   useMemo    → memoiza VALOR calculado caro (evita recalcular em cada render)
|##########################################################################
*/

const AuthContext = createContext(null);

/* 🔧 Hook customizado: useAuth()
 * Qualquer componente pode ler auth com um único import:
 *   const { user, login, logout } = useAuth();
 * Evita repetir useContext(AuthContext) + null check em todo lugar.
 */
export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth deve ser usado dentro de <AuthProvider>');
  return ctx;
}

/* Provider = "Fonte da Verdade" do estado de autenticação.
 * Envolve toda a App em index.js: <AuthProvider><App/></AuthProvider>
 */
export function AuthProvider({ children }) {
  const navigate = useNavigate();

  /* 🏷️ Estado LOCAL do contexto:
   *   - user: objeto { id, name, email } ou null
   *   - token: string (Bearer token) ou null
   *   - loading: true enquanto verificamos no backend se token é válido
   *     (no refresh F5 da página, precisamos saber se o token salvo
   *     no localStorage ainda vale)
   */
  const [user, setUser] = useState(AuthService.getUser());
  const [token, setToken] = useState(AuthService.getToken());
  const [loading, setLoading] = useState(true);

  /* 🔄 Efeito: ao montar o provider (refresh F5 / primeiro acesso),
   * se temos token salvo, tentamos GET /api/user para validar com backend.
   *   - se 200 OK → atualizamos user state (dados frescos)
   *   - se 401 → token inválido → limpamos tudo
   */
  useEffect(() => {
    let mounted = true;
    async function boot() {
      if (AuthService.isAuthenticated()) {
        try {
          const freshUser = await AuthService.me();
          if (mounted) {
            setUser(freshUser);
            setToken(AuthService.getToken());
          }
        } catch (e) {
          // Token inválido → limpa
          localStorage.removeItem('auth_token');
          localStorage.removeItem('auth_user');
          if (mounted) {
            setUser(null);
            setToken(null);
          }
        }
      }
      if (mounted) setLoading(false);
    }
    boot();

    /* 🔔 Evento global emitido pelo interceptor Axios se receber 401.
     * Mesmo que o usuário esteja logado há horas, se o token for revogado
     * a resposta 401 aciona logout imediato.
     */
    const handler = () => {
      setUser(null);
      setToken(null);
      navigate('/login', { replace: true });
    };
    window.addEventListener('app:force-logout', handler);

    return () => {
      mounted = false;
      window.removeEventListener('app:force-logout', handler);
    };
  }, [navigate]);

  /* 🔑 Login: chama AuthService.login(), atualiza estado, navega para dashboard */
  const login = useCallback(async (credentials) => {
    const result = await AuthService.login(credentials);
    setUser(result.user);
    setToken(result.token);
    navigate('/dashboard', { replace: true });
    return result;
  }, [navigate]);

  /* 📝 Register (igual login, mas cria usuário primeiro) */
  const register = useCallback(async (data) => {
    const result = await AuthService.register(data);
    setUser(result.user);
    setToken(result.token);
    navigate('/dashboard', { replace: true });
    return result;
  }, [navigate]);

  /* 🚪 Logout: invalida token server, limpa estado, vai para /login */
  const logout = useCallback(async () => {
    await AuthService.logout();
    setUser(null);
    setToken(null);
    navigate('/login', { replace: true });
  }, [navigate]);

  /* useMemo evita re-render em cadeia a cada render do Provider.
   * Se os valores não mudaram, passamos a MESMA referência do objeto.
   */
  const value = useMemo(() => ({
    user,
    token,
    loading,
    isAuthenticated: !!token,
    login,
    register,
    logout,
  }), [user, token, loading, login, register, logout]);

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}
