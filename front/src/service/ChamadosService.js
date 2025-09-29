import API_URL from './Config.js';

const token = localStorage.getItem('token');

const id = JSON.parse(localStorage.getItem('usuario'))?.id;

export default class ChamadosService {
    async buscaChamados() {
        return await fetch(`${API_URL}/chamados/busca-chamados/${id}`, {
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

    async indicadoresUsuario() {
        return await fetch(`${API_URL}/chamados/indicadores-usuario/${id}`, {
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

    async abrirChamado(form) {
        const formData = new FormData();
        formData.append('titulo', form?.titulo ?? null);
        formData.append('descricao', form?.descricao ?? null);
        formData.append('urgencia', form?.urgencia.label ?? null);
        formData.append('categoria_id', form?.categoria.id ?? null);
        formData.append('solicitante_id', id);
        formData.append('anexo', form?.pdf ?? null);

        const headers = {
            Authorization: `Bearer ${token}`
        };

        return fetch(`${API_URL}/chamados/abrir-chamado`, {
            method: 'POST',
            headers: headers,
            body: formData
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

    async indicadoresAdmin() {
        return await fetch(`${API_URL}/chamados/admin/indicadores-admin/${id}`, {
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

    async buscaChamadosAdmin() {
        return await fetch(`${API_URL}/chamados/admin/busca-chamados/${id}`, {
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

    async assumeChamado(id, idUsuario) {
        return await fetch(`${API_URL}/chamados/admin/assume-chamado/${id}/${idUsuario}`, {
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

    async alterarStatusChamado(id, idStatus) {
        return await fetch(`${API_URL}/chamados/admin/altera-status-chamado/${id}/${idStatus}`, {
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

    async alterarTecnicoChamado(id, idStatus) {
        return await fetch(`${API_URL}/chamados/admin/altera-tecnico-chamado/${id}/${idStatus}`, {
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

    async buscaAnexo(id) {
        return await fetch(`${API_URL}/chamados/admin/busca-anexo/${id}`, {
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
