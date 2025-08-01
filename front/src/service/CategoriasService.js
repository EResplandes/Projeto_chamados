import API_URL from './Config.js';

const token = localStorage.getItem('token');

export default class CategoriasService {
    async bucaCategorias() {
        return await fetch(`${API_URL}/categorias/busca-categorias`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                Authorization: `Bearer ${token}`
            }
        })
            .then((res) => res.json())
            .then((d) => {
                return d;
            })
            .catch((error) => {
                console.error('Error:', error);
                throw error;
            });
    }
}
