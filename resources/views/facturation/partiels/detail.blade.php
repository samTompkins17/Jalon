@php
    $couleur = match ($facture->statut->value) {
        'payee' => 'vert',
        'partielle' => 'jaune',
        default => 'rose',
    };
@endphp

<div class="flex flex-wrap items-start justify-between gap-3">
    <p class="etiquette">
        Dossier :
        @if ($facture->dossier)
            <a href="{{ route('dossiers.show', $facture->dossier) }}" style="color: var(--c-brand)">{{ $facture->dossier->reference }}</a>
        @else
            —
        @endif
    </p>

    <x-badge :variante="$couleur">{{ $facture->statut->label() }}</x-badge>
</div>

<h2 class="mt-2 text-[21px] font-semibold" style="letter-spacing:-0.02em">Facture {{ $facture->numero }}</h2>
<p class="text-[12.5px] text-muted">{{ $facture->client?->nom }}</p>

<div class="mt-4">
    @foreach ($facture->lignes as $ligne)
        <div class="ligne-info">
            <span class="cle">{{ $ligne->designation }}</span>
            <span class="valeur">{{ \App\Support\Format::nombre($ligne->montant) }} <span class="text-muted">FCFA</span></span>
        </div>
    @endforeach
</div>

<div class="mt-4 border-t pt-3" style="border-color: var(--c-line-soft)">
    <div class="ligne-info">
        <span class="cle">Total HT (FCFA)</span>
        <span class="valeur">{{ \App\Support\Format::nombre($facture->sous_total) }} <span class="text-muted">FCFA</span></span>
    </div>

    @if ((float) $facture->remise > 0)
        <div class="ligne-info">
            <span class="cle">Remise</span>
            <span class="valeur">− {{ \App\Support\Format::nombre($facture->remise) }} <span class="text-muted">FCFA</span></span>
        </div>
    @endif

    <div class="ligne-info">
        <span class="cle">TVA ({{ rtrim(rtrim(number_format((float) $facture->tva_taux, 2, ',', ''), '0'), ',') }} %)</span>
        <span class="valeur">{{ \App\Support\Format::nombre($facture->tva_montant) }} <span class="text-muted">FCFA</span></span>
    </div>

    <div class="ligne-info" style="margin-top:8px;padding:12px 14px;border:0;border-radius:10px;background: var(--c-surface-2)">
        <span class="cle" style="color:var(--c-ink);font-weight:600">Total TTC</span>
        <span class="valeur" style="font-size:19px;color:var(--c-brand)">
            {{ \App\Support\Format::nombre($facture->total) }} <span class="text-muted" style="font-size:12px">FCFA</span>
        </span>
    </div>

    <div class="ligne-info">
        <span class="cle">Déjà réglé</span>
        <span class="valeur" style="color: var(--c-vert)">− {{ \App\Support\Format::nombre($facture->montant_paye) }} <span class="text-muted">FCFA</span></span>
    </div>

    <div class="ligne-info">
        <span class="cle" style="color: var(--c-rose);font-weight:600">Reste à payer</span>
        <span class="valeur" style="color: var(--c-rose)">{{ \App\Support\Format::nombre($facture->solde) }} <span class="text-muted">FCFA</span></span>
    </div>
</div>

<div class="mt-4 flex items-start gap-2 rounded-[10px] p-3" style="background: var(--c-surface-2)">
    <x-icone nom="info" :taille="15" />
    <p class="text-[12px] text-muted">
        <strong style="color: var(--c-ink)">Marge du dossier</strong> = HT facturé moins coûts réels engagés
        (débours et prestations).
    </p>
</div>

<div class="mt-4 grid gap-2">
    @if ($facture->solde > 0)
        <x-bouton type="button" bloc data-modale-ouvrir="#modale-paiement">Enregistrer un paiement</x-bouton>
    @endif

    <x-bouton href="{{ route('facturation.imprimable', $facture) }}" variante="contour" icone="telecharger" bloc>
        Télécharger le PDF
    </x-bouton>

    <a href="{{ route('facturation.show', $facture) }}" class="lien-action text-center mt-1">Ouvrir la facture →</a>
</div>
