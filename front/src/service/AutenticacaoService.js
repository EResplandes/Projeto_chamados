import API_URL from './Config.js';

const token = localStorage.getItem('token');

export default class AutenticacaoService {
    async logar(form) {
        return await fetch(`${API_URL}/autenticacao/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json'
            },
            body: JSON.stringify({
                email: form.email,
                password: form.senha
            })
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
