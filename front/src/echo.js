// echo.js
import Echo from 'laravel-echo';

// Configura o Echo para usar Reverb
window.Reverb = new Echo({
    broadcaster: 'reverb',
    key: 'APP_KEY_DO_SEU_REVERB', // Substitua pela key real
    host: window.location.hostname, // ou o IP/URL do servidor
    port: 6001, // porta padrão do Reverb
    forceTLS: false, // true se estiver usando HTTPS/WSS
    encrypted: false,
    disableStats: true,
    authEndpoint: '/broadcasting/auth' 
});
