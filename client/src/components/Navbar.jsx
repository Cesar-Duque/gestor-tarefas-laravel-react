import React from 'react';
import NavbarBS from 'react-bootstrap/Navbar';
import Nav from 'react-bootstrap/Nav';
import Container from 'react-bootstrap/Container';
import Button from 'react-bootstrap/Button';
import Badge from 'react-bootstrap/Badge';
import { useAuth } from '../context/AuthContext';
import { Link, useLocation } from 'react-router-dom';

/*
|##########################################################################
| # Navbar = Componente de navegação topo                                  #
|##########################################################################
|
| ########################################################################
| # Componentização no React                                              #
| ########################################################################
| A arte de dividir a UI em blocos reutilizáveis e independentes.
| Cada componente:
|   - Tem sua própria estrutura JSX, estilos e estado se necessário.
|   - Recebe dados por props (de fora para dentro, sempre unidirecional).
|   - Pode disparar eventos (callbacks) para o pai.
|
| Vantagens:
|   • Reuso: Navbar em todas as páginas (não duplicar HTML em cada page)
|   • Manutenção: muda 1 lugar, muda em todos
|   • Legibilidade: arquivos pequenos, cada um com sua responsabilidade
|##########################################################################
*/

export default function Navbar() {
  const { isAuthenticated, user, logout } = useAuth();
  const location = useLocation();

  /* Lista de navegação ativa se usuário estiver logado */
  const showNav = isAuthenticated;
  const currentPath = location.pathname;

  return (
    <NavbarBS
      bg="primary"
      variant="dark"
      expand="lg"
      sticky="top"
      className="shadow-sm"
    >
      <Container>
        {/* 🔖 Logo / Título */}
        <NavbarBS.Brand as={Link} to={isAuthenticated ? '/dashboard' : '/login'}>
          <strong>📝 Gestor de Tarefas</strong>
        </NavbarBS.Brand>

        <NavbarBS.Toggle aria-controls="nav-main-collapse" />

        <NavbarBS.Collapse id="nav-main-collapse">
          <Nav className="me-auto">
            {showNav && (
              <>
                <Nav.Link
                  as={Link}
                  to="/dashboard"
                  active={currentPath === '/dashboard'}
                >
                  Dashboard
                </Nav.Link>
              </>
            )}
          </Nav>

          <Nav className="align-items-center">
            {isAuthenticated ? (
              <>
                {/* Nome do usuário + email */}
                <span className="text-white me-3 d-inline-flex align-items-center gap-2">
                  <span className="fw-semibold">{user?.name}</span>
                  {user?.email && (
                    <Badge bg="light" text="dark" pill className="fw-normal">
                      {user.email}
                    </Badge>
                  )}
                </span>
                <Button variant="outline-light" size="sm" onClick={logout}>
                  Sair
                </Button>
              </>
            ) : (
              <>
                <Nav.Link
                  as={Link}
                  to="/login"
                  className={currentPath === '/login' ? 'active text-white' : ''}
                >
                  Entrar
                </Nav.Link>
                <Nav.Link
                  as={Link}
                  to="/register"
                  className={currentPath === '/register' ? 'active text-white' : ''}
                >
                  Cadastrar
                </Nav.Link>
              </>
            )}
          </Nav>
        </NavbarBS.Collapse>
      </Container>
    </NavbarBS>
  );
}
