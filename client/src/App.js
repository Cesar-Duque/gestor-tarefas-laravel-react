import { Routes, Route, Navigate } from 'react-router-dom';
import Navbar from './components/Navbar';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import DashboardPage from './pages/DashboardPage';
import ProtectedRoute from './components/ProtectedRoute';

import './App.css';

/*
|##########################################################################
| # App.js — ROOT Component: Tabela de Rotas da SPA                      #
|##########################################################################
|
| ########################################################################
| # React Router v6 — Declarative Routing                                #
| ########################################################################
|
| Componentes principais:
|
|   <Routes>  = container que escolhe QUAL <Route> renderizar conforme URL.
|               (switch do router v5 substituído por Routes + match exato)
|
|   <Route path="/x" element={<Y />} />
|               = mapeia URL path para um componente.
|
|   <Navigate to="/dashboard" replace />
|               = redireciona programaticamente (redirect do router v5)
|
| Roteamento declarativo:
|   Dizemos QUEREMOS renderizar em cada URL, não controlamos o navegador
|   passo a passo. O Router faz isso para nós.
|
|
| ########################################################################
| # ProtectedRoute — Higher Order Component (HOC) de segurança          #
| ########################################################################
|   Qualquer rota filha de <ProtectedRoute> SÓ é acessível se o usuário
|   estiver autenticado. Caso contrário: redireciona para /login.
|   Ver component/ProtectedRoute.jsx.
|##########################################################################
*/

function App() {
  return (
    <div className="min-vh-100 bg-light">
      {/* Navbar: cabeçalho fixo no topo. Aparece em TODAS as páginas. */}
      <Navbar />

      {/* Container central: padding + Bootstrap responsivo */}
      <main className="container py-4">
        <Routes>
          {/* ================================================================
           ROTAS PÚBLICAS (qualquer pessoa acessa, não precisa login)
           ================================================================ */}
          <Route path="/login" element={<LoginPage />} />
          <Route path="/register" element={<RegisterPage />} />

          {/* ================================================================
           ROTAS PROTEGIDAS (só usuários autenticados)
           Para acessar o conteúdo, é obrigatório estar logado com token
           válido no localStorage.
           ================================================================ */}
          <Route
            path="/dashboard"
            element={
              <ProtectedRoute>
                <DashboardPage />
              </ProtectedRoute>
            }
          />

          {/* ================================================================
           ROTA RAIZ:
              • Logado → vai para /dashboard
              • Não logado → vai para /login
           Usamos Navigate (replace = true substitui a entrada no histórico)
           ================================================================ */}
          <Route
            path="/"
            element={<Navigate to="/dashboard" replace />}
          />

          {/* Qualquer URL não reconhecida → 404 redireciona pra dashboard
              (ou poderia criar uma página 404 dedicada). */}
          <Route
            path="*"
            element={<Navigate to="/dashboard" replace />}
          />
        </Routes>
      </main>
    </div>
  );
}

export default App;
