import './bootstrap';

import {
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    LinearScale,
    Tooltip,
} from 'chart.js';

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip);

/* --------------------------------------------------------------------------
   Thème clair / sombre
   -------------------------------------------------------------------------- */
const CLE_THEME = 'jalon-theme';

function themeCourant() {
    return document.documentElement.dataset.theme === 'dark' ? 'sombre' : 'clair';
}

function appliquerTheme(theme, memoriser = true) {
    const sombre = theme === 'sombre';
    document.documentElement.dataset.theme = sombre ? 'dark' : 'light';

    if (memoriser) {
        try {
            localStorage.setItem(CLE_THEME, theme);
        } catch (e) {
            /* stockage indisponible : on continue sans persistance */
        }
    }

    document.querySelectorAll('[data-theme-option]').forEach((bouton) => {
        bouton.setAttribute('aria-pressed', String(bouton.dataset.themeOption === theme));
    });

    document.dispatchEvent(new CustomEvent('jalon:theme', { detail: { theme } }));
}

appliquerTheme(themeCourant(), false);

document.addEventListener('click', (evenement) => {
    const bouton = evenement.target.closest('[data-theme-option]');
    if (bouton) {
        appliquerTheme(bouton.dataset.themeOption);
    }
});

/* --------------------------------------------------------------------------
   Menu latéral (mobile)
   -------------------------------------------------------------------------- */
document.addEventListener('click', (evenement) => {
    const bascule = evenement.target.closest('[data-bascule-menu]');
    const coque = document.querySelector('.app-shell');

    if (bascule && coque) {
        coque.dataset.menuOuvert = coque.dataset.menuOuvert === 'true' ? 'false' : 'true';
        return;
    }

    if (evenement.target.closest('[data-fermer-menu]') && coque) {
        coque.dataset.menuOuvert = 'false';
    }
});

/* --------------------------------------------------------------------------
   Modales
   -------------------------------------------------------------------------- */
let dernierFocus = null;

function ouvrirModale(modale) {
    if (!modale) {
        return;
    }

    dernierFocus = document.activeElement;
    modale.dataset.ouvert = 'true';
    document.body.classList.add('modale-ouverte');

    const premier = modale.querySelector('[autofocus], input, select, textarea, button');
    if (premier) {
        window.setTimeout(() => premier.focus(), 30);
    }
}

function fermerModale(modale) {
    if (!modale) {
        return;
    }

    modale.dataset.ouvert = 'false';
    document.body.classList.remove('modale-ouverte');

    if (dernierFocus && typeof dernierFocus.focus === 'function') {
        dernierFocus.focus();
    }
}

document.addEventListener('click', (evenement) => {
    const ouvrir = evenement.target.closest('[data-modale-ouvrir]');
    if (ouvrir) {
        evenement.preventDefault();
        ouvrirModale(document.querySelector(ouvrir.dataset.modaleOuvrir));
        return;
    }

    const fermer = evenement.target.closest('[data-modale-fermer]');
    if (fermer) {
        evenement.preventDefault();
        fermerModale(fermer.closest('.modale'));
        return;
    }

    if (evenement.target.classList.contains('modale-fond')) {
        fermerModale(evenement.target.closest('.modale'));
    }
});

document.addEventListener('keydown', (evenement) => {
    if (evenement.key !== 'Escape') {
        return;
    }

    const ouvertes = [...document.querySelectorAll('.modale[data-ouvert="true"]')];
    if (ouvertes.length > 0) {
        fermerModale(ouvertes[ouvertes.length - 1]);
        return;
    }

    const coque = document.querySelector('.app-shell');
    if (coque && coque.dataset.menuOuvert === 'true') {
        coque.dataset.menuOuvert = 'false';
    }
});

/* --------------------------------------------------------------------------
   Menus déroulants
   -------------------------------------------------------------------------- */
document.addEventListener('click', (evenement) => {
    const bouton = evenement.target.closest('[data-menu-bouton]');

    document.querySelectorAll('[data-menu-panneau]:not([hidden])').forEach((panneau) => {
        if (!bouton || panneau !== bouton.parentElement.querySelector('[data-menu-panneau]')) {
            panneau.hidden = true;
        }
    });

    if (bouton) {
        const panneau = bouton.parentElement.querySelector('[data-menu-panneau]');
        if (panneau) {
            panneau.hidden = !panneau.hidden;
        }
    }
});

/* --------------------------------------------------------------------------
   Notifications éphémères
   -------------------------------------------------------------------------- */
function creerToast(message, type = 'info') {
    let zone = document.querySelector('.toasts');

    if (!zone) {
        zone = document.createElement('div');
        zone.className = 'toasts';
        zone.setAttribute('role', 'status');
        zone.setAttribute('aria-live', 'polite');
        document.body.appendChild(zone);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;
    toast.innerHTML = `<span class="point"></span><span>${message}</span>`;
    zone.appendChild(toast);

    window.setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(8px)';
        toast.style.transition = 'opacity .2s ease, transform .2s ease';
        window.setTimeout(() => toast.remove(), 220);
    }, 4600);
}

document.querySelectorAll('[data-toast]').forEach((toast) => {
    window.setTimeout(() => {
        toast.style.transition = 'opacity .25s ease';
        toast.style.opacity = '0';
        window.setTimeout(() => toast.remove(), 260);
    }, 4800);
});

/* --------------------------------------------------------------------------
   Copie dans le presse-papiers
   -------------------------------------------------------------------------- */
document.addEventListener('click', async (evenement) => {
    const bouton = evenement.target.closest('[data-copier]');
    if (!bouton) {
        return;
    }

    evenement.preventDefault();

    try {
        await navigator.clipboard.writeText(bouton.dataset.copier);
        creerToast('Copié dans le presse-papiers.', 'succes');
    } catch (e) {
        creerToast('La copie a échoué. Sélectionnez le texte manuellement.', 'erreur');
    }
});

/* --------------------------------------------------------------------------
   Confirmations
   -------------------------------------------------------------------------- */
document.addEventListener('submit', (evenement) => {
    const formulaire = evenement.target.closest('form[data-confirmer]');
    if (formulaire && !window.confirm(formulaire.dataset.confirmer)) {
        evenement.preventDefault();
    }
});

/* --------------------------------------------------------------------------
   Raccourci de recherche (Ctrl/Cmd + K)
   -------------------------------------------------------------------------- */
document.addEventListener('keydown', (evenement) => {
    if ((evenement.metaKey || evenement.ctrlKey) && evenement.key.toLowerCase() === 'k') {
        const recherche = document.querySelector('[data-recherche]');
        if (recherche) {
            evenement.preventDefault();
            recherche.focus();
            recherche.select();
        }
    }
});

/* --------------------------------------------------------------------------
   Graphiques Chart.js
   -------------------------------------------------------------------------- */
const graphiques = {};

function couleur(nom) {
    return getComputedStyle(document.documentElement).getPropertyValue(nom).trim();
}

const etiquetteValeurs = {
    id: 'etiquetteValeurs',
    afterDatasetsDraw(graphique) {
        if (!graphique.options.plugins?.etiquetteValeurs?.actif) {
            return;
        }

        const { ctx } = graphique;
        const couleurTexte = couleur('--c-ink');

        ctx.save();
        ctx.fillStyle = couleurTexte;
        ctx.font = '500 12px "Instrument Sans", sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'bottom';

        graphique.data.datasets.forEach((jeu, indexJeu) => {
            const meta = graphique.getDatasetMeta(indexJeu);
            if (meta.hidden) {
                return;
            }

            meta.data.forEach((barre, index) => {
                const valeur = jeu.data[index];
                if (valeur === null || valeur === undefined) {
                    return;
                }

                ctx.fillText(String(valeur).replace('.', ','), barre.x, barre.y - 6);
            });
        });

        ctx.restore();
    },
};

Chart.register(etiquetteValeurs);

function construireGraphique(canvas, options) {
    const sombre = themeCourant() === 'sombre';
    const couleurTexte = couleur('--c-muted');
    const couleurGrille = couleur('--c-line');
    const couleurFond = couleur('--c-surface');

    const jeux = options.series.map((serie) => ({
        label: serie.label,
        data: serie.data,
        backgroundColor: couleur(serie.couleur),
        borderRadius: options.arrondi ?? 6,
        borderSkipped: false,
        maxBarThickness: options.epaisseur ?? 42,
        categoryPercentage: options.groupe ? 0.7 : 0.62,
        barPercentage: options.groupe ? 0.86 : 0.7,
    }));

    return new Chart(canvas, {
        type: 'bar',
        data: { labels: options.labels, datasets: jeux },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 500 },
            layout: { padding: { top: options.etiquettes ? 20 : 6, right: 4 } },
            plugins: {
                legend: { display: false },
                etiquetteValeurs: { actif: Boolean(options.etiquettes) },
                tooltip: {
                    backgroundColor: sombre ? '#f3eee8' : '#1a1917',
                    titleColor: sombre ? '#16130f' : '#ffffff',
                    bodyColor: sombre ? '#16130f' : '#ffffff',
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: (contexte) => {
                            const valeur = contexte.parsed.y ?? 0;
                            const suffixe = options.suffixe ?? '';
                            return `${contexte.dataset.label} : ${valeur.toLocaleString('fr-FR')}${suffixe}`;
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false, drawBorder: false },
                    border: { display: false },
                    ticks: {
                        color: couleurTexte,
                        font: { size: 12, family: 'Instrument Sans' },
                        padding: 6,
                    },
                },
                y: {
                    display: false,
                    beginAtZero: true,
                    grid: { display: false, drawBorder: false },
                    border: { display: false },
                    suggestedMax: options.maximum ?? undefined,
                    ticks: { display: false },
                },
            },
        },
    });
}

function creerGraphique(canvas, options) {
    if (!canvas) {
        return null;
    }

    if (graphiques[canvas.id]) {
        graphiques[canvas.id].destroy();
    }

    const graphique = construireGraphique(canvas, options);
    graphiques[canvas.id] = graphique;

    return graphique;
}

document.addEventListener('jalon:theme', () => {
    document.querySelectorAll('canvas[data-graphique]').forEach((canvas) => {
        const options = canvas._jalonOptions;
        if (options) {
            creerGraphique(canvas, options);
        }
    });

    // Les couleurs de la grille et du texte sont relues au prochain rendu.
    Object.values(graphiques).forEach((graphique) => graphique.update());
});

window.Jalon = {
    creerGraphique,
    toast: creerToast,
    couleur,
    graphiqueDepuis: (canvas, options) => {
        canvas._jalonOptions = options;
        return creerGraphique(canvas, options);
    },
};

/* --------------------------------------------------------------------------
   Facture : lignes dynamiques, totaux et filtre client → dossier
   -------------------------------------------------------------------------- */
const formulaireFacture = document.querySelector('[data-facture]');

if (formulaireFacture) {
    const corps = formulaireFacture.querySelector('[data-lignes]');
    const gabarit = formulaireFacture.querySelector('template[data-gabarit-ligne]');
    const remise = formulaireFacture.querySelector('[name="remise"]');
    const taux = formulaireFacture.querySelector('[name="tva_taux"]');

    const nombre = (valeur) => {
        const n = parseFloat(String(valeur ?? '').replace(',', '.').replace(/\s/g, ''));
        return Number.isFinite(n) ? n : 0;
    };

    const formater = (valeur) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(valeur);

    function recalculer() {
        let sousTotal = 0;

        corps.querySelectorAll('[data-ligne]').forEach((ligne) => {
            const quantite = nombre(ligne.querySelector('[name$="[quantite]"]')?.value);
            const prix = nombre(ligne.querySelector('[name$="[prix_unitaire]"]')?.value);
            const montant = Math.round(quantite * prix);
            sousTotal += montant;

            const affichage = ligne.querySelector('[data-montant]');
            if (affichage) {
                affichage.textContent = affichage.dataset.vide === 'true' ? '—' : formater(montant);
            }
        });

        const base = Math.max(0, sousTotal - Math.min(nombre(remise?.value), sousTotal));
        const tva = Math.round(base * (nombre(taux?.value) / 100));

        formulaireFacture.querySelectorAll('[data-sous-total]').forEach((el) => (el.textContent = formater(sousTotal)));
        formulaireFacture.querySelectorAll('[data-tva]').forEach((el) => (el.textContent = formater(tva)));
        formulaireFacture.querySelectorAll('[data-total]').forEach((el) => (el.textContent = formater(base + tva)));
    }

    function renumeroter() {
        corps.querySelectorAll('[data-ligne]').forEach((ligne, index) => {
            ligne.querySelectorAll('[name]').forEach((champ) => {
                champ.name = champ.name.replace(/lignes\[\d+\]/, `lignes[${index}]`);
            });
        });
    }

    corps?.addEventListener('input', recalculer);
    remise?.addEventListener('input', recalculer);
    taux?.addEventListener('change', recalculer);

    formulaireFacture.addEventListener('click', (evenement) => {
        const ajouter = evenement.target.closest('[data-ajouter-ligne]');
        if (ajouter && gabarit) {
            evenement.preventDefault();
            corps.appendChild(gabarit.content.cloneNode(true));
            renumeroter();
            recalculer();
            corps.lastElementChild?.querySelector('[name$="[designation]"]')?.focus();
            return;
        }

        const supprimer = evenement.target.closest('[data-supprimer-ligne]');
        if (supprimer) {
            evenement.preventDefault();
            const lignes = corps.querySelectorAll('[data-ligne]');
            if (lignes.length > 1) {
                supprimer.closest('[data-ligne]').remove();
                renumeroter();
                recalculer();
            }
        }
    });

    // Filtrer les dossiers selon le client sélectionné.
    const client = formulaireFacture.querySelector('[name="client_id"]');
    const dossier = formulaireFacture.querySelector('[name="dossier_id"]');

    function filtrerDossiers() {
        if (!client || !dossier) {
            return;
        }

        let premierVisible = null;

        [...dossier.options].forEach((option) => {
            if (!option.value) {
                return;
            }

            const correspond = !client.value || option.dataset.client === client.value;
            option.hidden = !correspond;
            option.disabled = !correspond;

            if (correspond && !premierVisible) {
                premierVisible = option;
            }
        });

        if (dossier.selectedOptions[0]?.disabled && premierVisible) {
            dossier.value = premierVisible.value;
        }
    }

    client?.addEventListener('change', filtrerDossiers);
    filtrerDossiers();

    recalculer();
}
