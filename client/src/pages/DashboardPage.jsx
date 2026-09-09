import React, { useState, useEffect, useCallback } from 'react';
import Row from 'react-bootstrap/Row';
import Col from 'react-bootstrap/Col';
import Stack from 'react-bootstrap/Stack';
import Badge from 'react-bootstrap/Badge';

import { useAuth } from '../context/AuthContext';
import TaskService from '../services/TaskService';
import CategoryService from '../services/CategoryService';

import FeedbackAlert from '../components/FeedbackAlert';
import TaskFilters from '../components/tasks/TaskFilters';
import TaskTable from '../components/tasks/TaskTable';
import TaskPagination from '../components/tasks/TaskPagination';
import TaskFormModal from '../components/tasks/TaskFormModal';
import CategoryManager from '../components/categories/CategoryManager';

/*
|##########################################################################
| # DashboardPage — Componente Pai (smart/container)                     #
|##########################################################################
|
| TEORIA React: Container vs Presentational Components
|
|   • Container (Smart)  — gerencia estado, chama APIs, repassa props.
|   • Presentational (Dumb) — só renderiza o que recebe via props.
|
| Aqui o Dashboard é o container. Ele centraliza:
|   1. Estado de filtros + paginação (URL params)
|   2. CRUD tasks via Modal + chamadas ao TaskService
|   3. CRUD categorias via CategoryManager (filho auto-suficiente)
|   4. Feedback alert global (sucesso / erro)
|
| Regra Ouro: Sempre que o state (filtros, page) mudar, useEffect aciona
| re-fetch automático das tarefas.
|##########################################################################
*/

const FILTERS_EMPTY = {
  status: null,
  prioridade: null,
  category_id: null,
  sort: 'created_at',
  dir: 'desc',
  page: 1,
  per_page: 10,
};

export default function DashboardPage() {
  const { user } = useAuth();

  /* Filtros aplicados atualmente */
  const [filters, setFilters] = useState(FILTERS_EMPTY);

  /* Tarefas + metadados de paginação */
  const [taskState, setTaskState] = useState({ data: [], meta: null, loading: true });

  /* Categorias */
  const [categories, setCategories] = useState([]);
  const [catsLoading, setCatsLoading] = useState(false);

  /* Feedback visual */
  const [feedback, setFeedback] = useState({ variant: null, message: null });

  /* Modal de Criar/Editar Tarefa */
  const [modal, setModal] = useState({
    show: false, task: null, submitting: false, errors: {},
  });

  /* Estados temporários de linhas individuais (evita re-render geral com loading) */
  const [togglingId, setTogglingId] = useState(null);
  const [deletingId, setDeletingId] = useState(null);

  /* ---------------------------------------------------------------
   *  Efeito 1: Carregar lista de tarefas SEMPRE que `filters` mudar
   * --------------------------------------------------------------- */
  const loadTasks = useCallback(async () => {
    setTaskState((s) => ({ ...s, loading: true }));
    try {
      const params = {};
      Object.entries(filters).forEach(([k, v]) => {
        if (v != null && v !== '') params[k] = v;
      });
      const res = await TaskService.index(params);
      setTaskState({
        data: res.data ?? [],
        meta: res.meta ?? null,
        loading: false,
      });
    } catch (err) {
      setFeedback({ variant: 'danger', message: err?.response?.data?.message || 'Erro ao carregar tarefas.' });
      setTaskState({ data: [], meta: null, loading: false });
    }
  }, [filters]);

  useEffect(() => { loadTasks(); }, [loadTasks]);

  /* ---------------------------------------------------------------
   *  Efeito 2: Carregar categorias ao montar o componente
   * --------------------------------------------------------------- */
  const loadCategories = useCallback(async () => {
    setCatsLoading(true);
    try {
      const list = await CategoryService.index();
      setCategories(list || []);
    } catch (err) {
      setFeedback({ variant: 'danger', message: err?.response?.data?.message || 'Erro ao carregar categorias.' });
    } finally {
      setCatsLoading(false);
    }
  }, []);

  useEffect(() => { loadCategories(); }, [loadCategories]);

  /* ---------------------------------------------------------------
   *  Handlers: Filtros e Paginação
   * --------------------------------------------------------------- */
  const setFiltersAndPage = (filtros) => {
    setFilters({ ...filtros, page: 1 });
  };

  const onClearFilters = () => {
    setFilters(FILTERS_EMPTY);
  };

  const onPageChange = (page) => {
    setFilters((prev) => ({ ...prev, page }));
  };

  /* ---------------------------------------------------------------
   *  Handlers: Modal de tarefa (criar/editar)
   * --------------------------------------------------------------- */
  const openCreateTask = () => {
    setModal({ show: true, task: null, submitting: false, errors: {} });
  };

  const openEditTask = (task) => {
    setModal({ show: true, task, submitting: false, errors: {} });
  };

  const closeModal = () => {
    if (modal.submitting) return;
    setModal({ show: false, task: null, submitting: false, errors: {} });
  };

  const submitModal = async (formData) => {
    setModal((m) => ({ ...m, submitting: true, errors: {} }));
    try {
      if (modal.task) {
        await TaskService.update(modal.task.id, formData);
        setFeedback({ variant: 'success', message: 'Tarefa atualizada!' });
      } else {
        await TaskService.store(formData);
        setFeedback({ variant: 'success', message: 'Tarefa criada!' });
      }
      setModal({ show: false, task: null, submitting: false, errors: {} });
      loadTasks();
      loadCategories();
    } catch (err) {
      const res = err.response;
      if (res?.status === 422) {
        setModal((m) => ({ ...m, submitting: false, errors: res.data.errors || {} }));
      } else {
        setFeedback({ variant: 'danger', message: res?.data?.message || 'Erro ao salvar.' });
        setModal((m) => ({ ...m, submitting: false }));
      }
    }
  };

  /* ---------------------------------------------------------------
   *  Handlers: Toggle concluída / Excluir tarefa
   * --------------------------------------------------------------- */
  const toggleTask = async (task) => {
    setTogglingId(task.id);
    try {
      await TaskService.toggleComplete(task.id);
      const msg = task.status === 'concluida'
        ? 'Marcada como pendente.'
        : 'Marcada como concluída!';
      setFeedback({ variant: 'success', message: msg });
      loadTasks();
      loadCategories();
    } catch (err) {
      setFeedback({ variant: 'danger', message: err?.response?.data?.message || 'Erro ao atualizar status.' });
    } finally {
      setTogglingId(null);
    }
  };

  const deleteTask = async (task) => {
    if (!window.confirm(`Excluir tarefa "${task.title}"?`)) return;
    setDeletingId(task.id);
    try {
      await TaskService.destroy(task.id);
      setFeedback({ variant: 'success', message: 'Tarefa excluída.' });
      loadTasks();
      loadCategories();
    } catch (err) {
      setFeedback({ variant: 'danger', message: err?.response?.data?.message || 'Erro ao excluir.' });
    } finally {
      setDeletingId(null);
    }
  };

  /* ---------------------------------------------------------------
   *  Render
   * --------------------------------------------------------------- */
  const hello = (() => {
    const h = new Date().getHours();
    if (h < 12) return 'Bom dia';
    if (h < 18) return 'Boa tarde';
    return 'Boa noite';
  })();

  const totalTasks = taskState.meta?.total ?? taskState.data.length;
  const counts = countStatus(taskState.data);

  return (
    <>
      <Row className="mb-4 g-3 align-items-end">
        <Col md={8}>
          <h1 className="h3 fw-bold mb-1">
            {hello}, {user?.name?.split(' ')[0] || user?.name}! 👋
          </h1>
          <p className="text-muted mb-0">
            Você tem <strong>{totalTasks || 0}</strong>{' '}
            {totalTasks === 1 ? 'tarefa' : 'tarefas'} cadastradas.
          </p>
        </Col>
        <Col md={4}>
          <Stack direction="horizontal" gap={2} className="justify-content-md-end flex-wrap">
            <Badge bg="secondary" pill className="px-3 py-2">
              {counts.pending} Pendente(s)
            </Badge>
            <Badge bg="warning" text="dark" pill className="px-3 py-2">
              {counts.progress} Em progresso
            </Badge>
            <Badge bg="success" pill className="px-3 py-2">
              {counts.done} Concluída(s)
            </Badge>
          </Stack>
        </Col>
      </Row>

      <FeedbackAlert
        variant={feedback.variant}
        message={feedback.message}
        onClose={() => setFeedback({ variant: null, message: null })}
      />

      <TaskFilters
        filters={filters}
        categories={categories}
        onChange={setFiltersAndPage}
        onClear={onClearFilters}
        onCreateNew={openCreateTask}
      />

      <Row className="g-4 mt-2">
        <Col md={8}>
          <TaskTable
            tasks={taskState.data}
            loading={taskState.loading}
            togglingId={togglingId}
            deletingId={deletingId}
            onToggle={toggleTask}
            onEdit={openEditTask}
            onDelete={deleteTask}
          />
          <TaskPagination
            meta={taskState.meta}
            onPageChange={onPageChange}
          />
        </Col>

        <Col md={4}>
          <CategoryManager
            categories={categories}
            loading={catsLoading}
            onRefresh={() => { loadCategories(); loadTasks(); }}
            onFeedback={(v, m) => setFeedback({ variant: v, message: m })}
          />
        </Col>
      </Row>

      <TaskFormModal
        show={modal.show}
        onHide={closeModal}
        onSubmit={submitModal}
        task={modal.task}
        submitting={modal.submitting}
        errors={modal.errors}
        categories={categories}
      />
    </>
  );
}

/* Helper: conta status para os badges do header */
function countStatus(list) {
  let pending = 0;
  let progress = 0;
  let done = 0;
  list.forEach((t) => {
    if (t.status === 'pendente') pending += 1;
    else if (t.status === 'em_progresso') progress += 1;
    else if (t.status === 'concluida') done += 1;
  });
  return { pending, progress, done };
}
