/* --------------------------------------------------------------------------
   Squelette de chargement (voir resources/views/components/squelette.blade.php).
   Affiché seulement si la navigation est assez lente pour être perceptible.
   -------------------------------------------------------------------------- */
const DELAI_AVANT_AFFICHAGE = 150;
const DELAI_SECURITE = 12000;

let minuterieAffichage = null;
let minuterieSecurite = null;

function afficherSquelette() {
    minuterieAffichage = null;
    minuterieSecurite = window.setTimeout(masquerSquelette, DELAI_SECURITE);
    document.documentElement.dataset.chargement = 'true';
}

function masquerSquelette() {
    if (minuterieAffichage !== null) {
        window.clearTimeout(minuterieAffichage);
        minuterieAffichage = null;
    }

    if (minuterieSecurite !== null) {
        window.clearTimeout(minuterieSecurite);
        minuterieSecurite = null;
    }

    delete document.documentElement.dataset.chargement;
}

function planifierAffichage() {
    if (minuterieAffichage !== null || document.documentElement.dataset.chargement === 'true') {
        return;
    }

    minuterieAffichage = window.setTimeout(afficherSquelette, DELAI_AVANT_AFFICHAGE);
}

function estNavigationInterne(lien) {
    if (lien.hasAttribute('download') || lien.dataset.sansChargement !== undefined) {
        return false;
    }

    const cible = lien.getAttribute('target');
    if (cible && cible !== '_self') {
        return false;
    }

    let url;
    try {
        url = new URL(lien.href, window.location.href);
    } catch (e) {
        return false;
    }

    if (url.origin !== window.location.origin) {
        return false;
    }

    const memeDocument =
        url.pathname === window.location.pathname && url.search === window.location.search;

    // Ancre vers la page courante : le navigateur ne recharge rien.
    return !(memeDocument && url.hash);
}

function navigueVersUnFormulaire(formulaire) {
    if (formulaire.dataset.sansChargement !== undefined) {
        return false;
    }

    const cible = formulaire.getAttribute('target');
    return !cible || cible === '_self';
}

export function demarrerSurveillanceChargement() {
    document.addEventListener('click', (evenement) => {
        if (evenement.defaultPrevented || evenement.button !== 0) {
            return;
        }

        if (evenement.metaKey || evenement.ctrlKey || evenement.shiftKey || evenement.altKey) {
            return;
        }

        const lien = evenement.target.closest('a[href]');

        if (lien && estNavigationInterne(lien)) {
            planifierAffichage();
        }
    });

    document.addEventListener('submit', (evenement) => {
        if (evenement.defaultPrevented || !(evenement.target instanceof HTMLFormElement)) {
            return;
        }

        if (navigueVersUnFormulaire(evenement.target)) {
            planifierAffichage();
        }
    });

    // Retour arrière depuis le cache du navigateur : rien n'a été rechargé.
    window.addEventListener('pageshow', (evenement) => {
        if (evenement.persisted) {
            masquerSquelette();
        }
    });

    document.addEventListener('keydown', (evenement) => {
        if (evenement.key === 'Escape') {
            masquerSquelette();
        }
    });
}
