import React, { useState } from 'react';
import Card from 'react-bootstrap/Card';
import ListGroup from 'react-bootstrap/ListGroup';
import Button from 'react-bootstrap/Button';
import Modal from 'react-bootstrap/Modal';
import Form from 'react-bootstrap/Form';
import Alert from 'react-bootstrap/Alert';
import Badge from 'react-bootstrap/Badge';
import Spinner from 'react-bootstrap/Spinner';
import CategoryService from '../../services/CategoryService';

/*
|##########################################################################
| # CategoryManager — Sidebar com CRUD simples de categorias             #
|##########################################################################
|
| SMART component: tem a própria lógica de chamada API, estado e modal.
| Não complica o DashboardPage repassando estados de edição.
|
| Recebe:
|   categories: array pronto para renderizar (listado pelo pai)
|   loading:    boolean
|   onRefresh:  callback() -> pede ao pai recarregar categorias + tarefas
|   onFeedback: (variant, message) -> exibe FeedbackAlert no Dashboard
|##########################################################################
*/

const EMPTY_FORM = { name: '', description: '' };

export default function CategoryManager({
  categories = [],
  loading = false,
  onRefresh,
  onFeedback,
}) {
  const [showModal, setShowModal] = useState(false);
  const [mode, setMode] = useState('create'); // 'create' | 'edit'
  const [editingId, setEditingId] = useState(null);
  const [form, setForm] = useState(EMPTY_FORM);
  const [submitting, setSubmitting] = useState(false);
  const [errors, setErrors] = useState({});

  const openCreate = () => {
    setMode('create');
    setEditingId(null);
    setForm(EMPTY_FORM);
    setErrors({});
    setShowModal(true);
  };

  const openEdit = (cat) => {
    setMode('edit');
    setEditingId(cat.id);
    setForm({ name: cat.name, description: cat.description || '' });
    setErrors({});
    setShowModal(true);
  };

  const closeModal = () => {
    if (submitting) return;
    setShowModal(false);
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm((p) => ({ ...p, [name]: value }));
    if (errors[name]) setErrors((p) => { const n = { ...p }; delete n[name]; return n; });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setErrors({});
    try {
      if (mode === 'create') {
        await CategoryService.store(form);
        onFeedback?.('success', 'Categoria criada com sucesso!');
      } else {
        await CategoryService.update(editingId, form);
        onFeedback?.('success', 'Categoria atualizada!');
      }
      setShowModal(false);
      onRefresh?.();
    } catch (err) {
      const res = err.response;
      if (res?.status === 422) {
        setErrors(res.data.errors || {});
        onFeedback?.('danger', res.data.message || 'Verifique os campos.');
      } else {
        onFeedback?.('danger', res?.data?.message || 'Erro ao salvar categoria.');
      }
    } finally {
      setSubmitting(false);
    }
  };

  const handleDelete = async (cat) => {
    if (!window.confirm(`Tem certeza que deseja excluir "${cat.name}"?
Tarefas desta categoria continuarão existindo (sem categoria).`)) {
      return;
    }
    try {
      await CategoryService.destroy(cat.id);
      onFeedback?.('success', 'Categoria excluída.');
      onRefresh?.();
    } catch (err) {
      onFeedback?.('danger', err?.response?.data?.message || 'Erro ao excluir.');
    }
  };

  return (
    <Card className="shadow-sm border sticky-md-top" style={{ top: 16 }}>
      <Card.Header className="bg-white d-flex justify-content-between align-items-center py-3">
        <h5 className="mb-0 fw-bold">🏷️ Categorias</h5>
        <Button size="sm" variant="primary" onClick={openCreate}>
          + Nova
        </Button>
      </Card.Header>
      <Card.Body className="p-0">
        <ListGroup variant="flush">
          {loading && (
            <ListGroup.Item className="text-center py-3 text-muted small">
              <Spinner as="span" size="sm" animation="border" className="me-2" />
              Carregando...
            </ListGroup.Item>
          )}
          {!loading && categories.length === 0 && (
            <ListGroup.Item className="text-muted small py-4 text-center">
              Nenhuma categoria ainda.
            </ListGroup.Item>
          )}
          {categories.map((c) => (
            <ListGroup.Item
              key={c.id}
              className="d-flex justify-content-between align-items-center gap-2"
            >
              <div className="d-flex align-items-center gap-2 overflow-hidden">
                <span className="fw-semibold text-truncate">{c.name}</span>
                {typeof c.tasks_count === 'number' && (
                  <Badge bg="light" text="dark" className="border flex-shrink-0">
                    {c.tasks_count}
                  </Badge>
                )}
              </div>
              <div className="d-flex gap-1 flex-shrink-0">
                <Button
                  size="sm"
                  variant="link"
                  className="text-decoration-none"
                  onClick={() => openEdit(c)}
                  title="Editar categoria"
                >
                  ✏️
                </Button>
                <Button
                  size="sm"
                  variant="link"
                  className="text-danger text-decoration-none"
                  onClick={() => handleDelete(c)}
                  title="Excluir categoria"
                >
                  🗑️
                </Button>
              </div>
            </ListGroup.Item>
          ))}
        </ListGroup>
      </Card.Body>

      {/* Modal Criar/Editar */}
      <Modal show={showModal} onHide={closeModal} backdrop="static" centered>
        <Modal.Header closeButton>
          <Modal.Title className="h6 fw-bold">
            {mode === 'create' ? '🏷️ Nova Categoria' : '✏️ Editar Categoria'}
          </Modal.Title>
        </Modal.Header>
        <Form onSubmit={handleSubmit}>
          <Modal.Body>
            {Object.keys(errors).length > 0 && (
              <Alert variant="danger">
                <ul className="small mb-0 ps-3">
                  {Object.entries(errors).map(([k, msgs]) => (
                    <li key={k}>
                      <strong>{k}</strong>: {(msgs || []).join('; ')}
                    </li>
                  ))}
                </ul>
              </Alert>
            )}

            <Form.Group className="mb-3" controlId="catName">
              <Form.Label>Nome da categoria *</Form.Label>
              <Form.Control
                type="text"
                name="name"
                value={form.name}
                onChange={handleChange}
                isInvalid={Array.isArray(errors.name) && errors.name.length > 0}
                required
                placeholder="Ex: Trabalho"
                disabled={submitting}
                autoFocus
              />
              <Form.Control.Feedback type="invalid">
                {(errors.name || []).join(' ')}
              </Form.Control.Feedback>
            </Form.Group>

            <Form.Group className="mb-0" controlId="catDesc">
              <Form.Label>Descrição (opcional)</Form.Label>
              <Form.Control
                as="textarea"
                rows={3}
                name="description"
                value={form.description}
                onChange={handleChange}
                disabled={submitting}
              />
            </Form.Group>
          </Modal.Body>
          <Modal.Footer>
            <Button
              variant="light"
              className="border"
              onClick={closeModal}
              disabled={submitting}
            >
              Cancelar
            </Button>
            <Button variant="primary" type="submit" disabled={submitting}>
              {submitting ? (
                <>
                  <Spinner as="span" size="sm" animation="border" className="me-2" />
                  Salvando...
                </>
              ) : mode === 'create' ? 'Criar' : 'Salvar'}
            </Button>
          </Modal.Footer>
        </Form>
      </Modal>
    </Card>
  );
}
