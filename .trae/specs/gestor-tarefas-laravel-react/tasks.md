# Gestor de Tarefas - Implementation Plan

## Task 1: Instalação e configuração inicial do Laravel 8
- **Status**: `pending`
- **Priority**: high
- **Depends On**: None
- **Description**:
  - Instalar Laravel 8 via Composer com configuração de ambiente (.env, banco MySQL)
  - Configurar database.php, APP_URL, etc.
  - Rodar migrations padrão do Laravel (users, password_resets, etc.)
  - Instalar e configurar Laravel Sanctum para autenticação de API
  - Configurar CORS (francois tefueh) (config/cors.php) para aceitar requisições do frontend React (localhost:3000)
  - **Comentários teóricos em arquivos de configuração explicando Service Container, .env, Facades
- **Acceptance Criteria Addressed**: AC-1
- **Test Requirements**:
  - `rule` TR-1.1: `php artisan serve` inicia servidor sem erros, página Laravel 8 carrega em http://localhost:8000
  - `rule` TR-1.2: `php artisan migrate` executa sem erros, tabelas users existem no MySQL
  - `rule` TR-1.3: Sanctum configuração está correta (sanctum.php, Kernel.php, HasApiTokens trait no User model)
  - `rubric` TR-1.4: Arquivos de configuração e bootstrap comentados com explicações de Service Container, Facades, Service Providers, ciclo de vida da request; scale 1-5; anchors 1=sem comentários; 3=alguns comentários; 5=explicativo e claro; threshold >=4; evidence: revisão de arquivos config/, app/Providers/, routes/

## Task 2: Modelos, Migrations e Relações (User, Category, Task)
- **Status**: `pending`
- **Priority**: high
- **Depends On**: Task 1
- **Description**:
  - Criar migration para `categories` (id, user_id, name, slug, description nullable, timestamps)
  - Criar migration para `tasks` (id, user_id, category_id nullable, title, description nullable, status [pendente,em_progresso,concluida], prioridade [baixa,media,alta], due_date nullable, completed_at nullable, timestamps)
  - Criar Models Category e Task com fillable, casts (status enum, prioridade enum, due_date date)
  - Definir relações: User hasMany Category / hasMany Task; Category belongsTo User / hasMany Task; Task belongsTo User / belongsTo Category
  - **Comentários teóricos explicando Eloquent ORM (Active Record), migrations, relações 1:N, N:1, casts, mutators/accessors**
- **Acceptance Criteria Addressed**: AC-3
- **Test Requirements**:
  - `rule` TR-2.1: `php artisan migrate` cria tabelas categories e tasks corretamente
  - `rule` TR-2.2: Relações funcionam via tinker (User::find(1)->categories retorna coleção)
  - `rubric` TR-2.3: Models e migrations comentados profundamente sobre ORM, padrão Active Record vs Data Mapper, relações, migrations como versionamento de BD; scale 1-5; anchors 1=sem explicação; 3=básico; 5=teoria completa; threshold >=4; evidence: revisão de arquivos app/Models/, database/migrations/

## Task 3: Form Requests, Services e API Resources para validação e resposta
- **Status**: `pending`
- **Priority**: high
- **Depends On**: Task 2
- **Description**:
  - Criar Form Requests para validação: StoreUserRequest, LoginUserRequest, StoreCategoryRequest, UpdateCategoryRequest, StoreTaskRequest, UpdateTaskRequest
  - Criar camada de Services (AuthService, CategoryService, TaskService) com lógica de negócio (validações adicionais, marcar tarefa concluída setando completed_at, etc.)
  - Criar API Resources para transformar respostas JSON consistentemente (UserResource, CategoryResource, TaskResource, TaskCollection para paginação)
  - **Comentários teóricos explicando Form Requests pattern, Service layer (SRP - Single Responsibility), API Resources (DTO para API), validação server-side, PSR-12**
- **Acceptance Criteria Addressed**: AC-4
- **Test Requirements**:
  - `rule` TR-3.1: Form Requests rejeitam dados inválidos retornando 422 com erros estruturados
  - `rule` TR-3.2: Services contêm lógica de negócio, não controllers apenas orquestram
  - `rule` TR-3.3: Respostas JSON passam por Resources com estrutura consistente (data, meta)
  - `rubric` TR-3.4: Camadas comentadas explicando SRP, DTO, separation of concerns, porque não colocar tudo em controller; scale 1-5; threshold >=4; evidence: revisão de app/Http/Requests/, app/Services/, app/Http/Resources/

## Task 4: Rotas API + Controllers (Autenticação Sanctum, Auth, Category, Task com Paginação/Filtros/Ordenação)
- **Status**: `pending`
- **Priority**: high
- **Depends On**: Task 3
- **Description**:
  - Definir rotas em routes/api.php: auth (register, login, logout, user), CRUD categories, CRUD tasks com resource routes + middleware('auth:sanctum')
  - Criar AuthController (register, login, logout, me)
  - Criar CategoryController (index, store, show, update, destroy)
  - Criar TaskController (index com paginação/filtros por status/prioridade/categoria, ordenação por due_date/prioridade/created_at, store, show, update, destroy, + marcar concluída toggleComplete)
  - Implementar paginação Laravel padrão (10/page), filtros query params, ordenação
  - **Comentários teóricos em arquivos de rotas/controllers explicando REST API (verbos HTTP GET/POST/PUT/PATCH/DELETE), Resource Controllers, Middleware, ciclo requisição HTTP (Request -> Middleware -> Controller -> Response)**
- **Acceptance Criteria Addressed**: AC-2, AC-3, AC-4, AC-5
- **Test Requirements**:
  - `rule` TR-4.1: POST /api/register cria usuário e retorna UserResource + token
  - `rule` TR-4.2: POST /api/login retorna token Bearer válido
  - `rule` TR-4.3: POST /api/logout invalida token
  - `rule` TR-4.4: Rotas /api/categories e /api/tasks protegidas por auth:sanctum (401 sem token)
  - `rule` TR-4.5: GET /api/tasks retorna dados paginados, aceita filtros por ?status=pendente, ?prioridade=alta, ?category_id=1, e ordenação ?sort=due_date|asc
  - `rubric` TR-4.6: Rotas/controllers comentados com teoria REST, verbos HTTP, middlewares, ciclo request->response; scale 1-5; threshold >=4; evidence: revisão de routes/api.php e app/Http/Controllers/

## Task 5: Criação do Frontend React + Bootstrap (estrutura base, Axios, Rotas)
- **Status**: `pending`
- **Priority**: high
- **Depends On**: Task 1
- **Description**:
  - Criar projeto React (CRA: npx create-react-app client)
  - Instalar dependências: bootstrap, react-bootstrap, react-router-dom, axios
  - Configurar Axios com baseURL (http://localhost:8000/api), interceptor de request para incluir Bearer token do localStorage, interceptor de response para 401 (logout redirect)
  - Estruturar pastas: src/pages, src/components, src/services, src/context (AuthContext), src/utils
  - Criar AuthContext com hooks: login, logout, register, user state (Contexto React ou hooks customizados)
  - Criar PrivateRoute / ProtectedRoute para rotas autenticadas
  - Criar página Login, Register, Dashboard (rota protegida)
  - **Comentários teóricos explicando Virtual DOM, React hooks (useState, useEffect, useContext), Axios interceptors, SPA vs MPA, JWT/Sanctum tokens, Autenticação stateless**
- **Acceptance Criteria Addressed**: AC-6
- **Test Requirements**:
  - `rule` TR-5.1: `npm start` (dentro de client/) inicia React em http://localhost:3000 sem erros
  - `rule` TR-5.2: Páginas Login e Register renderizam com formulários Bootstrap
  - `rule` TR-5.3: Registro e login no frontend salvam token no localStorage e redirecionam para Dashboard
  - `rule` TR-5.4: Rotas protegidas (Dashboard) bloqueiam usuários não logados
  - `rubric` TR-5.5: Estrutura React comentada com explicações teóricas de hooks, context, SPA, Axios interceptors; scale 1-5; threshold >=4; evidence: revisão de client/src/

## Task 6: Componentes React para CRUD de Categorias e Tarefas (Paginação, Filtros, Modais)
- **Status**: `pending`
- **Priority**: high
- **Depends On**: Task 4, Task 5
- **Description**:
  - Criar services (api client) separados: AuthService, CategoryService, TaskService com métodos reutilizáveis
  - Criar componente TaskList com: tabela Bootstrap, paginação client-side que reflete paginação server-side, filtros (status, prioridade, categoria) como dropdowns, ordenação, loading spinner, tratamento de erros
  - Criar TaskModal (criar/editar tarefa) com validação client-side e exibição de erros vindos da API
  - Criar CategorySidebar ou CategoryManager para CRUD de categorias
  - Criar botões: marcar concluída, editar, excluir com confirmação
  - Feedback visual: Alertas Bootstrap (success/error) em ações
  - **Comentários teóricos explicando Componentização, Props, State, Side Effects com useEffect, Formulários controlados, gerenciamento de estado, idempotência**
- **Acceptance Criteria Addressed**: AC-7
- **Test Requirements**:
  - `rule` TR-6.1: Dashboard lista tarefas com paginação (anterior/próxima), 10 por página
  - `rule` TR-6.2: Filtrar tarefas por status/prioridade/categoria atualiza a lista com a API filtrada
  - `rule` TR-6.3: Criar nova tarefa via modal persiste no backend e atualiza a lista
  - `rule` TR-6.4: Editar e excluir tarefa refletem na UI
  - `rule` TR-6.5: Marcar tarefa concluída toggle atualiza status e completed_at
  - `rule` TR-6.6: CRUD completo de categorias funciona
  - `rubric` TR-6.7: Componentes comentados explicando componentização, props, state, formulários controlados; scale 1-5; threshold >=4; evidence: revisão de client/src/components/, client/src/pages/

## Task 7: Revisão Final - Qualidade de código e comentários teóricos abrangentes
- **Status**: `pending`
- **Priority**: medium
- **Depends On**: Task 6
- **Description**:
  - Revisar TODOS os arquivos garantindo comentários teóricos consistentes em PT-BR:
    - Todo controller com bloco teórico no topo
    - Todo model com explicação do ORM
    - Todo arquivo React com explicação do conceito (hooks, context, etc.)
    - Arquivos de configuração com teoria do ciclo de vida do Laravel
  - Garantir separação de camadas: controllers enxutos, services com lógica, Form Requests para validação, Resources para resposta
  - Garantir que código segue PSR-12 (php artisan cs fixer se possível ou manualmente)
  - Garantir nomenclatura consistente, sem dead code, imports organizados
- **Acceptance Criteria Addressed**: AC-8, AC-9, AC-10
- **Test Requirements**:
  - `rubric` TR-7.1: Documentação Didática; scale 1-5; anchors 1=sem comentários; 3=parcial; 5=comentários profundos sobre HTTP, MVC, REST, Eloquent, Sanctum, React hooks; threshold >=4; evidence: revisão abrangente de arquivos
  - `rubric` TR-7.2: Qualidade Código; scale 1-5; separação de responsabilidades, nomenclatura, PSR-12, clean code; threshold >=4; evidence: revisão
  - `rubric` TR-7.3: Fluxo Completo Explicado; scale 1-5; anchors 1=não segue; 5=fluxo request->response explicado em cada arquivo; threshold >=4; evidence: revisão
