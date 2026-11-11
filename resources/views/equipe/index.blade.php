@extends('layouts.app')

@section('titre', 'Équipe')
@section('sous-titre', $membres->count().' collaborateur'.($membres->count() > 1 ? 's' : '').($invitations->isNotEmpty() ? ' · '.$invitations->count().' invitation'.($invitations->count() > 1 ? 's' : '').' en attente' : ''))

@section('actions')
    @if ($peutGerer)
        <x-bouton href="{{ route('equipe.inviter') }}" icone="plus">Inviter un employé</x-bouton>
    @endif
@endsection

@section('contenu')
    <div class="grid gap-5">
        @unless ($peutGerer)
            <div class="bandeau bandeau--info">
                <x-icone nom="info" :taille="16" />
                <div class="min-w-0 flex-1">
                    <p class="font-medium">Consultation seule</p>
                    <p class="mt-1 text-[12.5px]" style="color: var(--c-ink-2)">
                        La gestion de l'équipe est réservée aux agences. Demandez le passage en agence
                        pour inviter des collaborateurs et attribuer des rôles.
                    </p>
                </div>
            </div>
        @endunless
        @if (session('lien_invitation'))
            <div class="bandeau bandeau--info">
                <x-icone nom="info" :taille="16" />
                <div class="min-w-0 flex-1">
                    <p class="font-medium">Lien d'activation à transmettre</p>
                    <p class="mt-1 break-all text-[12px]" style="color: var(--c-ink-2)">{{ session('lien_invitation') }}</p>
                </div>
                <button type="button" class="btn btn-petit btn-contour" data-copier="{{ session('lien_invitation') }}">
                    <x-icone nom="copier" :taille="14" />
                    Copier
                </button>
            </div>
        @endif

        @if ($invitations->isNotEmpty())
            <x-carte etiquette="Accès en attente" titre="Invitations en cours" divise>
                <div class="grid gap-2">
                    @foreach ($invitations as $invitation)
                        <div class="ligne-info ligne-invitation">
                            <div class="min-w-0">
                                <p class="titre">{{ $invitation->nom ?: $invitation->email }}</p>
                                <p class="detail">
                                    {{ $invitation->email }} · {{ $invitation->role->label() }} ·
                                    expire le {{ $invitation->expires_at->translatedFormat('j M Y') }}
                                </p>
                            </div>

                            <div class="droite actions-invitation flex items-center gap-2">
                                <x-badge variante="jaune">En attente</x-badge>

                                @if ($peutGerer)
                                    <form method="POST" action="{{ route('equipe.invitation.renvoyer', $invitation) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-petit btn-contour">Renvoyer</button>
                                    </form>

                                    <form method="POST" action="{{ route('equipe.invitation.annuler', $invitation) }}"
                                          data-confirmer="Annuler cette invitation ? Le lien envoyé ne fonctionnera plus.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-petit btn-fantome">Annuler</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-carte>
        @endif

        <x-carte etiquette="Collaborateurs" titre="Membres de l'agence">
            <div class="tableau-scroll">
<table class="tableau">
                <thead>
                    <tr>
                        <th scope="col">Collaborateur</th>
                        <th scope="col">Fonction</th>
                        <th scope="col">Rôle</th>
                        <th scope="col">Statut</th>
                        <th scope="col">Dernière connexion</th>
                        <th scope="col" class="cellule-droite">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($membres as $membre)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="avatar" style="width:34px;height:34px;font-size:12px">{{ $membre->initiales }}</span>
                                    <div class="min-w-0">
                                        <p class="cellule-client">{{ $membre->nom }}@if ($membre->id === auth()->id()) <span class="text-muted">(vous)</span>@endif</p>
                                        <p class="text-[12px] text-muted">{{ $membre->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $membre->poste ?: '—' }}</td>
                            <td>
                                @if ($membre->id === auth()->id() || ! $peutGerer)
                                    <x-badge variante="encre">{{ $membre->role_label }}</x-badge>
                                @else
                                    <form method="POST" action="{{ route('equipe.membre.role', $membre) }}">
                                        @csrf
                                        <select name="role" class="filtre-select" data-envoi-auto
                                                style="height:32px;padding:0 28px 0 10px;border:1px solid var(--c-line-fort);border-radius:999px;background:var(--c-white);font-size:12.5px"
                                                aria-label="Rôle de {{ $membre->nom }}">
                                            @foreach (['administrateur' => 'Administrateur', 'transitaire' => 'Transitaire', 'comptable' => 'Comptable'] as $valeur => $libelle)
                                                <option value="{{ $valeur }}" @selected($membre->role->value === $valeur)>{{ $libelle }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                @endif
                            </td>
                            <td>
                                <x-badge :variante="$membre->estActif() ? 'vert' : null">{{ $membre->statut_activation->label() }}</x-badge>
                            </td>
                            <td class="cellule-compacte text-muted">
                                {{ $membre->derniere_connexion_at?->translatedFormat('j M Y · H:i') ?? 'Jamais' }}
                            </td>
                            <td class="cellule-droite">
                                @if ($membre->id !== auth()->id() && $peutGerer)
                                    <form method="POST" action="{{ route('equipe.membre.statut', $membre) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-petit {{ $membre->estActif() ? 'btn-fantome' : 'btn-contour' }}">
                                            {{ $membre->estActif() ? 'Désactiver' : 'Réactiver' }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[12px] text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
</div>
        </x-carte>

        <p class="text-center text-[12.5px] text-muted">
            Les rôles déterminent les écrans accessibles ; les permissions fines se règlent à l'invitation.
        </p>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-envoi-auto]').forEach((champ) => {
            champ.addEventListener('change', () => champ.form.requestSubmit());
        });
    </script>
@endpush
