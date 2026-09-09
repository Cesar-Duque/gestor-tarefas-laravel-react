import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';

import 'bootstrap/dist/css/bootstrap.min.css';
import './index.css';
import App from './App';
import { AuthProvider } from './context/AuthContext';
import reportWebVitals from './reportWebVitals';

/*
|##########################################################################
| # TEORIA: Virtual DOM + Bootstrap no React SPA                          #
|##########################################################################
|
| ########################################################################
| # React + Virtual DOM vs Real DOM                                     #
| ########################################################################
|
| O QUE É "DOM"?
|   DOM = Document Object Model.
|   É a representação em árvore dos elementos HTML da página
|   (ex: <html><body><div id="app">...).
|   Toda vez que você faz document.getElementById('x').innerHTML = 'oi',
|   você está ALTERANDO o DOM REAL.
|
| PROBLEMA do DOM Real:
|   Alterar o DOM real é CARO (operações lentas) se feito muitas vezes,
|   pois o navegador tem que recalcular CSS/layout/paint (reflow/repaint).
|
| SOLUÇÃO do React: VIRTUAL DOM
|   - É uma CÓPIA EM MEMÓRIA (JS puro, leve) do DOM real.
|   - Quando o state muda, React renderiza um NOVO Virtual DOM.
|   - Usa o algoritmo de RECONCILIAÇÃO (diffing) para comparar
|     o velho vs novo Virtual DOM (em O(n) heurístico).
|   - Encontra as DIFERENÇAS MÍNIMAS e APLICA APENAS ELAS no DOM real
|     em BATCH (lote), não atualiza tudo.
|   → Performance MUITO MELHOR.
|
| ########################################################################
| # Bootstrap 5 — Framework CSS Responsivo                               #
| ########################################################################
| `import 'bootstrap/dist/css/bootstrap.min.css';`
| - Importa o CSS do Bootstrap 5 (grid 12 colunas, breakpoints sm/md/lg/xl,
|   componentes: buttons, cards, modais, forms, tables, navbars, etc.)
| - Usado via react-bootstrap: componentes JSX (Button, Modal, Form,
|   Container, Navbar, etc.) que já sabem usar classes corretas.
|
| ########################################################################
| # BrowserRouter — React Router v6                                      #
| ########################################################################
| BrowserRouter habilita a navegação SPA:
|   - React manipula a URL e o histórico via History API do navegador
|     (history.pushState()), SEM recarregar a página.
|   - /login, /dashboard, etc. → mapeados para componentes.
|   - Não faz request para o servidor em cada rota (exceto refresh F5).
|
| AuthProvider → envolve App.js: torna o estado de autenticação
|   (usuário logado, token) disponível PARA TODOS os componentes,
|   sem precisar passar props manualmente por todos os níveis
|   (isso é Context API, ver em context/AuthContext.jsx).
|##########################################################################
*/

const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(
  <React.StrictMode>
    {/* 🌐 BrowserRouter: habilita roteamento SPA sem recarregar a página */}
    <BrowserRouter>
      {/* 🔐 AuthProvider: torna estado auth disponível para toda a árvore */}
      <AuthProvider>
        <App />
      </AuthProvider>
    </BrowserRouter>
  </React.StrictMode>
);

// If you want to start measuring performance in your app, pass a function
// to log results (for example: reportWebVitals(console.log))
// or send to an analytics endpoint. Learn more: https://bit.ly/CRA-vitals
reportWebVitals();
