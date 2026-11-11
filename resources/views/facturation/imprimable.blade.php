<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Facture {{ $facture->numero }} · {{ $facture->agence?->nom ?? 'Jalon' }}</title>

    <style>
        @page { margin: 18mm; }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 32px;
            font-family: "Instrument Sans", -apple-system, "Segoe UI", sans-serif;
            color: #1a1917;
            background: #eeeae7;
            font-size: 13px;
        }

        .feuille {
            max-width: 780px;
            margin: 0 auto;
            background: #fff;
            padding: 40px 44px;
            border-radius: 14px;
            box-shadow: 0 4px 14px rgba(60, 52, 44, .1);
        }

        .entete { display: flex; justify-content: space-between; gap: 24px; align-items: flex-start; }

        .marque { font-size: 22px; font-weight: 700; letter-spacing: -.02em; color: #c9460e; }
        .agence { margin-top: 6px; font-size: 12px; color: #6f6862; line-height: 1.5; }

        .facture-numero { text-align: right; }
        .facture-numero h1 { margin: 0; font-size: 22px; letter-spacing: -.02em; }
        .facture-numero p { margin: 4px 0 0; font-size: 12px; color: #6f6862; }

        .parties { display: flex; justify-content: space-between; gap: 30px; margin-top: 30px; }
        .parties h2 { margin: 0 0 6px; font-size: 11px; text-transform: uppercase; letter-spacing: .08em; color: #6f6862; }
        .parties p { margin: 0; line-height: 1.55; }

        table { width: 100%; border-collapse: collapse; margin-top: 28px; }
        th { text-align: left; font-size: 10.5px; text-transform: uppercase; letter-spacing: .07em; color: #6f6862; padding: 0 0 8px; border-bottom: 1px solid #e0dad3; }
        td { padding: 10px 0; border-bottom: 1px solid #f0ebe6; }
        th.droite, td.droite { text-align: right; }

        .totaux { margin-top: 18px; margin-left: auto; width: 280px; }
        .totaux div { display: flex; justify-content: space-between; padding: 6px 0; }
        .totaux .ttc { margin-top: 6px; padding-top: 10px; border-top: 1px solid #e0dad3; font-size: 17px; font-weight: 700; color: #c9460e; }
        .totaux .regle { color: #17804a; }
        .totaux .reste { color: #c0213f; font-weight: 600; }

        .pied { margin-top: 36px; padding-top: 14px; border-top: 1px solid #e0dad3; font-size: 11px; color: #6f6862; line-height: 1.6; }

        .barre { max-width: 780px; margin: 0 auto 14px; display: flex; justify-content: flex-end; gap: 10px; }

        .bouton {
            border: 0; border-radius: 8px; padding: 10px 16px; font-size: 13px; font-weight: 600;
            background: #c9460e; color: #fff; cursor: pointer; text-decoration: none;
        }

        .bouton.secondaire { background: #fff; color: #1a1917; border: 1px solid #d5cec6; }

        @media print {
            body { background: #fff; padding: 0; }
            .feuille { box-shadow: none; border-radius: 0; padding: 0; max-width: none; }
            .barre { display: none; }
        }
    </style>
</head>
<body>
    <div class="barre">
        <a href="{{ route('facturation.show', $facture) }}" class="bouton secondaire">Retour</a>
        <button type="button" class="bouton" onclick="window.print()">Imprimer / Enregistrer en PDF</button>
    </div>

    <div class="feuille">
        <div class="entete">
            <div>
                <p class="marque">{{ $facture->agence?->nom ?? 'Jalon' }}</p>
                <p class="agence">
                    Transit et douane · Port de Lomé (Togo)<br>
                    @if ($facture->agence?->numero_agrement)
                        Agrément {{ $facture->agence->numero_agrement }}<br>
                    @endif
                    {{ $facture->agence?->email }} @if ($facture->agence?->telephone) · {{ $facture->agence->telephone }} @endif
                </p>
            </div>

            <div class="facture-numero">
                <h1>Facture {{ $facture->numero }}</h1>
                <p>
                    Émise le {{ $facture->date_facture?->translatedFormat('j F Y') }}<br>
                    Échéance {{ $facture->date_echeance?->translatedFormat('j F Y') }}
                </p>
            </div>
        </div>

        <div class="parties">
            <div>
                <h2>Facturé à</h2>
                <p>
                    <strong>{{ $facture->client?->nom }}</strong><br>
                    @if ($facture->client?->contact_nom) {{ $facture->client->contact_nom }}<br> @endif
                    @if ($facture->client?->adresse) {{ $facture->client->adresse }}<br> @endif
                    {{ $facture->client?->ville }} @if ($facture->client?->pays) ({{ $facture->client->pays }}) @endif
                    @if ($facture->client?->email) <br>{{ $facture->client->email }} @endif
                </p>
            </div>

            <div>
                <h2>Dossier</h2>
                <p>
                    @if ($facture->dossier)
                        <strong>{{ $facture->dossier->reference }}</strong><br>
                        {{ $facture->dossier->regime?->nom }}<br>
                        {{ $facture->dossier->nature_marchandise }}
                    @else
                        —
                    @endif
                </p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th class="droite">Qté</th>
                    <th class="droite">Prix unitaire</th>
                    <th class="droite">Montant (FCFA)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($facture->lignes as $ligne)
                    <tr>
                        <td>{{ $ligne->designation }}</td>
                        <td class="droite">{{ rtrim(rtrim(number_format((float) $ligne->quantite, 2, ',', ' '), '0'), ',') }}</td>
                        <td class="droite">{{ \App\Support\Format::nombre($ligne->prix_unitaire) }}</td>
                        <td class="droite">{{ \App\Support\Format::nombre($ligne->montant) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totaux">
            <div><span>Sous-total HT</span><span>{{ \App\Support\Format::nombre($facture->sous_total) }} FCFA</span></div>

            @if ((float) $facture->remise > 0)
                <div><span>Remise</span><span>− {{ \App\Support\Format::nombre($facture->remise) }} FCFA</span></div>
            @endif

            <div><span>TVA ({{ rtrim(rtrim(number_format((float) $facture->tva_taux, 2, ',', ''), '0'), ',') }} %)</span><span>{{ \App\Support\Format::nombre($facture->tva_montant) }} FCFA</span></div>
            <div class="ttc"><span>Total TTC</span><span>{{ \App\Support\Format::nombre($facture->total) }} FCFA</span></div>

            <div class="regle"><span>Déjà réglé</span><span>− {{ \App\Support\Format::nombre($facture->montant_paye) }} FCFA</span></div>
            <div class="reste"><span>Reste à payer</span><span>{{ \App\Support\Format::nombre($facture->solde) }} FCFA</span></div>
        </div>

        <div class="pied">
            <p>
                Règlement par virement, chèque ou Mobile Money au nom de {{ $facture->agence?->nom ?? 'Jalon' }}.
                Merci d'indiquer la référence {{ $facture->numero }} sur chaque paiement.
            </p>

            @if ($facture->notes)
                <p>{{ $facture->notes }}</p>
            @endif

            <p>Document généré par Jalon · {{ now()->translatedFormat('j F Y à H:i') }}</p>
        </div>
    </div>
</body>
</html>
