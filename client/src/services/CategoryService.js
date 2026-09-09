import apiClient from './apiClient';

/*
| CategoryService: Camada para CRUD de categorias via API.
| Abstrai URLs/HTTP verbs para os componentes React usarem funções semânticas.
*/
class CategoryService {
  async index() {
    const response = await apiClient.get('/categories');
    return response.data.data ?? response.data;
  }

  async store(data) {
    const response = await apiClient.post('/categories', data);
    return response.data.data ?? response.data;
  }

  async show(id) {
    const response = await apiClient.get(`/categories/${id}`);
    return response.data.data ?? response.data;
  }

  async update(id, data) {
    const response = await apiClient.put(`/categories/${id}`, data);
    return response.data.data ?? response.data;
  }

  async destroy(id) {
    const response = await apiClient.delete(`/categories/${id}`);
    return response.data;
  }
}

export default new CategoryService();
