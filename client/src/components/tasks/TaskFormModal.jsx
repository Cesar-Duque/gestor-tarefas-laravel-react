import React, { useState, useEffect } from 'react';
import Modal from 'react-bootstrap/Modal';
import Form from 'react-bootstrap/Form';
import Button from 'react-bootstrap/Button';
import Alert from 'react-bootstrap/Alert';
import Spinner from 'react-bootstrap/Spinner';
import Col from 'react-bootstrap/Col';
import Row from 'react-bootstrap/Row';

/*
|##########################################################################
| # TaskFormModal — Modal de Criar / Editar Tarefa                        #
|##########################################################################
|
| TEORIA: Formulário Controlado com Estado de Edição vs Criação
|
|   Modo criar  -> props.task = null                    (POST /api/tasks)
|   Modo editar -> props.task = { id, title, ... }      (PUT /api/tasks/{id})
|
| Quando a prop `task` muda, o `useEffect` popula um ESTADO LOCAL do
| formulário. Assim, alterar um campo enquanto digita não afeta o
| objeto original de fora.
|
| Erros 422 vindos do Laravel (chaves do tipo `title` => ["Campo obrig.")
| são exibidos abaixo do respectivo campo com Form.Control.Feedback.
|##########################################################################
*/

const STATUS = [
  { value: 'pendente',      label: 'Pendente' },
  { value: 'em_progresso',  label: 'Em Progresso' },
  { value: 'concluida',     label: 'Concluída' },
];
const PRIORIDADE = [
  { value: 'baixa',  label: 'Baixa' },
  { value: 'media',  label: 'Média' },
  { value: 'alta',   label: 'Alta' },
];

export default function TaskFormModal({
  show,
  onHide,
  onSubmit,
  task,
  categories = [],
  submitting = false,
  errors = {},
}) {
  const isEdit = Boolean(task?.id);

  const [form, setForm] = useState({
    title: '', description: '', category_id: '',
    status: 'pendente', prioridade: 'media', due_date: '',
  });

  useEffect(() => {
    if (task) {
      setForm({
        title: task.title ?? '',
        description: task.description ?? '',
        category_id: task.category_id ?? task.category?.id ?? '',
        status: task.status ?? 'pendente',
        prioridade: task.prioridade ?? 'media',
        due_date: task.due_date ?? '',
      });
    } else {
      setForm({
        title: '', description: '', category_id: '',
        status: 'pendente', prioridade: 'media', due_date: '',
      });
    }
  }, [task, show]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
  };

  const submit = (e) => {
    e.preventDefault();
    onSubmit({
      ...form,
      category_id: form.category_id === '' ? null : Number(form.category_id),
    });
  };

  const invalid = (f) => Array.isArray(errors[f]) && errors[f].length > 0;

  return (
    <Modal show={show} onHide={onHide} size="lg" backdrop="static" centered>
      <Modal.Header closeButton>
        <Modal.Title className="h5 fw-bold">
          {isEdit ? '✏️ Editar tarefa' : '➕ Criar nova tarefa'}
        </Modal.Title>
      </Modal.Header>

      <Form noValidate onSubmit={submit}>
        <Modal.Body>
          {Object.keys(errors).length > 0 && (
            <Alert variant="danger">
              <Alert.Heading className="small mb-2">Verifique os campos:</Alert.Heading>
              <ul className="small mb-0 ps-3">
                {Object.entries(errors).map(([field, msgs]) => (
                  <li key={field}>
                    <strong>{field}</strong>: {(msgs || []).join('; ')}
                  </li>
                ))}
              </ul>
            </Alert>
          )}

          <Row className="g-3">
            <Form.Group as={Col} md={8} controlId="fTitle">
              <Form.Label>Título *</Form.Label>
              <Form.Control
                type="text"
                name="title"
                value={form.title}
                onChange={handleChange}
                isInvalid={invalid('title')}
                required
                placeholder="Ex: Entregar relatório para o cliente"
                disabled={submitting}
                autoFocus
              />
              <Form.Control.Feedback type="invalid">
                {(errors.title || []).join(' ')}
              </Form.Control.Feedback>
            </Form.Group>

            <Form.Group as={Col} md={4} controlId="fStatus">
              <Form.Label>Status *</Form.Label>
              <Form.Select
                name="status"
                value={form.status}
                onChange={handleChange}
                isInvalid={invalid('status')}
                disabled={submitting}
              >
                {STATUS.map((o) => (
                  <option key={o.value} value={o.value}>{o.label}</option>
                ))}
              </Form.Select>
              <Form.Control.Feedback type="invalid">
                {(errors.status || []).join(' ')}
              </Form.Control.Feedback>
            </Form.Group>

            <Form.Group as={Col} md={12} controlId="fDesc">
              <Form.Label>Descrição</Form.Label>
              <Form.Control
                as="textarea"
                rows={3}
                name="description"
                value={form.description}
                onChange={handleChange}
                isInvalid={invalid('description')}
                placeholder="Detalhes adicionais..."
                disabled={submitting}
              />
              <Form.Control.Feedback type="invalid">
                {(errors.description || []).join(' ')}
              </Form.Control.Feedback>
            </Form.Group>

            <Form.Group as={Col} md={6} controlId="fCategory">
              <Form.Label>Categoria</Form.Label>
              <Form.Select
                name="category_id"
                value={form.category_id}
                onChange={handleChange}
                isInvalid={invalid('category_id')}
                disabled={submitting}
              >
                <option value="">Nenhuma</option>
                {categories.map((c) => (
                  <option key={c.id} value={c.id}>{c.name}</option>
                ))}
              </Form.Select>
              <Form.Control.Feedback type="invalid">
                {(errors.category_id || []).join(' ')}
              </Form.Control.Feedback>
            </Form.Group>

            <Form.Group as={Col} md={3} controlId="fPrioridade">
              <Form.Label>Prioridade *</Form.Label>
              <Form.Select
                name="prioridade"
                value={form.prioridade}
                onChange={handleChange}
                isInvalid={invalid('prioridade')}
                disabled={submitting}
              >
                {PRIORIDADE.map((o) => (
                  <option key={o.value} value={o.value}>{o.label}</option>
                ))}
              </Form.Select>
              <Form.Control.Feedback type="invalid">
                {(errors.prioridade || []).join(' ')}
              </Form.Control.Feedback>
            </Form.Group>

            <Form.Group as={Col} md={3} controlId="fDueDate">
              <Form.Label>Data de Vencimento</Form.Label>
              <Form.Control
                type="date"
                name="due_date"
                value={form.due_date}
                onChange={handleChange}
                isInvalid={invalid('due_date')}
                disabled={submitting}
              />
              <Form.Control.Feedback type="invalid">
                {(errors.due_date || []).join(' ')}
              </Form.Control.Feedback>
            </Form.Group>
          </Row>
        </Modal.Body>

        <Modal.Footer>
          <Button variant="light" onClick={onHide} disabled={submitting} className="border">
            Cancelar
          </Button>
          <Button variant="primary" type="submit" disabled={submitting}>
            {submitting ? (
              <>
                <Spinner as="span" size="sm" animation="border" className="me-2" />
                Salvando...
              </>
            ) : (
              isEdit ? 'Salvar alterações' : 'Criar tarefa'
            )}
          </Button>
        </Modal.Footer>
      </Form>
    </Modal>
  );
}
