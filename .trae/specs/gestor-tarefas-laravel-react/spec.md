# Gestor de Tarefas - Product Requirements Document

## Overview
- **Summary**: Aplicação full-stack de Gestão de Tarefas construída com Laravel 8 (Backend API REST com Sanctum) e React (Frontend SPA com Bootstrap). O projeto é desenvolvido com fins didáticos: código extensivamente documentado e comentado, com explicações teóricas sobre desenvolvimento web, padrões arquiteturais e práticas recomendadas.
- **Purpose**: Fornecer um caso de estudo completo e funcional para o usuário aprender, na prática: (1) padrão arquitetural cliente-servidor REST, (2) autenticação stateless com tokens, (3) ciclo de vida de requisições HTTP, (4) padrão MVC no backend, (5) componentes React com hooks e gerenciamento de estado, (6) validação e tratamento de erros, (7) relações ORM e paginação server-side.
- **Target Users**: Desenvolvedor em aprendizado que deseja compreender profundamente como um app full-stack funciona por trás dos panos, não apenas "funcionar", mas **porquê** e **como** cada parte se conecta.

## Goals
- Entregar aplicação funcional rodando localmente com backend Laravel + frontend React.
- Todo código (PHP/JavaScript) extensivamente comentado explicando a teoria (HTTP, MVC, REST, etc.).
- Documentação inline explicando o "porquê" de cada padrão, não só o "o quê".
- Separação clara entre explicações teóricas dentro dos blocos de comentário.
- Cobertura de casos reais: autenticação, erros, validações, paginação, relações.

## Non-Goals
- Não implementar features avançadas: notificações em tempo real, websockets, upload de arquivos, multi-tenant, roles/permissions complexas.
- Não focar em deploy em produção (Docker, CI/CD, otimizações de performance extremas).
- Não implementar testes automatizados (a menos que em comentários didáticos expliquem o conceito).

## Background & Context
- Projeto do zero, diretório vazio.
- Backend: Laravel 8 com Sanctum para autenticação API (tokens de API).
- Frontend: React standalone (Create React App) consumindo a API via Axios.
- UI: Bootstrap 5 para componentes de UI.
- Banco: MySQL.
- Comunicação: JSON sobre HTTPSstateless, CORS configurado.

## Functional Requirements

- **FR-1 Autenticação API**:
  - Registro de usuário (POST /api/register)
  - Login (POST /api/login) retorna token Bearer
  - Logout (POST /api/logout) invalida token
  - Perfil do usuário autenticado (GET /api/user)
- **FR-2 Gestão de Categorias (1:N com Tarefas)**:
  - CRUD completo de categorias (index, show, store, update, destroy)
  - Cada categoria pertence a um usuário
- **FR-3 Gestão de Tarefas (N:1 com Categoria)**:
  - CRUD completo de tarefas
  - Campos: título, descrição, status (pendente, em_progresso, concluida), data_vencimento, prioridade (baixa, media, alta), categoria_id
  - Listagem paginada (10 itens/página)
  - Filtros por status, prioridade e categoria
  - Ordenação por data de vencimento, prioridade, data de criação
- **FR-4 Validação e Erros**:
  - Validação server-side com Form Requests
  - Respostas de erro estruturadas (campos violados + mensagens)
  - Mensagens amigáveis e mensagens de erro formatadas consistentemente
- **FR-5 Frontend React**:
  - Tela de Login/Registro com formulários e tratamento de erros
  - Dashboard com listagem de tarefas paginada
  - CRUD de tarefas e categorias via modais ou rotas dedicadas
  - Filtros na listagem
  - Feedback visual de loading e erros
  - Logout e gerenciamento de token (persistência em localStorage)

## Non-Functional Requirements
- **NFR-1 Documentação Didática**: Todo arquivo de código relevante deve conter comentários explicando a teoria por trás do conceito. Ex: "Este é um **Service Container** é uma ideia de IoC..." ou "Este método usa **Eloquent ORM** — padrão Active Record..."
- **NFR-2 Comentários Educacionais**: Em cada arquivo, explicar:
  - (1) O que o arquivo faz (header de arquivo)
  - (2) Teoria envolvida (HTTP verbos, padrões, ou conceito
  - (3) Fluxo de dados
- **NFR-3 Padrões de Projeto**: Seguir PSR-12 no PHP, separação de responsabilidades (S do SOLID: Single Responsibility), Repository pattern através de Service classes e Form Requests para validação.
- **NFR-4 Separação de Camadas**: Rotas → Controllers → Services/Models → Resources Responses, nunca lógica de negócio em controllers.
- **NFR-5 Segurança Básica**: Validação CSRF (Sanctum stateful para SPA?), autenticação Bearer token, hash de senhas com bcrypt, validação de entrada, CORS configurado adequadamente.
- **NFR-6 Código Limpo**: Nomes de variáveis, funções e arquivos semanticamente corretos em inglês, comentários em PT-BR para fins didáticos.

## Constraints
- **Technical**:
  - PHP ^7.3 | ^8.0 (Laravel 8)
  - Laravel 8.x (não superior)
  - Laravel Sanctum para autenticação
  - MySQL 5.7+ ou 8.x
  - Node.js >= 14.x
  - React 17+ ou 18
  - Bootstrap 5
  - Axios para requisições HTTP
- **Business**: Todo código deve ser compreensível por iniciante; explicações passo a passo, não pular etapas de teoria.
- **Dependencies**: Composer, Node, npm/yarn.

## Assumptions
- Usuário tem Composer e Node instalados localmente, ou vai instalar.
- MySQL disponível (XAMPP/WAMP/Laragon/MySQL standalone.
- Projeto roda em ambiente de desenvolvimento local (php artisan serve + npm start).

## Acceptance Criteria

### AC-1: Backend Laravel instalado e rodando
- **Type**: `rule`
- **Given**: Composer disponível
- **When**: Rodar `php artisan serve
- **Then**: Acessar `http://localhost:8000` e ver página padrão do Laravel 8
- **Pass Condition**: Página Laravel 8 carrega sem erros
- **Evidence**: Screenshot ou terminal confirmando serve running

### AC-2: API de Autenticação Sanctum funcionando
- **Type**: `rule`
- **Given**: Banco de dados configurado
- **When**: Enviar POST para /api/register com nome, email, senha e depois POST /api/login
- **Then**: Receber token de autenticação Bearer 200 com status 200 e token válido
- **Pass Condition**: Registro e login retornam JSON com token e dados do usuário
- **Evidence**: Teste com Postman/Insomnia ou curl

### AC-3: CRUD de Categorias e Tarefas com Relações
- **Type**: `rule`
- **Given**: Usuário autenticado
- **When**: Realizar operações CRUD através de API endpoints
- **Then**: Dados persistem em MySQL, relações categoria -> tarefas são mantidas
- **Pass Condition**: Todas as operações (index, show, store, update, destroy respondem corretamente com status HTTP apropriado
- **Evidence**: Respostas JSON consistentes

### AC-4: Validação e tratamento de erros estruturado
- **Type**: `rule`
- **Given**: Requisição inválida (ex: título vazio)
- **When**: Enviar POST/PUT com dados inválidos
- **Then**: Resposta 422 com campos de erro estruturados
- **Pass Condition**: JSON com mensagens por campo em português (ou inglês claro)
- **Evidence**: Resposta JSON com erros

### AC-5: Paginação e Filtros
- **Type**: `rule`
- **Given**: 15+ tarefas no banco
- **When**: GET /api/tasks?page=2&status=pendente
- **Then**: 10 itens por página, filtro de status aplicado, links de paginação
- **Pass Condition**: Retorno paginado corretamente filtrado
- **Evidence**: JSON com estrutura de paginação

### AC-6: Frontend React rodando e autenticação
- **Type**: `rule`
- **Given**: npm start
- **When**: Acessar http://localhost:3000
- **Then**: Tela de login, registrar-se e fazer login
- **Pass Condition**: Tela de login renderiza, após login vai para dashboard
- **Evidence**: Browser

### AC-7: CRUD de Tarefas no React com Feedback Visual
- **Type**: `rule`
- **Given**: Usuário logado no frontend
- **When**: Criar, editar, listar paginado, filtrar, excluir tarefa e categoria via UI
- **Then**: Operações refletem no backend e atualizam a UI sem refresh
- **Pass Condition**: Toda operação CRUD funciona via frontend com feedback de loading/erro/sucesso
- **Evidence**: UI do navegador

### AC-8: Documentação Didática (Comentários Teóricos)
- **Type**: `rubric`
- **Dimension**: Clareza, profundidade e abrangência das explicações teóricas
- **Scale**: 1-5
- **Anchors**: 1 = código sem comentários; 3 = comentários apenas o que faz, sem teoria; 5 = comentários profundos sobre HTTP, MVC, REST, Eloquent, Sanctum, React hooks explicando o porquê
- **Pass Threshold**: >= 4
- **Evidence**: Revisão de arquivos-chave

### AC-9: Qualidade e Padrões de Código
- **Type**: `rubric`
- **Dimension**: Aderência a boas práticas (separação de responsabilidades, nomenclatura, consistência)
- **Scale**: 1-5
- **Anchors**: 1 = lógica toda em controllers, nomes confusos; 3 = separação básica; 5 = Services, Form Requests, API Resources, PSR-12, clean code
- **Pass Threshold**: >= 4
- **Evidence**: Revisão de código

### AC-10: Explicação do Fluxo Completo
- **Type**: `rubric`
- **Dimension**: Facilidade de compreensão do fluxo completo requisição -> resposta
- **Scale**: 1-5
- **Anchors**: 1 = não dá para seguir; 3 = fluxo parcialmente explicado; 5 = comentários em cada etapa explicam exatamente o que acontece em cada arquivo
- **Pass Threshold**: >= 4
- **Evidence**: Revisão

## Open Questions
- Nenhuma.
