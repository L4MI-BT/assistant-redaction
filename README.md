# Assistant de rédaction — plugin WordPress
 
Plugin WordPress qui ajoute, dans l'administration, un écran pour rédiger un article à partir d'un brief (sujet, mot-clé, ton, langue, consigne facultative). Un LLM local (Ollama + Gemma 3 4b) propose un texte, l'utilisateur le relit, puis décide s'il crée un brouillon. Rien n'est jamais publié automatiquement.
 
> Le détail de mes recherches et de mes tests de modèles est dans [`Decision.md`](docs/Decision.md), et le suivi jour par jour dans [`journal.md`](docs/journal.md).
 
---
 
## Prérequis
 
- WordPress 7.1.3 avec PHP 8.3.6 et MySQL 8
- [Ollama](https://ollama.com), joignable depuis le serveur WordPress (par défaut sur la **même machine**, sur `http://localhost:11434` ; modifiable via `ASSREDAC_OLLAMA_URL`, voir *Configuration*)
- Environ 4 Go de RAM libres pour le modèle (testé sur 16 Go, CPU uniquement, sans GPU)
## Installation
 
### 1. Lancer le LLM local
 
```bash
# Installer Ollama (Linux) — démarre un service systemd sur le port 11434
curl -fsSL https://ollama.com/install.sh | sh
 
# Télécharger le modèle utilisé par le plugin
ollama pull gemma3:4b
 
# Vérifier que l'API répond et que le modèle est présent
curl http://localhost:11434/api/tags

# Le script lance Ollama comme service systemd. Pour le (re)démarrer et vérifier :
sudo systemctl start ollama
sudo systemctl status ollama

# Sans systemd (macOS, Windows, installation manuelle) : lancer l'application Ollama,
# ou dans un terminal dédié :
ollama serve
```
 
Pour macOS / Windows : installateur sur https://ollama.com/download.

### 2. WordPress local (si vous n'en avez pas déjà un)

J'ai testé avec le serveur intégré de PHP et une base MySQL :

1. Télécharger WordPress et créer une base de données vide.
2. Depuis le dossier de WordPress : `php -S localhost:8000`
3. Ouvrir http://localhost:8000 et suivre l'installation.
4. (Facultatif) `define( 'WP_DEBUG', true );` dans `wp-config.php` pour voir les erreurs.

Le plugin devrait fonctionner avec n'importe quelle installation de WordPress, à condition que PHP puisse joindre Ollama (testé uniquement avec le serveur intégré de PHP).
Si WordPress tourne dans Docker (wp-env, docker-compose…), `localhost` désigne le conteneur :
il faut définir `ASSREDAC_OLLAMA_URL` vers la machine hôte et faire écouter Ollama
au-delà de 127.0.0.1 (variable `OLLAMA_HOST`), ce qui l'expose au réseau local. Non testé de mon côté.

### 3. Installer le plugin
 
1. Copier le dossier `assistant-redaction/` dans `wp-content/plugins/` (ou téléverser le ZIP via *Extensions > Ajouter*).
2. Activer **assistant-redaction** dans *Extensions*.
3. L'écran est disponible dans **Articles > Assistant de rédaction** (rôle Contributeur ou plus : capacité `edit_posts`).

### Configuration (facultative)
 
Les réglages sont des constantes, modifiables dans `wp-config.php` sans toucher au plugin :
 
```php
define( 'ASSREDAC_OLLAMA_URL', 'http://localhost:11434' ); // adresse d'Ollama
define( 'ASSREDAC_MODEL', 'gemma3:4b' );                    // modèle utilisé
define( 'ASSREDAC_TIMEOUT', 120 );                          // délai max d'attente, en secondes
```
 
Aucun secret n'est nécessaire : Ollama tourne en local, sans clé d'API.
 
## Comment tester
 
> **Démo en 5 captures commentées** (brief > proposition > brouillon) : [`docs/demo/demo.md`](docs/demo/demo.md)

**Chemin heureux** (brief > proposition > brouillon) :

1. *Articles > Assistant de rédaction*.
2. Remplir le brief, par exemple : sujet « les bienfaits de la marche quotidienne », mot-clé « marche à pied », ton « conversationnel et bienveillant », consigne « termine par un conseil pratique ».
3. Cliquer sur **Générer l'article**. Compter **environ 1 minute** sur une machine sans GPU (un peu plus au premier appel, le temps de charger le modèle).
4. Relire la proposition. Trois choix :
   - **Modifier dans l'éditeur** : crée un brouillon et l'ouvre dans l'éditeur WordPress ;
   - **Régénérer** : nouvelle proposition avec le même brief ;
   - **Modifier le brief** : retour au formulaire pré-rempli.
5. Vérifier dans *Articles* que l'article est bien en **brouillon**.
**Cas d'erreur à essayer** :
 
- Ollama arrêté (`sudo systemctl stop ollama`) → message « Ollama est injoignable ».
- Champ obligatoire vidé (en retirant l'attribut `required` dans les outils du navigateur) → la validation côté serveur réaffiche le formulaire pré-rempli avec l'erreur.

## Temps passé
 
| Étape | Temps |
|---|---|
| Lecture du sujet et de la documentation des plugins | ~4 h |
| Choix de l'outil, installation d'Ollama, tests de 4 modèles | ~4 h |
| Installation de WordPress en local | 1 h |
| Développement du plugin (client Ollama, prompt, page d'admin) | ~8 h |
| README et démo | 1h |
| **Total** | **18h** |
 
J'ai compris la durée indicative de 6 à 8 h comme portant sur le développement du plugin, qui m'a pris environ 8 h. J'y ai ajouté environ 9 h pour découvrir le développement de plugins WordPress et comparer plusieurs modèles avec Ollama, car je voulais justifier le choix du modèle par des tests plutôt que de le prendre au hasard.
Le plugin est fonctionnel sur le chemin prévu (brief > proposition > brouillon), mais je n'ai pas eu le temps d'ajouter l'indicateur d'attente en JavaScript prévu pendant la génération.
 

## Méthode de travail

J'ai utilisé un assistant IA comme un collègue plus expérimenté. Il proposait des options, que je vérifiais dans la documentation officielle (WordPress, Ollama), que je testais, puis que je tranchais. Pour le code, nous fixions d'abord le fonctionnement ensemble ; j'ai écrit une partie des méthodes moi-même, et relu puis retapé celles qu'il proposait, en m'assurant de pouvoir expliquer chaque ligne. Les décisions, les essais et les erreurs sont tracés dans [`journal.md`](docs/journal.md). 


## Choix et pourquoi
 
### Outil : Ollama
 
Comparé à vLLM (trop lourd pour ce besoin), LM Studio et Jan (interface graphique inutile ici). Ollama s'installe en une commande, expose une API HTTP locale documentée et permet de changer de modèle en une commande, ce qui facilitait la comparaison.
 
### Modèle : Gemma 3 4b
 
J'ai testé Ministral 3 (3b et 8b), Qwen 3.5 4b et Gemma 3 4b avec la même requête, à froid, sur ma machine.
 
| Modèle | Pourquoi écarté / retenu |
|---|---|
| Ministral 3 3b | Écarté : article tronqué avec le format JSON imposé (2 essais sur 2), source inventée récurrente |
| Qwen 3.5 4b | Écarté : inutilisable sans désactiver le mode réflexion (11 min sans réponse), français irrégulier |
| Ministral 3 8b | Référence seulement : meilleur texte, mais 2 fois plus lent (~2 min 20) |
| **Gemma 3 4b** | **Retenu** : le plus rapide sur un article complet, français le plus naturel, aucune source inventée. Ses défauts (balises en trop, mot-clé un peu répété) sont corrigeables par le code ou par le relecteur |
 
### Appel du modèle : HTTP depuis PHP, sur le même serveur
 
Le plugin appelle `POST /api/chat` d'Ollama avec `wp_remote_post()`, côté serveur. Ollama n'est donc jamais exposé au navigateur ni à Internet (il n'a pas d'authentification par défaut). 
J'ai écarté une API cloud (payante, textes qui sortent de l'entreprise, clé à protéger) et un serveur LLM dédié (je n'ai pas de deuxième machine).
J'ai aussi écarté :
- **un appel direct depuis le navigateur** (`fetch()` vers Ollama) : Ollama devrait être joignable depuis chaque poste, ou exposé sur Internet sans authentification, et le serveur ne pourrait plus vérifier la réponse avant de l'enregistrer 
- **l'API compatible OpenAI** d'Ollama (`/v1/chat/completions`) : elle faciliterait un changement de fournisseur, mais tous mes tests et le paramètre `format` ont été validés avec l'API native.
 
L'appel est **synchrone** et sans streaming (`stream: false`) : c'est le plus simple pour un premier jet, au prix d'une page qui attend la réponse.
 
### Échange avec le LLM
 
- **Message système** fixe : il remplace le prompt par défaut du modèle et décrit le rôle, le format et les règles.
- **Message utilisateur** : une ligne par champ du brief.
- **Sortie structurée** (paramètre `format` d'Ollama avec un schéma JSON `titre` / `contenu`) : la séparation titre / corps est garantie par Ollama, pas par la bonne volonté du modèle.
- **Contenu en HTML limité à `<p>` et `<h2>`** : WordPress stocke du HTML, donc pas de conversion ; une liste fermée de deux balises est facile à vérifier.
- **Consignes écrites en positif** (« uniquement des balises `<p>` et `<h2>` ») : mes tests ont montré qu'interdire le Markdown ne suffisait pas.
- **Consignes précises** issues des tests : mot-clé « deux ou trois fois », « sans jamais demander de précisions », « ne cite aucune étude, statistique ni source ».

### Contrôles sur la réponse
 
Chaque contrôle répond à un défaut observé pendant les tests :
 
| Contrôle | Pourquoi |
|---|---|
| `done_reason` doit valoir `stop` | Qwen a été coupé par la limite de tokens (`length`) |
| JSON valide, champs `titre` et `contenu` présents et non vides | Ne jamais créer de brouillon à partir d'une réponse cassée |
| HTML filtré par liste blanche (`wp_kses`, `<p>` et `<h2>` seulement) + `force_balance_tags` | Gemma ajoute parfois `<b>` et laisse une balise ouverte ; c'est aussi une protection de sécurité |
| Au moins 100 mots | L'article tronqué de Ministral finissait pourtant par `stop` |
 
En cas d'échec, l'utilisateur voit un message clair et peut régénérer.
 
### Organisation du code
 
```
assistant-redaction/
├── assistant-redaction.php                    # en-tête du plugin, constantes, assemblage
└── includes/
    ├── class-assredac-ollama-client.php       # HTTP uniquement : envoie, vérifie le code, décode
    ├── class-assredac-article-generator.php   # prompt, schéma, contrôles, nettoyage
    └── class-assredac-admin-page.php          # menu, formulaire, relecture, brouillon
```
 
Une classe par responsabilité, reliées par injection dans le constructeur (la page reçoit le générateur, qui reçoit le client). Changer de fournisseur de LLM toucherait surtout le client : le générateur lit encore deux champs propres à Ollama (`done_reason` et `message.content`), qu'il faudrait adapter. Pas de namespace ni d'autoloader : inutile pour trois fichiers.
 
La page d'admin suit deux temps : **traiter** le formulaire au hook `load-…` (avant tout affichage, ce qui permet la redirection vers l'éditeur), puis **afficher** l'écran mémorisé (formulaire ou relecture).
 
### Sécurité
 
- Capacité `edit_posts` vérifiée au traitement et à l'affichage, nonce sur chaque formulaire.
- Entrées nettoyées (`sanitize_text_field`, `sanitize_textarea_field`), sorties échappées (`esc_attr`, `esc_html`, `wp_kses_post`).
- L'article relu repasse par le navigateur (champs cachés) : il est **renettoyé** à son retour, avec les mêmes règles que la réponse du modèle.
- Le plugin crée uniquement un **brouillon**, attribué à l'utilisateur connecté.

## Limites et fragilités
 
- **Temps d'attente** : environ 1 minute sur CPU, pendant laquelle la page est bloquée. Indicateur de temps en Js non réalisé par manque de temps.
- **Testé uniquement sur ma machine** (Pop!_OS, serveur PHP intégré `php -S`). Sur un hébergement classique, un délai du serveur web ou de PHP pourrait couper la requête avant la réponse d'Ollama : non testé.
- **Le modèle n'est pas fiable sur les faits** et n'est pas déterministe : la relecture humaine est indispensable.
- **Longueur** : Parfois 200 mots obtenus pour ~300 demandés.
- **Mot-clé** parfois répété plus que demandé.
- **Fenêtre de contexte limitée** (4 096 tokens observés) : une consigne très longue réduirait la place pour la réponse. Limite de 500 caractères vérifiée uniquement côté navigateur.
- **Injection de prompt** : la consigne est du texte libre envoyé au modèle ; un utilisateur peut tenter de contourner les règles. L'impact reste limité (HTML filtré, brouillon seulement, relecture humaine).
- **Une seule langue** proposée (français).
- **Aucun test automatisé.**
- **Plusieurs utilisateurs en même temps** : non testé. Les générations risquent d'être plus longues si plusieurs rédacteurs génèrent simultanément.
- **Pas de vrai rôle système avec Gemma** : les règles et la consigne arrivent dans le même message. Si la consigne contredit les règles (par exemple « écris 500 mots »), le résultat est imprévisible, et la génération peut dépasser le délai de 120 s.
- **Double clic** : sans le JS prévu, cliquer deux fois sur « Générer » lance deux générations.
- **Champs cachés** : leur contenu est renettoyé à leur retour, mais leur type n'est pas vérifié. Un champ volontairement remplacé par un tableau peut provoquer une erreur PHP (rien n'est alors enregistré).
- **Titre vide** : un titre qui ne contiendrait que des balises deviendrait vide après nettoyage, sans être détecté.
- **Choix du modèle** fondé sur 1 à 3 essais par modèle : un échantillon réduit.

## Ce que je ferais ensuite

1. Un fichier Js pour empêcher de cliquer sur le bouton de génération 2 fois de suite, plus un indicateur de temps de génération. 
2. Tests unitaires du générateur avec un faux client Ollama (rendu facile par l'injection de dépendance).
3. Tester sur un hébergement réel (Apache ou Nginx + PHP-FPM) et ajuster les délais.
4. Ajouter d'autres langues et l'internationalisation des textes de l'interface.
5. Des consignes éditoriales modifiables par la rédaction (une page de réglages), le contrat technique du prompt (format JSON, balises autorisées) restant dans le code, versionné et testé.