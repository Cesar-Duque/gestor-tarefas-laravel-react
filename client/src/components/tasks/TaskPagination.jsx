import React from 'react';
import Pagination from 'react-bootstrap/Pagination';

/*
|##########################################################################
| # TaskPagination — Barra de paginação server-side                       #
|##########################################################################
|
| O backend retorna os metadados de paginação no objeto `meta` da
| TaskCollection (ver backend TaskCollection.php):
|   {
|     current_page, last_page, total, per_page,
|     has_next_page, has_prev_page, from, to
|   }
|
| Aqui apenas renderizamos botões de acordo com esses metadados.
| Quando o usuário clica em uma página, chamamos onPageChange(pageNumber)
| que vai ACIONAR O COMPONENTE PAI para refazer o fetch com ?page=X.
|
| Regras UX:
|   - Mostrar primeira, anterior, N páginas centrais, próxima, última
|   - Desabilitar "primeira/anterior" se estiver na página 1
|   - Desabilitar "próxima/última" se estiver na última página
|##########################################################################
*/
export default function TaskPagination({ meta, onPageChange }) {
  if (!meta || meta.total === 0 || meta.last_page <= 1) return null;

  /* Gera array de páginas visíveis (janela de no máximo 7 páginas em torno da atual) */
  const paginas = [];
  const maxVisiveis = 7;
  let inicio = Math.max(1, meta.current_page - Math.floor(maxVisiveis / 2));
  const fim = Math.min(meta.last_page, inicio + maxVisiveis - 1);
  if (fim - inicio < maxVisiveis - 1) {
    inicio = Math.max(1, fim - maxVisiveis + 1);
  }
  for (let i = inicio; i <= fim; i += 1) paginas.push(i);

  const total = meta.total ?? 0;
  const de = meta.from ?? 0;
  const ate = meta.to ?? 0;

  return (
    <div className="d-flex flex-column flex-sm-row justify-content-between align-items-center mt-4 gap-3">
      <div className="text-muted small">
        Mostrando <strong>{de}</strong> a <strong>{ate}</strong> de{' '}
        <strong>{total}</strong> tarefa{total === 1 ? '' : 's'}.
      </div>

      <Pagination className="mb-0">
        <Pagination.First
          disabled={!meta.has_prev_page}
          onClick={() => onPageChange(1)}
        />
        <Pagination.Prev
          disabled={!meta.has_prev_page}
          onClick={() => onPageChange(meta.current_page - 1)}
        />
        {paginas.map((p) => (
          <Pagination.Item
            key={p}
            active={p === meta.current_page}
            onClick={() => onPageChange(p)}
          >
            {p}
          </Pagination.Item>
        ))}
        <Pagination.Next
          disabled={!meta.has_next_page}
          onClick={() => onPageChange(meta.current_page + 1)}
        />
        <Pagination.Last
          disabled={!meta.has_next_page}
          onClick={() => onPageChange(meta.last_page)}
        />
      </Pagination>
    </div>
  );
}
