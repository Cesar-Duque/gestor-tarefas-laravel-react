import apiClient from './apiClient';

/*
| TaskService: Camada de acesso à API de Tarefas.
|
| Métodos especiais:
|   index(params)  → aceita objeto com filtros/paginação e transforma
|                    em query string (?page=2&status=pendente&...)
|   toggleComplete(id) → PATCH /api/tasks/{id}/toggle
*/
class TaskService {
  async index(params) {
    const response = await apiClient.get('/tasks', { params });
    return response.data;
  }

  async store(data) {
    const response = await apiClient.post('/tasks', data);
    return response.data.data ?? response.data;
  }

  async show(id) {
    const response = await apiClient.get(`/tasks/${id}`);
    return response.data.data ?? response.data;
  }

  async update(id, data) {
    const response = await apiClient.put(`/tasks/${id}`, data);
    return response.data.data ?? response.data;
  }

  async destroy(id) {
    const response = await apiClient.delete(`/tasks/${id}`);
    return response.data;
  }

  /**
   * Marca/desmarca tarefa como concluída sem enviar todo o formulário.
   * Rota extra da API: PATCH /api/tasks/{task}/toggle
   */
  async toggleComplete(id) {
    const response = await apiClient.patch(`/tasks/${id}/toggle`);
    return response.data.data ?? response.data;
  }
}

export default new TaskService();
