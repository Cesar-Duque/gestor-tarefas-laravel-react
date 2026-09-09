import React, { useState } from 'react';
import Form from 'react-bootstrap/Form';
import Button from 'react-bootstrap/Button';
import Card from 'react-bootstrap/Card';
import Alert from 'react-bootstrap/Alert';
import Spinner from 'react-bootstrap/Spinner';
import Container from 'react-bootstrap/Container';
import Row from 'react-bootstrap/Row';
import Col from 'react-bootstrap/Col';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

/*
| Página de Registro de novo usuário.
| Similar ao LoginPage, mas com password_confirmation
| (conforme a regra confirmed() no Form Request do Laravel).
*/
export default function RegisterPage() {
  const { register, isAuthenticated } = useAuth();
  const navigate = useNavigate();

  React.useEffect(() => {
    if (isAuthenticated) navigate('/dashboard', { replace: true });
  }, [isAuthenticated, navigate]);

  const [form, setForm] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  });

  const [submitting, setSubmitting] = useState(false);
  const [globalError, setGlobalError] = useState('');
  const [fieldErrors, setFieldErrors] = useState({});

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
    if (fieldErrors[name]) {
      setFieldErrors((prev) => ({ ...prev, [name]: undefined }));
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setGlobalError('');
    setFieldErrors({});
    try {
      await register(form);
    } catch (err) {
      const res = err.response;
      if (res?.status === 422) {
        setFieldErrors(res.data.errors || {});
        setGlobalError(res.data.message || 'Verifique os campos.');
      } else {
        setGlobalError(res?.data?.message || 'Erro inesperado.');
      }
    } finally {
      setSubmitting(false);
    }
  };

  const isInvalid = (f) => !!fieldErrors[f];

  return (
    <Container className="mt-5 mb-5">
      <Row className="justify-content-center">
        <Col md={6} lg={5}>
          <Card className="shadow-sm">
            <Card.Header className="bg-white border-bottom py-3">
              <h4 className="mb-0 fw-bold text-center">📝 Criar Conta</h4>
            </Card.Header>
            <Card.Body>
              <Form noValidate onSubmit={handleSubmit}>
                {globalError && (
                  <Alert variant="danger" dismissible onClose={() => setGlobalError('')}>
                    {globalError}
                  </Alert>
                )}

                <Form.Group className="mb-3" controlId="formName">
                  <Form.Label>Nome completo</Form.Label>
                  <Form.Control
                    type="text"
                    name="name"
                    placeholder="Ex: Maria da Silva"
                    value={form.name}
                    onChange={handleChange}
                    isInvalid={isInvalid('name')}
                    required
                    autoComplete="name"
                  />
                  <Form.Control.Feedback type="invalid">
                    {(fieldErrors.name || []).join(' ')}
                  </Form.Control.Feedback>
                </Form.Group>

                <Form.Group className="mb-3" controlId="formEmail">
                  <Form.Label>E-mail</Form.Label>
                  <Form.Control
                    type="email"
                    name="email"
                    placeholder="voce@exemplo.com"
                    value={form.email}
                    onChange={handleChange}
                    isInvalid={isInvalid('email')}
                    required
                    autoComplete="email"
                  />
                  <Form.Control.Feedback type="invalid">
                    {(fieldErrors.email || []).join(' ')}
                  </Form.Control.Feedback>
                </Form.Group>

                <Form.Group className="mb-3" controlId="formPassword">
                  <Form.Label>Senha</Form.Label>
                  <Form.Control
                    type="password"
                    name="password"
                    placeholder="No mínimo 8 caracteres"
                    value={form.password}
                    onChange={handleChange}
                    isInvalid={isInvalid('password')}
                    required
                    autoComplete="new-password"
                  />
                  <Form.Text className="text-muted d-block mb-1">
                    A senha precisa ter pelo menos 8 caracteres.
                  </Form.Text>
                  <Form.Control.Feedback type="invalid">
                    {(fieldErrors.password || []).join(' ')}
                  </Form.Control.Feedback>
                </Form.Group>

                <Form.Group className="mb-4" controlId="formPasswordConfirm">
                  <Form.Label>Confirmar senha</Form.Label>
                  <Form.Control
                    type="password"
                    name="password_confirmation"
                    placeholder="Digite a senha novamente"
                    value={form.password_confirmation}
                    onChange={handleChange}
                    isInvalid={isInvalid('password_confirmation')}
                    required
                    autoComplete="new-password"
                  />
                  <Form.Control.Feedback type="invalid">
                    {(fieldErrors.password_confirmation || []).join(' ')}
                  </Form.Control.Feedback>
                </Form.Group>

                <Button
                  variant="primary"
                  type="submit"
                  className="w-100"
                  disabled={submitting}
                >
                  {submitting ? (
                    <>
                      <Spinner as="span" animation="border" size="sm" className="me-2" />
                      Criando conta...
                    </>
                  ) : (
                    'Criar minha conta'
                  )}
                </Button>
              </Form>

              <div className="mt-4 text-center text-muted small">
                Já tem conta?{' '}
                <Link to="/login" className="text-decoration-none fw-semibold">
                  Entrar
                </Link>
              </div>
            </Card.Body>
          </Card>
        </Col>
      </Row>
    </Container>
  );
}
