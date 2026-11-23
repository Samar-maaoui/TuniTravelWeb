# 🌍 Itinireves – Plateforme Web de Réservation de Voyages

Projet développé dans le cadre du module **Projet Intégré : Développement Web__3A26**  
à **Esprit School of Engineering**, année universitaire 2024–2025.

---

## Table des matières

- [Présentation](#présentation)
- [Modules Fonctionnels](#modules-fonctionnels)
- [Fonctionnalités Avancées](#fonctionnalités-avancées)
- [Technologies Utilisées](#technologies-utilisées)
- [Services & APIs Intégrés](#services--apis-intégrés-srcservice)
- [Topics](#topics)
- [Structure du Projet](#structure-du-projet)
- [Installation](#installation)
- [Statistiques Intégrées](#statistiques-intégrées)
- [Contribution](#contribution)
- [Remerciements](#remerciements)

---

## Présentation

**Itinireves** est une application web modulaire permettant à des utilisateurs de :

- Réserver des **packs voyages**, **vols**, **hôtels** et **excursions**
- Participer à des **événements VIP**
- Donner des **avis** après leurs expériences
- Gérer leur profil et visualiser leur historique
- Bénéficier d’une **expérience de paiement fluide et sécurisée**

Côté administration, une interface complète permet la gestion des contenus et la visualisation de statistiques globales.

---

## Modules Fonctionnels

## 🧩 Modules Fonctionnels

### 👤 Utilisateurs
- Inscription, connexion sécurisée (reCAPTCHA)
- Vérification d’identité (faciale + infos personnelles)
- Modification de profil, historique des réservations
- Statuts de paiement (payée / non payée)
- Gestion de rôles (client, admin)

### 🛫 Vols
- Consultation des vols selon dates et destinations
- Filtres dynamiques (prix, durée, compagnie)
- Affichage clair des horaires et villes de départ / arrivée
- Réservation d’un vol seul ou intégré à un pack
- Paiement sécurisé + confirmation automatique

### 🏨 Hôtels
- Liste d’hôtels filtrables par ville, prix, type d’hébergement
- Affichage des détails (photos, équipements, description)
- Intégration de la géolocalisation via Google Maps
- Réservation d’une ou plusieurs nuits avec dates flexibles
- Téléchargement automatique des informations en PDF

### 🗺️ Excursions
- Parcours et circuits touristiques (culturels, nature…)
- Cartes interactives avec trajets et points d’intérêt
- Réservation limitée selon nombre de places disponibles
- Intégration dans un pack ou en option libre
- Itinéraire généré en PDF + QR code à présenter

### 🎉 Événements VIP
- Événements réservés à certains profils (VIP, fidélité, etc.)
- Validation automatique d’éligibilité selon statut utilisateur
- Gestion des invitations et quotas de places
- Système de notifications (mail + SMS)
- Affichage dynamique des événements à venir

### 🧳 Packs Voyage
- Offre combinée : vol + hôtel + excursions
- Réservation groupée en une seule opération
- Interface de sélection dynamique avec mises à jour des places restantes
- Paiement unique (PayPal / Stripe / Paymee)
- Génération d’un billet unique PDF + QR code

### 📰 Posts / Annonces
- Affichage des offres et nouveautés sous forme de cartes
- Intégration dans la page client avec modale de détails
- Système de tri (récent, populaire…) et recherche par titre ou lieu
- Lien vers la réservation directe ou page de l’événement
- Gestion par l’admin (ajout, édition, suppression)

### 💬 Feedback
- Formulaire d’avis après participation à une activité ou pack
- Système de notation (étoiles) et commentaire
- Modération automatique ou manuelle des retours
- Statistiques de satisfaction visible en back-office
- Intégration dans les statistiques générales (ChartJS)


---

## Fonctionnalités Avancées

- Statistiques dynamiques (Google Charts, ChartJS)
- Notifications e-mail (Brevo, Postmark)
- SMS de confirmation (Twilio, Infobip)
- Génération automatique de billets PDF
- Paiement sécurisé (PayPal, Stripe, Paymee)
- QR Code pour vérification à l’entrée
- Géolocalisation des hôtels et circuits

---

## Technologies Utilisées

- Symfony 6.4, PHP 8.1
- Doctrine ORM, Twig, Bootstrap
- Stimulus, Turbo, Webpack Encore
- Snappy PDF, Endroid QRCode
- VichUploaderBundle
- Google Charts, Chart.js
- KnpPaginator, Pagerfanta

---

## Services & APIs Intégrés (`src/Service/`)

| Service/API                | Rôle                                 |
|---------------------------|--------------------------------------|
| Brevo, Postmark           | Envoi d’e-mails                      |
| Twilio, Infobip           | SMS et alertes                       |
| PayPal, Stripe, Paymee    | Paiement sécurisé                    |
| GeocodingService          | Localisation dynamique               |
| IdentityVerificationService | Vérification d’identité           |
| FaceVerificationService   | Reconnaissance faciale               |
| QrCodeGenerator           | Génération de billets numériques     |
| PdfService                | Génération PDF                       |
| UnsplashService           | Images automatiques                  |
| PredictHQService          | Suggestions d’événements             |

---

## Topics

`symfony` `php` `esprit` `travel` `packs` `flight-booking` `hotel-reservation`  
`excursion` `event-management` `paypal` `stripe` `user-feedback` `admin-dashboard`

---

## Structure du Projet

```plaintext
PIWEB/
├── src/
│   ├── Controller/
│   ├── Entity/
│   ├── Form/
│   └── Service/
├── templates/
├── public/
├── config/
├── translations/
├── migrations/
├── .env
└── composer.json

Installation
git clone https://github.com/aymen74407/PIWEB.git
cd PIWEB
composer install

✅ La configuration de la base de données se fait directement dans le fichier .env.
Modifie cette ligne avec tes identifiants :

env
DATABASE_URL="mysql://user:password@127.0.0.1:3306/base_commune"
Puis exécute :


php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

symfony serve
Statistiques Intégrées
Réservations mensuelles (Ligne)

Utilisateurs les plus actifs (Barres)

Popularité des modules (Camembert)

Contribution
Fork du projet

Créer une branche :
git checkout -b feature/ma-feature

Commit :
git commit -m "Ajout fonctionnalité"
Push :


git push origin feature/ma-feature
Ouvre ensuite une Pull Request sur GitHub

Remerciements
Projet réalisé sous la supervision de l’équipe pédagogique
Esprit School of Engineering – Module Projet Intégré : Développement Web__3A26

"Le voyage commence dès la première ligne de code." 🚀
"# TuniTravelWeb" 
