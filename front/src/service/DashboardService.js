import API_URL from './Config.js';

const token = localStorage.getItem('token');

export default class DashboardService {
    async buscaIndicadoresGereais() {
        return await fetch(`${API_URL}/dashboard/indicadores-gerais`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json'
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
