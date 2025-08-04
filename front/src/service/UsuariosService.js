import API_URL from './Config.js';

const token = localStorage.getItem('token');
const id = JSON.parse(localStorage.getItem('usuario'))?.id;

export default class UsuariosService {
    async buscaUsuarios() {
        return await fetch(`${API_URL}/usuarios/busca-usuarios`, {
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

    async resetarSenha(id) {
        return await fetch(`${API_URL}/usuarios/resetar-senha/${id}`, {
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

    async alterarStatusUsuario(id, status) {
        return await fetch(`${API_URL}/usuarios/alterar-status-usuario/${id}/${status}`, {
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

    async criarUsuario(form) {
        return await fetch(`${API_URL}/usuarios/criar-usuario`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json'
            },
            body: JSON.stringify({
                email: form.email,
                name: form.name,
                tipo_usuario: form.tipo_usuario
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

    async alteraSenhaAtual(senha) {
        return await fetch(`${API_URL}/usuarios/altera-senha`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json'
            },
            body: JSON.stringify({
                senha: senha,
                id: id
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
