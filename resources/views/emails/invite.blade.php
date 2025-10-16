<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Convite para Sala</title>
<style>
  /* fallback de fonte para email */
  body, table, td, a {
    font-family: 'MedievalSharp', Georgia, serif;
    color: #f8f9fa;
  }
</style>
</head>
<body style="margin:0; padding:0; background-color:#1a1a1a;">

    <!-- Wrapper table -->
    <table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#1a1a1a">
        <tr>
            <td align="center">
                <!-- Card table -->
                <table border="0" cellpadding="20" cellspacing="0" width="600" style="background-color:#222; border:2px solid #6f42c1; border-radius:15px;">
                    <tr>
                        <td align="center" style="text-align:center;">
                            <!-- Logo -->
                            <img src="{{ asset('assets/images/forgeicon.png') }}" alt="ForgeAction" width="80" style="display:block; margin-bottom:15px;">
                            <!-- Title -->
                            <h1 style="font-family:'MedievalSharp', Georgia, serif; color:#d4af37; font-size:28px; margin:0 0 15px;">Convite à Aventura</h1>

                            <!-- Content -->
                            <p style="font-size:16px; line-height:1.5; margin:0 0 10px;">
                                <strong>{{ $remetente }}</strong> está te convidando para se juntar à aventura:
                            </p>
                            <h2 style="font-family:'MedievalSharp', Georgia, serif; color:#6f42c1; font-size:22px; margin:0 0 15px;">{{ $sala }}</h2>
                            <p style="font-size:16px; line-height:1.5; margin:0 0 20px;">
                                Prepare-se para explorar, lutar e se divertir ao lado de outros jogadores. Clique no botão abaixo para aceitar o convite e entrar na sala.
                            </p>

                            <!-- Button -->
                            <a href="{{ $link }}" style="display:inline-block; background-color:#6f42c1; color:#fff; font-weight:bold; font-size:16px; text-decoration:none; padding:12px 25px; border-radius:10px; margin-bottom:10px;">
                                Aceitar Convite
                            </a>

                            <!-- Footer -->
                            <p style="font-size:12px; color:#aaa; margin-top:20px;">
                                ForgeAction &copy; {{ date('Y') }}
                            </p>
                        </td>
                    </tr>
                </table>
            <!-- End Card -->
            </td>
        </tr>
    </table>
    <!-- End Wrapper -->

</body>
</html>
