import React from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import Spinner from 'react-bootstrap/Spinner';

/*
|##########################################################################
| # ProtectedRoute = HOC (Higher-Order Component) de Autorização          #
|##########################################################################
|
| ########################################################################
| # Higher-Order Component (HOC)                                         #
| ########################################################################
|   HOC = Componente que RECEBE um componente FILHO por props.children,
|   adiciona ALGUMA LÓGICA extra (no caso: checagem de autenticação),
|   e:
|      • SE permitido → renderiza {children} (o conteúdo protegido)
|      • SE NÃO permitido → retorna um <Navigate to="/login">
|
| Assim evita DUPLICAR a checagem "isAuthenticated ? A : B"
| em CADA página. Se tiver 20 páginas protegidas, não precisa de 20 ifs.
|
| Outro termo: Wrapper (envelope).
|##########################################################################
*/

export default function ProtectedRoute({ children }) {
  const { isAuthenticated, loading } = useAuth();
  const location = useLocation();

  // ⏳ Ainda validando token no backend (boot), mostra um loading centralizado
  if (loading) {
    return (
      <div className="d-flex justify-content-center align-items-center" style={{ minHeight: 200 }}>
        <Spinner animation="border" role="status" variant="primary">
          <span className="visually-hidden">Carregando...</span>
        </Spinner>
        <span className="ms-3 text-muted">Validando sessão...</span>
      </div>
    );
  }

  /* 🔒 NÃO autenticado?
   *   - Redireciona para /login
   *   - Salva a URL que o usuário QUERIA acessar em state.from
   *     (após login, podemos voltá-lo para a página desejada — UX melhor).
   */
  if (!isAuthenticated) {
    return <Navigate to="/login" replace state={{ from: location }} />;
  }

  /* ✅ Autenticado → mostra o conteúdo da rota (DashboardPage, etc.) */
  return children;
}
