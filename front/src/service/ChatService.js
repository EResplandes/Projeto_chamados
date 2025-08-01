import API_URL from './Config.js';

const token = localStorage.getItem('token');
const usuario_id = JSON.parse(localStorage.getItem('usuario'))?.id;

export default class ChatService {
    async buscaChat(id) {
        return await fetch(`${API_URL}/chat/busca-mensagens` + `/${id}`, {
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

    async enviarMensagem(mensagem, chamado_id) {
        return await fetch(`${API_URL}/chat/enviar-mensagem`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json'
            },
            body: JSON.stringify({
                mensagem: mensagem,
                chamado_id: chamado_id,
                usuario_id: usuario_id
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

    async enviarAnexo(imagem, chamado_id) {
        const formData = new FormData();
        formData.append('anexo', imagem ?? null);
        formData.append('usuario_id', usuario_id);
        formData.append('chamado_id', chamado_id);

        const headers = {
            Authorization: `Bearer ${token}`
        };

        return fetch(`${API_URL}/chat/enviar-anexo`, {
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
}
