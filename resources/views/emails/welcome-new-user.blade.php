<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue sur Les Hameçonnés</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2563eb;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
        .credentials {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #2563eb;
        }
        .footer {
            margin-top: 30px;
            font-size: 0.9em;
            color: #6c757d;
            text-align: center;
        }
        .button {
            display: inline-block;
            background-color: #2563eb;
            color: white !important;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            font-family: Arial, sans-serif;
            font-weight: bold;
            font-size: 16px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Bienvenue sur Les Hameçonnés</h1>
    </div>

    <div class="content">
        <p>Bonjour {{ $user->name }},</p>

        <p>Nous sommes ravis de vous accueillir sur la plateforme <strong>Les Hameçonnés</strong>, votre nouvel espace dédié à la pêche en Bretagne.</p>

        <p>Un compte a été créé pour vous avec les identifiants suivants :</p>

        <div class="credentials">
            <p><strong>Email :</strong> {{ $user->email }}</p>
            <p><strong>Mot de passe :</strong> {{ $plainPassword }}</p>
        </div>

        <p>Nous vous recommandons de changer votre mot de passe lors de votre première connexion.</p>

        <p>Vous pouvez dès maintenant vous connecter à votre compte en cliquant sur le bouton ci-dessous :</p>

        <div style="text-align: center;">
            <a href="https://hameconnes.guillaume-rv.fr/login" class="button">Se connecter</a>
        </div>

        <p style="text-align: center; margin-top: 10px; font-size: 12px; color: #6c757d;">
            Si vous êtes en environnement local, utilisez : <a href="http://localhost/login" style="color: #2563eb;">http://localhost/login</a>
        </p>

        <p>Sur notre plateforme, vous pourrez :</p>
        <ul>
            <li>Découvrir des spots de pêche en Bretagne</li>
            <li>Partager vos propres spots de pêche</li>
            <li>Enregistrer vos prises</li>
            <li>Échanger avec d'autres passionnés de pêche</li>
        </ul>

        <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p>

        <p>Bonne pêche !</p>

        <p>Cordialement,<br>
        L'équipe des Hameçonnés</p>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} Les Hameçonnés - Tous droits réservés</p>
        <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
    </div>
</body>
</html>
