<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Mensagem da TI</title>
    <style>
        body,
        p,
        h1,
        h2 {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: #222;
        }

        body {
            background-color: #f0f3f7;
            padding: 30px 15px;
        }

        .email-container {
            max-width: 600px;
            background: #ffffff;
            margin: 0 auto;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgb(0 0 0 / 0.1);
            padding: 30px 40px;
        }

        .header {
            border-bottom: 2px solid #0078d7;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .header h1 {
            color: #0078d7;
            font-weight: 700;
            font-size: 28px;
        }

        .content p {
            font-size: 16px;
            line-height: 1.5;
            color: #444444;
            margin-bottom: 20px;
        }

        .content p strong {
            color: #0078d7;
        }

        .footer {
            font-size: 13px;
            color: #888888;
            border-top: 1px solid #ddd;
            padding-top: 15px;
            text-align: center;
            margin-top: 30px;
        }

        /* Botão estilizado */
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: #0078d7;
            color: white !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #005ea1;
        }

        /* Responsividade */
        @media (max-width: 640px) {
            .email-container {
                padding: 20px;
            }

            .header h1 {
                font-size: 24px;
            }

            .content p {
                font-size: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <h1>Olá, {{ $dados['nome'] ?? 'Usuário' }}!</h1>
        </div>

        <div class="content">
            <p>Você recebeu uma nova mensagem da <strong>Área de TI</strong> da sua empresa.</p>

            <p><strong>Titulo:</strong> {{ $dados['titulo'] ?? 'Sem Título' }}</p>

            <p><strong>Mensagem:</strong> "{{ $dados['mensagem'] ?? 'Esta é uma mensagem de teste para garantir que seu sistema está funcionando perfeitamente.' }}"</p>

            <p>Se precisar de suporte ou tiver qualquer dúvida, não hesite em nos contatar.</p>

            <p><a href="mailto:ti@gruporialma.com.br" class="btn">Contato com TI</a></p>
        </div>

        <div class="footer">
            <p>Este é um e-mail automático. Por favor, não responda.</p>
            <p>© {{ date('Y') }} Sua Empresa - Departamento de Tecnologia da Informação</p>
        </div>
    </div>
</body>

</html>