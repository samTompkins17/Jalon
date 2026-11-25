@extends('layouts.plateforme')

@section('titre', 'Administration plateforme')

@section('contenu')
    @php
        $initiales = fn (?string $nom): string => \App\Support\Format::initiales((string) $nom);
        $totalAgences = $agences->total();
    @endphp

    <div class="grid gap-6">
        @if (session('lien_activation'))
            <div class="bandeau bandeau--info">
                <x-icone nom="info" :taille="16" class="mt-0.5" />
                <div class="min-w-0 flex-1">
                    <p class="font-medium">Lien d’activation à transmettre</p>
                    <p class="mt-1 break-all text-xs">{{ session('lien_activation') }}</p>
                </div>
                <button type="button" class="btn btn-petit btn-contour" data-copier="{{ session('lien_activation') }}">
                    <x-icone nom="copier" :taille="15" />
                    Copier
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="bandeau bandeau--alerte">
                <x-icone nom="attention" :taille="16" class="mt-0.5" />
                <p>{{ $errors->first() }}</p>
            </div>
        @endif

        {{-- Vue d’ensemble ------------------------------------------------ --}}
        <section class="console-hero" aria-labelledby="console-titre">
            <div class="console-hero-corps">
                <p class="console-hero-etiquette">
                    <span class="console-point" aria-hidden="true"></span>
                    Supervision Jalon
                </p>
                <h2 id="console-titre" class="console-hero-titre">La plateforme sous contrôle</h2>
                <p class="console-hero-texte">
                    Validez les nouvelles agences, ouvrez ou coupez leur accès en un clic.
                    {{ $totalAgences }} agence{{ $totalAgences > 1 ? 's' : '' }} enregistrée{{ $totalAgences > 1 ? 's' : '' }},
                    {{ $agencesActives }} en activité sur le réseau.
                </p>
            </div>

            <div class="console-hero-file">
                <div>
                    <p class="nombre">{{ $demandesEnAttente }}</p>
                    <p class="libelle">demande{{ $demandesEnAttente > 1 ? 's' : '' }} d’accès<br>en attente de décision</p>
                </div>
                <a href="#demandes" class="lien">
                    Traiter
                    <x-icone nom="chevron-droit" :taille="14" />
                </a>
            </div>
        </section>

        {{-- Indicateurs --------------------------------------------------- --}}
        <section class="console-tuiles" aria-label="Indicateurs plateforme">
            <article class="console-tuile">
                <span class="picto picto--vert"><x-icone nom="check" :taille="18" /></span>
                <div class="min-w-0">
                    <p class="valeur">{{ $agencesActives }}</p>
                    <p class="libelle">Agences actives</p>
                    <p class="detail">Accès opérationnel</p>
                </div>
            </article>

            <article class="console-tuile">
                <span class="picto picto--rose"><x-icone nom="fermer" :taille="18" /></span>
                <div class="min-w-0">
                    <p class="valeur">{{ $agencesSuspendues }}</p>
                    <p class="libelle">Agences suspendues</p>
                    <p class="detail">Connexion bloquée</p>
                </div>
            </article>

            <article class="console-tuile">
                <span class="picto"><x-icone nom="equipe" :taille="18" /></span>
                <div class="min-w-0">
                    <p class="valeur">{{ $totalAgences }}</p>
                    <p class="libelle">Agences enregistrées</p>
                    <p class="detail">Depuis l’ouverture du service</p>
                </div>
            </article>
        </section>

        {{-- Demandes d’agence --------------------------------------------- --}}
        <section id="demandes" class="grid gap-4" aria-labelledby="demandes-titre">
            <div class="console-section-titre">
                <div>
                    <p class="etiquette">Accès plateforme</p>
                    <h2 id="demandes-titre" class="carte-titre">Demandes d’agence</h2>
                    <p class="carte-sous-titre">
                        Approuver une demande crée l’agence, son administrateur et l’invitation par e-mail.
                    </p>
                </div>
                <span class="console-pastille">
                    <x-icone nom="horloge" :taille="13" />
                    {{ $demandesEnAttente }} en attente
                </span>
            </div>

            {{-- Onglets ------------------------------------------------- --}}
            <div class="segmented w-fit" role="group" aria-label="Historique des demandes">
                <a href="{{ route('plateforme.index', ['onglet' => 'attente']) }}"
                   @if ($onglet === 'attente') aria-current="page" @endif>
                    En attente
                </a>
                <a href="{{ route('plateforme.index', ['onglet' => 'traitees']) }}"
                   @if ($onglet === 'traitees') aria-current="page" @endif>
                    Traitées
                </a>
            </div>

            @if ($onglet === 'traitees')
                {{-- Liste des demandes traitées ------------------------------- --}}
                @forelse ($demandes as $demande)
                    <article class="console-demande">
                        <div class="console-demande-tete">
                            <span class="console-monogramme" aria-hidden="true">{{ $initiales($demande->nom_agence) }}</span>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <h3 class="text-[15px] font-semibold leading-tight">{{ $demande->nom_agence }}</h3>
                                    <x-badge :variante="$demande->statut === 'approuvee' ? 'vert' : 'rose'" petit>
                                        {{ $demande->statut === 'approuvee' ? 'Approuvée' : 'Refusée' }}
                                    </x-badge>
                                </div>

                                <p class="mt-1 text-[13px] text-ink-2">{{ $demande->nom_contact }}</p>

                                <div class="console-demande-meta">
                                    <span>
                                        <x-icone nom="document" :taille="13" />
                                        <a href="mailto:{{ $demande->email }}">{{ $demande->email }}</a>
                                    </span>

                                    @if ($demande->telephone)
                                        <span>
                                            <x-icone nom="utilisateur" :taille="13" />
                                            {{ $demande->telephone }}
                                        </span>
                                    @endif

                                    @if ($demande->motif_refus)
                                        <span>
                                            <x-icone nom="attention" :taille="13" />
                                            Motif : {{ $demande->motif_refus }}
                                        </span>
                                    @endif

                                    @if ($demande->administrateur)
                                        <span>
                                            <x-icone nom="equipe" :taille="13" />
                                            Traité par {{ $demande->administrateur->nom }}
                                        </span>
                                    @endif

                                    <span>
                                        <x-icone nom="calendrier" :taille="13" />
                                        Traité le {{ $demande->traitee_at?->translatedFormat('j M Y à H:i') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="carte">
                        <x-etat-vide icone="equipe" titre="Aucune demande traitée"
                                     texte="Les demandes approuvées ou refusées apparaîtront ici." />
                    </div>
                @endforelse

                <x-pagination :paginateur="$demandes" />
            @else
                {{-- Liste des demandes en attente (existante) --------------- --}}
                @forelse ($demandes as $demande)
                    <article class="console-demande">
                        <div class="console-demande-tete">
                            <span class="console-monogramme" aria-hidden="true">{{ $initiales($demande->nom_agence) }}</span>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <h3 class="text-[15px] font-semibold leading-tight">{{ $demande->nom_agence }}</h3>
                                    <x-badge variante="ambre" petit>Nouvelle</x-badge>
                                </div>

                                <p class="mt-1 text-[13px] text-ink-2">{{ $demande->nom_contact }}</p>

                                <div class="console-demande-meta">
                                    <span>
                                        <x-icone nom="document" :taille="13" />
                                        <a href="mailto:{{ $demande->email }}">{{ $demande->email }}</a>
                                    </span>

                                    @if ($demande->telephone)
                                        <span>
                                            <x-icone nom="utilisateur" :taille="13" />
                                            {{ $demande->telephone }}
                                        </span>
                                    @endif

                                    @if ($demande->numero_agrement)
                                        <span>
                                            <x-icone nom="check" :taille="13" />
                                            Agrément {{ $demande->numero_agrement }}
                                        </span>
                                    @endif

                                    <span>
                                        <x-icone nom="calendrier" :taille="13" />
                                        Reçue le {{ $demande->created_at->translatedFormat('j M Y à H:i') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="console-demande-actions">
                            <form method="POST" action="{{ route('plateforme.demandes.approuver', $demande) }}">
                                @csrf
                                <x-bouton type="submit" taille="petit" icone="check">Approuver et inviter</x-bouton>
                            </form>

                            <form method="POST" action="{{ route('plateforme.demandes.refuser', $demande) }}"
                                  class="console-refus"
                                  data-confirmer="Refuser la demande de {{ $demande->nom_agence }} ?">
                                @csrf
                                <input name="motif_refus" type="text" maxlength="500"
                                       placeholder="Motif du refus (facultatif)"
                                       aria-label="Motif du refus">
                                <x-bouton type="submit" variante="contour" taille="petit">Refuser</x-bouton>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="carte">
                        <x-etat-vide icone="equipe" titre="Aucune demande en attente"
                                     texte="Les nouvelles inscriptions d’agences apparaîtront ici pour validation." />
                    </div>
                @endforelse

                <x-pagination :paginateur="$demandes" />
            @endif
        </section>

        {{-- Demandes de démonstration -------------------------------------- --}}
        <section id="demos" class="grid gap-4" aria-labelledby="demos-titre">
            <div class="console-section-titre">
                <div>
                    <p class="etiquette">Vitrine</p>
                    <h2 id="demos-titre" class="carte-titre">Demandes de démonstration</h2>
                    <p class="carte-sous-titre">Créneaux demandés depuis la page « Demander une démo », du plus proche au plus lointain.</p>
                </div>
                <span class="console-pastille">
                    <x-icone nom="calendrier" :taille="13" />
                    {{ $demandesDemo }} au total
                </span>
            </div>

            <div class="carte overflow-hidden">
                <div class="tableau-scroll">
                    <table class="tableau">
                        <thead>
                            <tr>
                                <th scope="col">Créneau demandé</th>
                                <th scope="col">Demandeur</th>
                                <th scope="col">Contact</th>
                                <th scope="col">Format</th>
                                <th scope="col">Reçue le</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($demos as $demo)
                                <tr>
                                    <td class="cellule-compacte">
                                        <p class="font-semibold">{{ $demo->creneau_at->translatedFormat('l j F Y') }}</p>
                                        <p class="text-[12px] text-muted">{{ $demo->creneau_at->format('H:i') }} · heure de Lomé</p>
                                    </td>
                                    <td>
                                        <p class="font-medium">{{ $demo->nom }}</p>
                                        <p class="text-[12px] text-muted">{{ $demo->agence ?: 'Sans agence' }}</p>
                                        @if ($demo->message)
                                            <p class="mt-1 text-[12px] text-muted">{{ \Illuminate\Support\Str::limit($demo->message, 90) }}</p>
                                        @endif
                                    </td>
                                    <td class="cellule-compacte">
                                        <p><a href="mailto:{{ $demo->email }}">{{ $demo->email }}</a></p>
                                        <p class="text-[12px] text-muted">{{ $demo->telephone }}</p>
                                    </td>
                                    <td>
                                        <x-badge :variante="$demo->format === 'presentiel' ? 'vert' : 'ambre'">
                                            {{ $demo->libelleFormat() }}
                                        </x-badge>
                                    </td>
                                    <td class="cellule-compacte text-[12px] text-muted">
                                        {{ $demo->created_at->translatedFormat('j M Y à H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <x-etat-vide icone="calendrier" titre="Aucune demande de démonstration"
                                                     texte="Les demandes envoyées depuis la vitrine apparaîtront ici avec le créneau choisi." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($demos->hasPages())
                    <div class="carte-corps border-t" style="border-color: var(--c-line-soft)">
                        <x-pagination :paginateur="$demos" />
                    </div>
                @endif
            </div>
        </section>

        {{-- Agences -------------------------------------------------------- --}}
        <section class="grid gap-4" aria-labelledby="agences-titre">
            <div class="console-section-titre">
                <div>
                    <p class="etiquette">Organisations</p>
                    <h2 id="agences-titre" class="carte-titre">Toutes les agences</h2>
                    <p class="carte-sous-titre">État des accès et volume de comptes par organisation.</p>
                </div>
                <span class="console-pastille">
                    <x-icone nom="utilisateur" :taille="13" />
                    {{ $agences->total() }} au total
                </span>
            </div>

            <div class="carte overflow-hidden">
                <div class="tableau-scroll">
                    <table class="tableau">
                        <thead>
                            <tr>
                                <th scope="col">Agence</th>
                                <th scope="col">Contact</th>
                                <th scope="col">Comptes</th>
                                <th scope="col">État</th>
                                <th scope="col" class="cellule-droite">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($agences as $agence)
                                <tr>
                                    <td>
                                        <div class="console-agence">
                                            <span class="console-monogramme" aria-hidden="true">{{ $initiales($agence->nom) }}</span>
                                            <div class="min-w-0">
                                                <p class="nom truncate">{{ $agence->nom }}</p>
                                                <p class="slug truncate">{{ $agence->slug }} · {{ ucfirst($agence->type) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="cellule-compacte">
                                        <p>{{ $agence->email ?: '—' }}</p>
                                        <p class="text-[12px] text-muted">{{ $agence->telephone ?: '' }}</p>
                                    </td>
                                    <td>
                                        <span class="console-pastille">
                                            {{ $agence->users_count }} compte{{ $agence->users_count > 1 ? 's' : '' }}
                                        </span>
                                    </td>
                                    <td>
                                        <x-badge :variante="$agence->active ? 'vert' : 'rose'">
                                            {{ $agence->active ? 'Active' : 'Suspendue' }}
                                        </x-badge>
                                    </td>
                                    <td class="cellule-droite">
                                        <form method="POST" action="{{ route('plateforme.agences.statut', $agence) }}"
                                              data-confirmer="{{ $agence->active ? 'Suspendre' : 'Réactiver' }} l’agence {{ $agence->nom }} ?">
                                            @csrf
                                            <button class="btn btn-petit {{ $agence->active ? 'btn-fantome' : 'btn-contour' }}" type="submit">
                                                {{ $agence->active ? 'Suspendre' : 'Réactiver' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <x-etat-vide icone="equipe" titre="Aucune agence"
                                                     texte="Les agences approuvées apparaîtront dans cette liste." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($agences->hasPages())
                    <div class="carte-corps border-t" style="border-color: var(--c-line-soft)">
                        <x-pagination :paginateur="$agences" />
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection
