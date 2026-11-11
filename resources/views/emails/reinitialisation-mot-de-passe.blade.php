<!DOCTYPE html>
<html lang="fr">
<body style="margin:0;padding:32px 16px;background:#eeeae7;font-family:Helvetica,Arial,sans-serif;color:#1a1917;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;background:#f3efeb;border:1px solid #e0dad3;border-radius:16px;">
        <tr>
            <td style="padding:32px 32px 8px;">
                <p style="margin:0;font-size:13px;letter-spacing:.16em;text-transform:uppercase;color:#6f6862;">Jalon</p>
                <h1 style="margin:10px 0 0;font-size:26px;line-height:1.25;">Réinitialisation du mot de passe</h1>
            </td>
        </tr>
        <tr>
            <td style="padding:8px 32px 0;font-size:15px;line-height:1.6;">
                <p style="margin:16px 0;">Bonjour {{ $utilisateur->nom }},</p>
                <p style="margin:16px 0;">
                    Vous avez demandé à réinitialiser le mot de passe de votre compte
                    ({{ $utilisateur->email }}). Ce lien expire dans 60 minutes.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding:8px 32px 0;">
                <a href="{{ $lien }}"
                   style="display:inline-block;padding:14px 24px;border-radius:999px;background:#c9460e;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;">
                    Choisir un nouveau mot de passe
                </a>
            </td>
        </tr>
        <tr>
            <td style="padding:24px 32px 32px;font-size:13px;line-height:1.6;color:#6f6862;">
                <p style="margin:0 0 10px;">Si le bouton ne fonctionne pas, copiez ce lien :</p>
                <p style="margin:0 0 18px;word-break:break-all;color:#3b3733;">{{ $lien }}</p>
                <p style="margin:0;">Si vous n'êtes pas à l'origine de cette demande, aucune action n'est nécessaire.</p>
            </td>
        </tr>
    </table>
</body>
</html>
