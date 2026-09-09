import React from 'react';
import Table from 'react-bootstrap/Table';
import Button from 'react-bootstrap/Button';
import Badge from 'react-bootstrap/Badge';
import Form from 'react-bootstrap/Form';
import Spinner from 'react-bootstrap/Spinner';

/*
|##########################################################################
| # TaskTable = Tabela de listagem de Tarefas com ações CRUD              #
|##########################################################################
|
| ########################################################################
| # Componente puro (dumb component) vs smart component                 #
| ########################################################################
|
|   Dumb Component (Presentacional): só recebe dados por props, renderiza
|   JSX, não tem lógica de negócio, não faz fetch. Vantagem: reusável,
|   fácil de testar.
|
|   Smart Component (Container): gerencia estado, carrega dados, chama
|   services, passa para componentes filhos por props.
|
| TaskTable é DUMB: recebe `tasks` (array), `loading`, e callbacks
| (`onToggle`, `onEdit`, `onDelete`) via props. O pai (DashboardPage) é o
| smart component com toda a lógica de estado/API.
|
| Essa separação aumenta a manutenibilidade.
|##########################################################################
*/

/* Badges coloridos para status e prioridade.
 * Padrão: Bootstrap variant string */
function getStatusVariant(status) {
  switch (status) {
    case 'concluida':     return 'success';
    case 'em_progresso':  return 'warning';
    case 'pendente':      return 'secondary';
    default:              return 'light';
  }
}
function getPrioridadeVariant(p) {
  switch (p) {
    case 'alta':   return 'danger';
    case 'media':  return 'primary';
    case 'baixa':  return 'outline-secondary';
    default:       return 'light';
  }
}

export default function TaskTable({
  tasks = [],
  loading = false,
  onToggle,
  onEdit,
  onDelete,
  togglingId = null,
  deletingId = null,
}) {
  if (loading) {
    return (
      <div className="text-center py-5">
        <Spinner animation="border" variant="primary" />
        <div className="mt-2 text-muted small">Carregando tarefas...</div>
      </div>
    );
  }

  if (!tasks.length) {
    return (
      <div className="border rounded bg-white py-5 text-center">
        <div style={{ fontSize: 42 }}>🗒️</div>
        <p className="text-muted my-3">
          Nenhuma tarefa encontrada com os filtros selecionados.
        </p>
        <p className="text-muted small mb-0">
          Clique em <strong>+ Nova Tarefa</strong> para criar a primeira!
        </p>
      </div>
    );
  }

  return (
    <div className="bg-white border rounded overflow-hidden shadow-sm">
      <Table hover responsive className="align-middle mb-0">
        <thead className="bg-light text-muted small">
          <tr>
            <th style={{ width: 40 }} className="text-center">#</th>
            <th>Tarefa</th>
            <th style={{ width: 140 }}>Categoria</th>
            <th style={{ width: 130 }}>Status</th>
            <th style={{ width: 120 }}>Prioridade</th>
            <th style={{ width: 120 }}>Vencimento</th>
            <th style={{ width: 180 }} className="text-center">Ações</th>
          </tr>
        </thead>
        <tbody>
          {tasks.map((t) => {
            const meta = t.meta || {};
            return (
              <tr
                key={t.id}
                className={meta.is_completed ? 'text-decoration-line-through text-muted' : ''}
              >
                {/* Checkbox de marcar concluída */}
                <td className="text-center">
                  <Form.Check
                    type="checkbox"
                    id={`toggle-${t.id}`}
                    checked={meta.is_completed}
                    onChange={() => onToggle(t)}
                    disabled={togglingId === t.id}
                  />
                </td>

                {/* Título + descrição */}
                <td>
                  <div className="fw-semibold">{t.title}</div>
                  {t.description && (
                    <div className="small text-muted mt-1 text-truncate" style={{ maxWidth: 400 }}>
                      {t.description}
                    </div>
                  )}
                  {meta.is_overdue && !meta.is_completed && (
                    <Badge bg="danger" className="mt-1" pill>
                      Atrasada
                    </Badge>
                  )}
                </td>

                {/* Categoria */}
                <td>
                  {t.category ? (
                    <Badge bg="light" text="dark" pill className="border">
                      {t.category.name}
                    </Badge>
                  ) : (
                    <span className="text-muted small">—</span>
                  )}
                </td>

                {/* Status */}
                <td>
                  <Badge bg={getStatusVariant(t.status)} className="text-capitalize">
                    {meta.status_label || t.status}
                  </Badge>
                </td>

                {/* Prioridade */}
                <td>
                  {(() => {
                    const v = getPrioridadeVariant(t.prioridade);
                    const isOutline = v.startsWith('outline-');
                    return (
                      <Badge
                        pill
                        {...(isOutline
                          ? { variant: 'light', text: 'dark', className: 'border' }
                          : { bg: v })}
                      >
                        {meta.priority_label || t.prioridade}
                      </Badge>
                    );
                  })()}
                </td>

                {/* Data de vencimento */}
                <td className="small">
                  {t.due_date ? (
                    <span
                      className={
                        meta.is_overdue && !meta.is_completed
                          ? 'text-danger fw-semibold'
                          : 'text-dark'
                      }
                    >
                      {new Date(t.due_date + 'T00:00:00').toLocaleDateString('pt-BR')}
                    </span>
                  ) : (
                    <span className="text-muted">—</span>
                  )}
                </td>

                {/* Ações: editar | excluir */}
                <td className="text-center">
                  <div className="d-inline-flex gap-2">
                    <Button
                      size="sm"
                      variant="outline-primary"
                      onClick={() => onEdit(t)}
                      title="Editar"
                    >
                      ✏️ Editar
                    </Button>
                    <Button
                      size="sm"
                      variant="outline-danger"
                      onClick={() => onDelete(t)}
                      title="Excluir"
                      disabled={deletingId === t.id}
                    >
                      {deletingId === t.id ? (
                        <>
                          <Spinner as="span" size="sm" animation="border" className="me-1" />
                          ...
                        </>
                      ) : (
                        '🗑️ Excluir'
                      )}
                    </Button>
                  </div>
                </td>
              </tr>
            );
          })}
        </tbody>
      </Table>
    </div>
  );
}
