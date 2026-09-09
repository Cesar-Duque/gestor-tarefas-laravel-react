import React from 'react';
import Row from 'react-bootstrap/Row';
import Col from 'react-bootstrap/Col';
import Form from 'react-bootstrap/Form';
import Button from 'react-bootstrap/Button';

/*
|##########################################################################
| # TaskFilters: Barra de filtros + ordenação da listagem de tarefas      #
|##########################################################################
|
| ########################################################################
| # Formulário Controlado para Filtros                                   #
| ########################################################################
|
| Os filtros são campos independentes, mas qualquer alteração precisa
| refletir de volta no PAI (DashboardPage), pois o pai faz o fetch da API.
|
| Para isso usamos "LIFTING STATE UP":
|   → Os valores dos filtros são mantidos NO COMPONENTE PAI (Dashboard)
|   → Aqui recebemos via props: filters + onChange(filtrosAtualizados)
|   → Qualquer modificação em um select → montamos um NOVO objeto filters
|     e devolvemos para o pai via onChange.
|   → O pai, ao receber, re-faz a requisição GET /api/tasks?status=pendente...
|
| Isso é o padrão mais comum: filho é "controlado" pelo pai.
|##########################################################################
*/

const STATUS_OPTIONS = [
  { value: '', label: 'Todos os status' },
  { value: 'pendente', label: 'Pendente' },
  { value: 'em_progresso', label: 'Em Progresso' },
  { value: 'concluida', label: 'Concluída' },
];

const PRIORIDADE_OPTIONS = [
  { value: '', label: 'Todas as prioridades' },
  { value: 'alta', label: 'Alta' },
  { value: 'media', label: 'Média' },
  { value: 'baixa', label: 'Baixa' },
];

const SORT_OPTIONS = [
  { value: 'created_at', label: 'Data de criação' },
  { value: 'due_date', label: 'Data de vencimento' },
  { value: 'title', label: 'Título (A-Z)' },
];

export default function TaskFilters({
  filters,
  onChange,
  categories,
  onClear,
  onCreateNew,
}) {
  const updateField = (name, value) => {
    onChange({ ...filters, [name]: value });
  };

  return (
    <div className="bg-white border rounded p-3 mb-4 shadow-sm">
      <Row className="g-2 align-items-end">
        {/* Filtro: Status */}
        <Col xs={12} sm={6} md={3}>
          <Form.Label className="mb-1 small fw-semibold">Status</Form.Label>
          <Form.Select
            size="sm"
            value={filters.status ?? ''}
            onChange={(e) => updateField('status', e.target.value || null)}
          >
            {STATUS_OPTIONS.map((opt) => (
              <option key={opt.value} value={opt.value}>{opt.label}</option>
            ))}
          </Form.Select>
        </Col>

        {/* Filtro: Prioridade */}
        <Col xs={12} sm={6} md={3}>
          <Form.Label className="mb-1 small fw-semibold">Prioridade</Form.Label>
          <Form.Select
            size="sm"
            value={filters.prioridade ?? ''}
            onChange={(e) => updateField('prioridade', e.target.value || null)}
          >
            {PRIORIDADE_OPTIONS.map((opt) => (
              <option key={opt.value} value={opt.value}>{opt.label}</option>
            ))}
          </Form.Select>
        </Col>

        {/* Filtro: Categoria */}
        <Col xs={12} sm={6} md={3}>
          <Form.Label className="mb-1 small fw-semibold">Categoria</Form.Label>
          <Form.Select
            size="sm"
            value={filters.category_id ?? ''}
            onChange={(e) => {
              const v = e.target.value;
              updateField('category_id', v === '' ? null : Number(v));
            }}
          >
            <option value="">Todas as categorias</option>
            {categories.map((c) => (
              <option key={c.id} value={c.id}>{c.name}</option>
            ))}
          </Form.Select>
        </Col>

        {/* Ordenação + Botões */}
        <Col xs={12} sm={6} md={3}>
          <Form.Label className="mb-1 small fw-semibold">Ordenar por</Form.Label>
          <div className="d-flex gap-2">
            <Form.Select
              size="sm"
              className="flex-grow-1"
              value={filters.sort ?? 'created_at'}
              onChange={(e) => updateField('sort', e.target.value)}
            >
              {SORT_OPTIONS.map((opt) => (
                <option key={opt.value} value={opt.value}>{opt.label}</option>
              ))}
            </Form.Select>
            <Button
              size="sm"
              variant={filters.dir === 'asc' ? 'primary' : 'outline-secondary'}
              title="Ordenação crescente/decrescente"
              onClick={() => updateField('dir', filters.dir === 'asc' ? 'desc' : 'asc')}
            >
              {filters.dir === 'asc' ? '↑' : '↓'}
            </Button>
          </div>
        </Col>

        <Col xs={12} md="auto" className="ms-auto d-flex justify-content-end gap-2 mt-2 mt-md-0">
          <Button
            size="sm"
            variant="light"
            onClick={onClear}
            className="border"
          >
            Limpar
          </Button>
          <Button
            size="sm"
            variant="success"
            onClick={onCreateNew}
          >
            + Nova Tarefa
          </Button>
        </Col>
      </Row>
    </div>
  );
}
