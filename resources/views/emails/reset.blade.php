<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Redefinição de Senha — ForgeAction</title>
    <style>
        body, table, td, a {
            font-family: 'MedievalSharp', Georgia, serif;
            color: #f8f9fa;
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#1a1a1a;">

    <table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#1a1a1a">
        <tr>
            <td align="center">
                <table border="0" cellpadding="20" cellspacing="0" width="600" style="background-color:#222; border:2px solid #6f42c1; border-radius:15px;">
                    <tr>
                        <td align="center" style="text-align:center;">

                        <!-- Logo -->
                        <img src="{{ asset('assets/images/forgeicon.png') }}" alt="ForgeAction" width="80" style="display:block; margin-bottom:15px;">

                        <!-- Título -->
                        <h1 style="font-family:'MedievalSharp', Georgia, serif; color:#d4af37; font-size:28px; margin:0 0 15px;">
                            Redefinição de Senha
                        </h1>

                        <!-- Texto -->
                        <p style="font-size:16px; line-height:1.5; margin:0 0 10px;">
                            Saudações, aventureiro(a)!
                        </p>

                        <p style="font-size:16px; line-height:1.5; margin:0 0 10px;">
                            Um pedido de <strong>redefinição de senha</strong> foi invocado em sua conta do <strong>ForgeAction</strong>.
                        </p>

                        <p style="font-size:16px; line-height:1.5; margin:0 0 20px;">
                            Se foi você quem fez a solicitação, clique no botão abaixo para forjar uma nova senha.<br>
                            Este link expira em breve — o tempo é essencial!
                        </p>

                        <!-- Botão -->
                        <a href="{{ $resetLink }}" style="display:inline-block; background-color:#6f42c1; color:#fff; font-weight:bold; font-size:16px; text-decoration:none; padding:12px 25px; border-radius:10px; margin-bottom:10px;">
                            Redefinir Senha
                        </a>

                        <!-- Aviso -->
                        <p style="font-size:14px; color:#bbb; line-height:1.5; margin-top:20px;">
                            Se você <strong>não solicitou</strong> essa redefinição, ignore este e-mail com tranquilidade.<br>
                            Nenhuma mudança será feita na sua conta.
                        </p>

                        <!-- Rodapé -->
                        <p style="font-size:12px; color:#aaa; margin-top:25px;">
                            ForgeAction ⚒️ &copy; {{ date('Y') }}<br>
                            Forje seu destino. Domine a aventura.
                        </p>

                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
