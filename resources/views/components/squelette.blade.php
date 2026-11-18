@props(['variante' => 'shell'])

{{-- Squelette affiché pendant une navigation pleine page (voir resources/js/chargement.js). --}}
<div class="chargement-page" data-chargement-page>
    <p class="sr-only" role="status">Chargement en cours…</p>

    @if ($variante === 'auth')
        <div class="chargement-centre">
            <div class="chargement-carte">
                <div class="squelette mx-auto h-8 w-32"></div>
                <div class="squelette mx-auto mt-9 h-10 w-52"></div>
                <div class="squelette mx-auto mt-4 h-3 w-64"></div>

                <div class="mt-10 space-y-5">
                    @for ($champ = 0; $champ < 2; $champ++)
                        <div>
                            <div class="squelette h-2.5 w-20"></div>
                            <div class="squelette mt-2 h-12 w-full"></div>
                        </div>
                    @endfor

                    <div class="squelette h-12 w-full rounded-full"></div>
                </div>

                <div class="squelette mx-auto mt-9 h-3 w-56"></div>
            </div>
        </div>
    @elseif ($variante === 'portail')
        <div class="chargement-centre">
            <div class="chargement-portail">
                <div class="flex items-start justify-between gap-4">
                    <div class="squelette h-6 w-28"></div>
                    <div class="squelette h-9 w-52 rounded-full"></div>
                </div>

                <div class="squelette mt-8 h-6 w-44"></div>

                <div class="mt-5 space-y-3">
                    @for ($bloc = 0; $bloc < 4; $bloc++)
                        <div class="squelette h-16 w-full"></div>
                    @endfor
                </div>
            </div>
        </div>
    @elseif ($variante === 'console')
        <div class="mx-auto max-w-7xl px-5 py-8 sm:px-8 sm:py-10">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="squelette h-6 w-32"></div>
                <div class="squelette h-9 w-56 rounded-full"></div>
            </div>

            <div class="mt-10">
                <div class="squelette h-2.5 w-24"></div>
                <div class="squelette mt-3 h-8 w-72"></div>
                <div class="squelette mt-3 h-3 w-96"></div>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                @for ($carte = 0; $carte < 3; $carte++)
                    <div class="carte">
                        <div class="carte-corps">
                            <div class="squelette h-2.5 w-16"></div>
                            <div class="squelette mt-3 h-6 w-20"></div>
                        </div>
                    </div>
                @endfor
            </div>

            <div class="carte mt-5">
                <div class="carte-corps">
                    <div class="squelette h-4 w-40"></div>

                    <div class="mt-5 space-y-3">
                        @for ($rang = 0; $rang < 5; $rang++)
                            <div class="squelette h-11 w-full"></div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="app-shell">
            <aside class="sidebar" aria-hidden="true">
                <div class="squelette h-6 w-28"></div>

                <div class="mt-7 space-y-2">
                    <div class="squelette h-3 w-40"></div>
                    <div class="squelette h-3 w-24"></div>
                </div>

                <div class="squelette mt-6 h-9 w-52 rounded-full"></div>

                <div class="mt-7 space-y-2">
                    @for ($lien = 0; $lien < 6; $lien++)
                        <div class="squelette h-10 rounded-full" style="width: {{ 100 - $lien * 5 }}%"></div>
                    @endfor
                </div>

                <div class="mt-auto pt-6">
                    <div class="squelette h-12 w-full rounded-full"></div>
                </div>
            </aside>

            <main class="contenu" aria-hidden="true">
                <div class="contenu-interieur">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="squelette h-7 w-48"></div>
                            <div class="squelette mt-3 h-3 w-32"></div>
                        </div>

                        <div class="squelette hidden h-11 w-64 rounded-full sm:block"></div>
                    </div>

                    <div class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @for ($kpi = 0; $kpi < 4; $kpi++)
                            <div class="carte">
                                <div class="carte-corps">
                                    <div class="squelette h-2.5 w-20"></div>
                                    <div class="squelette mt-3 h-6 w-24"></div>
                                    <div class="squelette mt-2.5 h-3 w-16"></div>
                                </div>
                            </div>
                        @endfor
                    </div>

                    <div class="mt-5 grid gap-5 lg:grid-cols-3">
                        <div class="carte lg:col-span-2">
                            <div class="carte-corps">
                                <div class="squelette h-4 w-40"></div>

                                <div class="mt-5 space-y-3">
                                    @for ($rang = 0; $rang < 5; $rang++)
                                        <div class="squelette h-9 w-full"></div>
                                    @endfor
                                </div>
                            </div>
                        </div>

                        <div class="carte">
                            <div class="carte-corps">
                                <div class="squelette h-4 w-28"></div>

                                <div class="mt-5 space-y-3">
                                    @for ($ligne = 0; $ligne < 4; $ligne++)
                                        <div class="squelette h-14 w-full"></div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    @endif
</div>
