Feuille de Route – Site Web Madagascar Tours
Vue d’ensemble

Cette feuille de route organise les tâches de développement en phases claires avec des checklists.
L’objectif est de transformer le site WordPress d’un template statique en une plateforme dynamique de réservation de circuits à Madagascar.

PHASE 1 — Fondations et Nettoyage du Code

Préparer le projet pour qu’il soit maintenable, internationalisable et évolutif.

1. Internationalisation (i18n)

 Remplacer tous les textes écrits en dur dans les fichiers PHP par les fonctions de traduction WordPress :

__()

_e()

 S'assurer que la langue par défaut est l’anglais

 Préparer la génération d’un fichier de traduction .pot

2. Structure du Code et Généralisation

 Refactoriser functions.php pour centraliser la logique personnalisée

 Vérifier que les modifications n’affectent pas le thème parent

 Organiser la logique dans des fichiers modulaires dans /inc/

Structure recommandée
/inc
  settings.php
  cpt-tours.php
  cpt-reservations.php
  email-functions.php
  helpers.php

3. Variables de Configuration Statique

Créer le fichier :

/inc/settings.php

pour stocker les informations réutilisables.

Variables à définir

 Numéro WhatsApp (format international)

 Email administrateur

 Informations pour le dépôt (acompte)

 Mobile Money

 Coordonnées bancaires

Exemple
define('SITE_WHATSAPP', '+261XXXXXXXXX');
define('SITE_ADMIN_EMAIL', 'contact@site.com');
define('SITE_DEPOSIT_INFO', 'Mobile Money / Virement bancaire');

4. Nettoyage des Patterns

Analyser le dossier :

/patterns/

Actions :

 Supprimer le Lorem Ipsum

 Supprimer les références à :

Bali

Thaïlande

Japon

 Remplacer par du contenu sur Madagascar

 Modifier ou supprimer les 26 fichiers PHP existants

PHASE 2 — Développement du Moteur Backend

Transformer le site en site dynamique avec des Custom Post Types (CPT).

1. Custom Post Type : Tours

Créer un CPT :

Tours
Champs nécessaires

 Durée du circuit

 Itinéraire (jour par jour)

 Services inclus

 Services non inclus

 Prix (optionnel)

 Image principale

Champs personnalisés suggérés

duration

itinerary

includes

excludes

2. Custom Post Type : Reservations

Créer un CPT :

Reservations

pour enregistrer les demandes de réservation.

Champs

 Nom du client

 Email

 Circuit choisi

 Date du voyage

 Nombre de voyageurs

 Message (optionnel)

Colonnes dans l’administration

 Nom du client

 Email

 Circuit choisi

 Date du voyage

 Nombre de voyageurs

 Statut de la réservation

3. Workflow des Statuts de Réservation

Implémenter les statuts suivants :

Pending (En attente)

Availability Confirmed (Disponibilité confirmée)

Deposit Requested (Acompte demandé)

Deposit Received (Acompte reçu)

Confirmed (Confirmé)

Cancelled (Annulé)

Ces statuts serviront à automatiser les emails.

PHASE 3 — Affichage Front-End

Adapter le design et connecter les templates aux données dynamiques.

1. Section Hero (Page d’accueil)

 Remplacer banner-img1.jpg

 Utiliser une photo de paysage de Madagascar

Nouveau titre

Discover Madagascar with Local Experts

2. Page Liste des Circuits

Créer une page dynamique affichant tous les circuits.

Fonctionnalités

 Grille de circuits

 Image principale

 Description courte

 Durée

 Bouton "Voir les détails"

Template
archive-tours.php
3. Page Détail d’un Circuit

Créer le template :

single-tours.php
Sections

 Image principale

 Présentation du circuit

 Itinéraire en accordéon

 Inclus

 Non inclus

 Formulaire de réservation

Exemple d’itinéraire

Jour 1 – Arrivée à Antananarivo
Jour 2 – Route vers Andasibe
Jour 3 – Visite de la réserve de lémuriens

4. Navigation

Mettre à jour les menus header et footer.

Remplacer les liens # par les pages réelles :

 Home

 Tours

 Why Choose Us

 Learn More

 Contact

PHASE 4 — Système de Réservation et Emails

Automatiser le workflow entre le client et l’administrateur.

1. Formulaire de Réservation

Ajouter un formulaire sur la page détail du circuit.

Champs

 Nom

 Email

 Date du voyage

 Nombre de voyageurs

 Message

Comportement

 Création d’une entrée dans Reservations

 Statut initial : Pending

2. Configuration SMTP

Installer un plugin pour sécuriser l’envoi d’email.

Plugins recommandés

WP Mail SMTP

FluentSMTP

Objectifs

 Améliorer la délivrabilité

 Éviter les spams

3. Template Email Professionnel

Créer un modèle HTML réutilisable.

Éléments

 Logo

 Design propre

 Coordonnées

 Signature

Signature
RABE DAGO
Local Madagascar Travel Expert
4. Workflow d’Emails Automatisés
Email 1 — Réponse automatique au client

Déclenchement : nouvelle réservation

Message :

Nous avons bien reçu votre demande et nous vous répondrons sous 24 heures.

Email 2 — Notification Admin

Déclenchement : nouvelle réservation

Contenu :

Nom du client

Email

Circuit

Date

Nombre de voyageurs

Email 3 — Instructions de dépôt (manuel)

Envoyé par l’admin après confirmation de disponibilité.

Contenu :

Montant de l’acompte

Instructions de paiement

Mobile Money / Banque

Email 4 — Confirmation finale

Déclenchement : acompte reçu

Message :

Votre réservation est confirmée.

PHASE 5 — Pages Secondaires et SEO

Améliorer la conversion et la visibilité.

1. Page "Learn More"

Expliquer le processus de réservation.

Étapes

Choisir un circuit

Envoyer la demande

Confirmation de disponibilité

Paiement de l’acompte

Réservation confirmée

2. Page Contact

Inclure :

 Formulaire de contact

 Lien direct WhatsApp

 Email

 Carte (optionnel)

Exemple lien WhatsApp
https://wa.me/261XXXXXXXXX
3. Optimisation SEO

Installer un plugin SEO.

Recommandé

Rank Math

Yoast SEO

Vérifications

 Meta title

 Meta description

 Contenu structuré des circuits

 Images OpenGraph

4. Vérification Mobile

Tester sur smartphone.

Vérifier :

 Navigation du menu

 Pages circuits

 Formulaire de réservation

 Temps de chargement