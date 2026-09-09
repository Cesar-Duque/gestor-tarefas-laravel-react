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
|##########################################################################
| # Formulários Controlados no React                                      #
|##########################################################################
|
| ########################################################################
| # Controlled vs Uncontrolled Components                                 #
| ########################################################################
|
| UNCONTROLLED (não controlado):
|   Usa useRef() ou document.getElementById() para ler o valor APENAS no
|   momento do submit. Simples, mas difícil validar em tempo real.
|
| CONTROLADO (mais comum e recomendado):
|   Cada input TEM seu valor AMARRADO a um useState (fonte da verdade = React).
|   OnChange → atualiza o state → o input recebe o valor via `value` prop.
|   React SABE em tempo real TUDO o que o usuário digitou.
|     → fácil validação em tempo real, habilitar botão, etc.
|
| Aqui usamos useState({ email, password }) + onChange único.
|##########################################################################
*/

export default function LoginPage() {
  const { login, isAuthenticated } = useAuth();
  const navigate = useNavigate();

  /* Se já estiver logado, não precisa ficar na tela de login → vai dashboard */
  React.useEffect(() => {
    if (isAuthenticated) navigate('/dashboard', { replace: true });
  }, [isAuthenticated, navigate]);

  /* 🏷️ Estado do formulário */
  const [form, setForm] = useState({ email: '', password: '' });

  /* 🔄 Estado geral: loading, success/error messages, erros por campo */
  const [submitting, setSubmitting] = useState(false);
  const [globalError, setGlobalError] = useState('');
  const [fieldErrors, setFieldErrors] = useState({});

  /* onChange handler: atualiza UM campo do form state a cada tecla */
  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
    // Limpa erro do campo específico quando usuário começa a editar
    if (fieldErrors[name]) {
      setFieldErrors((prev) => ({ ...prev, [name]: undefined }));
    }
  };

  /* Submit do formulário */
  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setGlobalError('');
    setFieldErrors({});
    try {
      await login(form);
    } catch (err) {
      /*
       * 🔍 Parse de erros do backend:
       *   - 401 com message = credenciais inválidas.
       *   - 422 com errors.<campo> = validação do Form Request.
       */
      const res = err.response;
      if (res?.status === 401) {
        setGlobalError(res.data.message || 'E-mail ou senha inválidos.');
      } else if (res?.status === 422) {
        setFieldErrors(res.data.errors || {});
        setGlobalError(res.data.message || 'Verifique os campos e tente novamente.');
      } else {
        setGlobalError('Erro inesperado. Tente novamente mais tarde.');
      }
    } finally {
      setSubmitting(false);
    }
  };

  const isInvalid = (field) => !!fieldErrors[field];

  return (
    <Container className="mt-5">
      <Row className="justify-content-center">
        <Col md={5} lg={4}>
          <Card className="shadow-sm">
            <Card.Header className="bg-white border-bottom py-3">
              <h4 className="mb-0 fw-bold text-center">🔐 Entrar</h4>
            </Card.Header>
            <Card.Body>
              <Form noValidate onSubmit={handleSubmit}>
                {globalError && (
                  <Alert variant="danger" dismissible onClose={() => setGlobalError('')}>
                    {globalError}
                  </Alert>
                )}

                {/* 📧 Email */}
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

                {/* 🔒 Password */}
                <Form.Group className="mb-4" controlId="formPassword">
                  <Form.Label>Senha</Form.Label>
                  <Form.Control
                    type="password"
                    name="password"
                    placeholder="No mínimo 8 caracteres"
                    value={form.password}
                    onChange={handleChange}
                    isInvalid={isInvalid('password')}
                    required
                    autoComplete="current-password"
                  />
                  <Form.Control.Feedback type="invalid">
                    {(fieldErrors.password || []).join(' ')}
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
                      Entrando...
                    </>
                  ) : (
                    'Entrar'
                  )}
                </Button>
              </Form>

              <div className="mt-4 text-center text-muted small">
                Não tem conta?{' '}
                <Link to="/register" className="text-decoration-none fw-semibold">
                  Cadastre-se
                </Link>
              </div>
            </Card.Body>
          </Card>
        </Col>
      </Row>
    </Container>
  );
}
