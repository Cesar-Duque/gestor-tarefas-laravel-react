import React from 'react';
import Alert from 'react-bootstrap/Alert';
import Button from 'react-bootstrap/Button';

/*
|##########################################################################
| # Componente reutilizável: Alertas de feedback global                   #
|##########################################################################
|
| Separa a responsabilidade de mostrar mensagens de sucesso/erro/warning
| em um único componente. Recebe via props:
|   variant: 'success' | 'danger' | 'warning' | 'info' (cor Bootstrap)
|   message: texto da mensagem
|   onClose: callback para quando usuário clicar no "X"
|
| Vantagem: você não precisa duplicar código de Alert em toda página.
*/
export default function FeedbackAlert({ variant, message, onClose }) {
  if (!message) return null;
  return (
    <Alert variant={variant || 'info'} dismissible onClose={onClose} className="mb-3">
      <div className="d-flex justify-content-between align-items-center">
        <span className="small">{message}</span>
        <Button
          size="sm"
          variant="link"
          className="text-decoration-none p-0"
          onClick={onClose}
          aria-label="Fechar alerta"
        >
          ×
        </Button>
      </div>
    </Alert>
  );
}
