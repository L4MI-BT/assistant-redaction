# Outils pour LLM

| Model | Ecater | Retenue | Args |
:------ |:------:|:-------:|:----:|
|vLLM|x| |trop gourmand et over-engineering ici|
|LM Studio|x| |interface graphique inutile|
|Jan|x| |interface graphique inutile|
|Ollama||x|installation simple, bien documenté, chargement modèle en 1 commande => facilité pour tester les modèles|

 **Outil** : Ollama (installé comme service systemd), appel via l'API `POST http://localhost:11434/api/chat` avec curl
# Ma machine
- **Machine** : Pop!_OS, Intel UHD Graphics 620 (pas de GPU dédié), 16 Go de RAM → **CPU uniquement**

## Modèles à tester 
- Qwen 3.5, 4b
- Ministral 3, 3b
- Ministral 3, 8b (normalement trop gourmant mais si j'ai le temps à tester)
- Gemma 3, 4b

appel via l'API `POST http://localhost:11434/api/chat` avec `curl` 

## Requête utilisée
 
Requête de base (modifié uniquement le modèle) :
 
```json
{
  "model": "qwen3.5:4b",
  "messages": [
    {
      "role": "system",
      "content": "Tu es un rédacteur web. Tu rédiges des articles à partir d'un brief. Tu n'utilises ni émojis ni Markdown. Tu ne poses pas de questions."
    },
    {
      "role": "user",
      "content": "Rédige un article de blog d'environ 300 mots. Sujet : les bienfaits de la marche quotidienne. Mot-clé : marche à pied. Ton : conversationnel et bienveillant. Langue : français. Consigne : termine par un conseil pratique."
    }
  ],
  "stream": false
}
```
## Ministral 3, 3b :

- **Pourquoi ce modèle** : candidat principal. Modèle « edge » de Mistral AI, conçu pour les petites machines ; Mistral est une entreprise française (bon français attendu, à vérifier) ; la famille existe en 3b, 8b et 14b, ce qui permet de mesurer l'effet de la taille à comportement égal.

### Test 1 - en ligne de commande, sans message système :
 
```bash
ollama run ministral-3:3b --verbose "$(cat brief-test.txt)"
```
Non représentatif de la réalité donc modifé plus tard.
 
Contenu de `brief-test.txt` (à ajuster si modifié) :
 
```text
Rédige un article de blog d'environ 300 mots.
Sujet : les bienfaits de la marche quotidienne.
Mot-clé à intégrer : marche à pied.
Ton : conversationnel et bienveillant.
Langue : français.
Consigne : termine par un conseil pratique.
Donne un titre, puis le corps de l'article.
```

Modèle déjà chargé (temps de chargement non représentatif).
 
| Mesure | Valeur |
|---|---|
| Durée totale | **91,2 s (1 min 31 s)** |
| Chargement | 2,8 ms (modèle déjà en mémoire) |
| Prompt lu | **627 tokens**, 17,5 s → 35,8 tokens/s |
| Tokens générés | 567 |
| Durée de génération | 73,7 s |
| Vitesse de génération | **7,7 tokens/s** |
| Longueur | ~365 mots (`wc -w`) |
 
**Évaluation du texte**
 
| Critère | Résultat |
|---|---|
| Français correct et naturel |  fluide, sans faute visible |
| Ton conversationnel et bienveillant |  |
| Mot-clé « marche à pied » |  5 occurrences, dont 4 en gras |
| Longueur (~300 mots) |  ~365 mots |
| Conseil pratique final |  |
| Titre |  présent, mais en Markdown et non séparé du contenu |
| Format |  Markdown (`**gras**`, `###`, `---`), illisible tel quel dans WordPress |
| Ajouts non demandés |  émojis, écriture inclusive (« prêt·e ») |
| Exactitude |  affirmations santé non sourcées (système immunitaire, globules blancs) |
 
**Observations**
 
- 627 tokens lus pour un brief d'environ 100 tokens : le prompt système par défaut (~520 tokens) explique l'écart et coûte environ 17 s par appel.
- `ollama run` ne reflète pas le fonctionnement du plugin : les tests suivants passent par l'API.
**Texte généré**
 
```text
**La marche à pied : la solution douce pour une vie plus saine et épanouie**
 
Saviez-vous qu'une simple **marche à pied** quotidienne peut transformer radicalement votre bien-être ? Pas besoin de courir ou de s'engager dans un régime extrême : une promenade régulière, même de 20 à 30 minutes par jour, peut apporter des bénéfices surprenants sur la santé physique et mentale. Alors, pourquoi ne pas commencer dès aujourd'hui à marcher plus ?
 
### **Un allié contre le stress et l'anxiété**
Le corps humain est conçu pour bouger. Une **marche à pied** stimule la production d'endorphines, ces hormones du bonheur qui réduisent le stress et améliorent l'humeur. En respirant profondément en marchant, on libère aussi les tensions accumulées, que ce soit après une journée chargée ou simplement pour se recentrer. Et le meilleur ? C'est gratuit, accessible à tous, et ne demande presque aucun équipement !
 
### **Renforcer son cœur et son système immunitaire**
Saviez-vous que marcher régulièrement peut équivaloir à une séance de sport ? Cette activité modérée améliore la circulation sanguine, renforce le cœur et réduit les risques de maladies cardiovasculaires. De plus, elle booste le système immunitaire en favorisant la circulation des globules blancs, ce qui limite les risques d'infections. Une raison de plus pour intégrer la **marche à pied** dans sa routine !
 
### **Équilibrer son poids et son énergie**
Si vous cherchez à perdre du poids ou simplement à mieux gérer votre énergie, la marche à pied est une excellente solution. Elle brûle des calories sans être aussi intense qu'une séance de musculation. En combinant marche rapide et pauses pour s'étirer, on tonifie les muscles sans les solliciter trop, ce qui préserve les articulations.
 
### **Un moyen simple de se reconnecter à soi**
Enfin, marcher en pleine conscience – c'est-à-dire en observant ses pas, sa respiration et son environnement – permet de se détendre et de se reconnecter à soi. Que ce soit en forêt, dans un parc ou simplement en ville, cette pratique apaisante favorise la méditation en mouvement.
 
---
**Alors, prêt·e à commencer ?**
Commencez par 10 minutes par jour et augmentez progressivement. Choisissez un endroit agréable pour marcher : un parc, une promenade en forêt ou même vos pas du quotidien. Et n'oubliez pas : l'important est la régularité plutôt que l'intensité. Votre corps et votre esprit vous remercieront ! 🌿💙
```
### Test 2 — API avec message système, prompt en cache
 
**Date** : 2026-10-04, 13:24 UTC —  **non représentatif** : modèle déjà chargé et prompt déjà en cache (requête identique lancée juste avant).
 
| Mesure | Valeur |
|---|---|
| Durée totale | 46,2 s |
| Chargement | 1,5 ms (modèle déjà en mémoire) |
| Prompt lu | **102 tokens**, dont 101 en cache, 0,1 s |
| Tokens générés | 426 |
| Durée de génération | 46,0 s |
| Vitesse de génération | **9,3 tokens/s** |
| `done_reason` | `stop` |
| Longueur | ~297 mots (`wc -w`) |
 
**Évaluation du texte**
 
| Critère | Résultat |
|---|---|
| Émojis, titres `###`, écriture inclusive |  disparus |
| Markdown |  titre encore en `**gras**` : consigne partiellement respectée |
| Longueur (~300 mots) |  ~297 mots |
| Mot-clé « marche à pied » |  6 occurrences |
| Ton |  plus scolaire (« Premièrement, Ensuite, Enfin ») |
| Français |  « Pour commencer, commencez par… » |
| Exactitude |  « Une étude a même montré que… » : source inventée |
 
**Observation** : le message système remplace bien le prompt par défaut (627 → 102 tokens).
 
**Texte généré**
 
>text
>**La marche à pied : un remède naturel pour une vie plus saine**
> 
>Saviez-vous que la marche à pied est l'une des activités les plus accessibles et bénéfiques pour votre santé ? Que vous soyez débutant ou sportif confirmé, cette simple activité quotidienne peut transformer votre bien-être, sans effort apparent. Découvrez pourquoi la marche à pied mérite une place centrale dans votre routine.
> 
>Premièrement, la marche à pied renforce le système cardiovasculaire. En stimulant votre rythme cardiaque de manière douce, elle améliore la circulation sanguine et réduit les risques de maladies comme l'hypertension ou les troubles du cœur. Une étude a même montré que marcher régulièrement peut équivaloir à 30 minutes de sport intensif en termes de bénéfices pour le cœur.
> 
>Ensuite, cette activité favorise la régulation du poids et la prévention des maladies métaboliques. En dépensant des calories naturellement, la marche aide à maintenir un poids sain et à équilibrer les niveaux de sucre dans le sang. Elle agit aussi comme un bouclier contre le diabète de type 2 et l'obésité.
> 
>Enfin, la marche à pied libère des endorphines, ces hormones du bonheur qui améliorent l'humeur et réduisent le stress. Une promenade quotidienne peut ainsi vous aider à mieux gérer l'anxiété et la fatigue mentale. C'est aussi un excellent moyen de se reconnecter avec la nature, ce qui a un effet apaisant sur l'esprit.
> 
>Pour commencer, commencez par intégrer la marche à pied dans votre quotidien. Même une courte promenade de 20 à 30 minutes par jour peut faire une différence significative. Commencez par des distances progressives et variez les parcours pour stimuler votre motivation. Une balade en forêt, un tour dans un parc ou une marche rapide en ville : l'important est de bouger régulièrement. Et pourquoi pas en combinant cette activité avec un moment de détente pour en faire un vrai moment de bien-être ?
>
 
---
 
### Test 3 — API avec message système, à froid
 
**Date** : 2026-10-05, 15:04 UTC — modèle non chargé, pas de cache (conditions réalistes d'un premier appel).
 
| Étape | Durée | Part du total |
|---|---|---|
| Chargement du modèle (`load_duration`) | 5,9 s | 9 % |
| Lecture du prompt (`prompt_eval_duration`) | 3,8 s (102 tokens → 26,7 tokens/s) | 6 % |
| Génération du texte (`eval_duration`) | 58,8 s | **86 %** |
| **Total** | **68,5 s** | |
 
| Mesure | Valeur |
|---|---|
| Tokens générés | 477 |
| Vitesse de génération | **8,1 tokens/s** |
| `done_reason` | `stop` |
| Longueur | ~327 mots (`wc -w`) |
 
**Évaluation du texte**
 
| Critère | Résultat |
|---|---|
| Français correct et naturel |  fluide, mais « elle **agisse** » au lieu de « elle **agit** » |
| Ton conversationnel et bienveillant |  (« Détrompez-vous ! ») |
| Mot-clé « marche à pied » |  6 occurrences |
| Longueur (~300 mots) |  ~327 mots |
| Conseil pratique final |  mais redondant (deux paragraphes de conseils, « commencez » répété) |
| Titre |  en `**gras**` |
| Structure |  « Premièrement, Ensuite, Enfin » |
| Exactitude |  « Une étude a même montré que… » : même source inventée qu'au test 2 |
| Raisonnement |  « lire un livre » en marchant ; la circulation sanguine qui améliore le sommeil |
 
**Observations**
 
- Même requête qu'au test 2, mais texte différent : le modèle n'est pas déterministe, même avec une température basse. Un seul essai ne suffit pas pour juger.
- La génération représente 86 % du temps : les seuls vrais leviers sont la longueur de l'article et la vitesse du modèle.
- Le temps de chargement (~6 s) n'existe qu'au premier appel ; Ollama décharge le modèle après quelques minutes d'inactivité (paramètre `keep_alive` pour le garder plus longtemps, au prix de la RAM occupée).
**Texte généré**
 
>text
>**La marche à pied : une révolution discrète pour votre bien-être**
> 
>Vous pensez peut-être que la marche à pied est juste une activité simple et sans enjeu ? Détrompez-vous ! Cette pratique, souvent sous-estimée, regorge de bienfaits pour votre santé physique et mentale. Que vous soyez débutant ou habitué à une activité sportive, intégrer la marche quotidienne dans votre routine peut transformer votre quotidien sans effort apparent.
> 
>Premièrement, la marche à pied renforce votre système cardiovasculaire. En stimulant votre circulation sanguine, elle améliore la qualité de votre sommeil et réduit les risques de maladies cardiovasculaires. Une étude a même montré que marcher 30 minutes par jour peut équivaloir à une séance de sport intense pour le cœur. Ensuite, elle agisse comme un excellent antidouleur naturel : elle soulage les tensions musculaires, les maux de dos et même les douleurs articulaires, surtout pour les personnes âgées.
> 
>Sur le plan mental, la marche à pied est un véritable remède contre le stress et l'anxiété. En libérant des endorphines, ces hormones du bien-être, elle améliore votre humeur et réduit le cortisol, l'hormone du stress. De plus, marcher en plein air, c'est aussi une opportunité de se reconnecter à la nature, ce qui favorise la détente et la créativité.
> 
>Enfin, la marche à pied est accessible à tous : peu importe votre âge ou votre niveau de condition physique. Elle ne demande aucun équipement coûteux et peut se faire presque partout, que ce soit dans votre quartier, en forêt ou même en ville. Pour commencer, essayez de marcher 10 à 15 minutes par jour, en augmentant progressivement la durée. L'idéal est d'associer cette activité à une activité intellectuelle, comme lire un livre ou écouter un podcast, pour en tirer encore plus de bénéfices.
> 
>Pour intégrer la marche à pied à votre quotidien, commencez par des promenades régulières après le travail ou pendant vos pauses déjeuner. Vous pouvez aussi utiliser des applications de suivi pour motiver votre progression. Commencez simplement, écoutez votre corps et savourez chaque pas – votre santé et votre esprit vous remercieront !


### Points clés pour Ministral 3 3b
 
- **Un message système personnalisé est indispensable** : il remplace le prompt par défaut (~520 tokens, environ 17 s par appel, et une consigne de demander des précisions inadaptée au plugin).
- **Temps réaliste** : environ 68 s à froid pour ~300 mots. Vitesse de génération moyenne d'environ 8,4 tokens/s (7,7 / 9,3 / 8,1).
- **Défauts récurrents** (tests 2 et 3) : titre en gras, mot-clé surutilisé (~6 fois), même source inventée, structure scolaire. → paramètre `format` pour séparer titre et contenu, consigne plus précise sur le mot-clé, relecture humaine indispensable.
- **Défauts ponctuels** : fautes de français et raisonnements bancals, différents d'un essai à l'autre.
- **Pas de mode réflexion** : aucun réglage supplémentaire nécessaire pour obtenir une réponse.
- **Conclusion provisoire** : le français est globalement fluide, mais les essais montrent des défauts récurrents : une source inventée, un mot-clé surutilisé, et quelques fautes ou absurdités. Ce sont des limites typiques d'un modèle de 3 milliards de paramètres.

## Qwen 3.5, 4b: 

### 1er test

**Date** : 2026-10-05, 18:17 UTC — modèle non chargé, pas de cache
 
| Mesure | Valeur |
|---|---|
| Durée totale | **684,2 s (11 min 24 s)** |
| Chargement | 7,2 s |
| Prompt lu | 108 tokens (0 en cache), 3,3 s → 32,4 tokens/s |
| Tokens générés | **3 988** |
| Durée de génération | 673,7 s |
| Vitesse de génération | **5,9 tokens/s** |
| `done_reason` | **`length`** (réponse coupée) |
| `content` | **vide** |
| `thinking` | très long raisonnement, en anglais |


**Observations**
 
- 108 + 3 988 = **4 096 tokens** : la fenêtre de contexte est pleine. Le modèle a consommé tout son budget en réflexion et n'a jamais écrit de réponse finale.
- Dans sa réflexion : plusieurs brouillons complets, comptage des mots à répétition, revérifications des contraintes.
- Il réfléchit **en anglais** alors que tout le prompt est en français.
- **Consigne mal interprétée** : « Tu ne poses pas de questions » (intention : ne pas demander de précisions) a été compris comme « pas de question rhétorique dans l'article ».
- **Effet de bord** : « ni Markdown » l'a conduit à supprimer le titre, par peur qu'il ressemble à du Markdown.
**Conclusion** : inutilisable en l'état sur ma machine. Hypothèse du mode réflexion confirmée par le test 2.

### 2eme test, avec le mode thinking désactiver (`think:false`)

**Date** : 2026-10-05, 18:24 UTC — modèle non chargé, pas de cache
 
| Mesure | Valeur |
|---|---|
| Durée totale | **74,9 s (1 min 15 s)** |
| Chargement | 7,1 s |
| Prompt lu | 110 tokens (0 en cache), 3,4 s → 32,0 tokens/s |
| Tokens générés | 451 |
| Durée de génération | 64,4 s |
| Vitesse de génération | **7,0 tokens/s** |
| `done_reason` | `stop` (fin normale) |
| Longueur | 309 mots (`wc -w`) |
 
**Évaluation du texte**
 
| Critère | Résultat |
|---|---|
| Français correct et naturel |  pas de faute évidente, quelques tournures maladroites (« la nature a bien mieux préparé notre corps à bouger : le simple pas ») |
| Ton conversationnel et bienveillant |  (« Imaginez… », « N'hésitez donc pas… ») |
| Mot-clé « marche à pied » |  2 occurrences, intégration naturelle |
| Longueur (~300 mots) |  309 mots |
| Conseil pratique final |  présent mais absurde (pique-nique à manger après 2-3 km), introduit par « Voici un conseil pratique », qui répète la consigne |
| Titre | ✗ aucun (non demandé explicitement dans la requête) |
| Émojis / Markdown |  aucun |
| Source inventée |  aucune |
| Affirmations douteuses |  « antidépresseur léger », « nettoie votre système digestif », « effet immédiat dès les premières heures » |
| Structure |  fluide, pas d'énumération scolaire |
 
**Texte généré**
 
> Il est facile de penser que pour rester en forme, il faut faire des efforts impressionnants ou suivre des programmes d'entraînements complexes. Pourtant, la nature a bien mieux préparé notre corps à bouger : le simple pas. La marche quotidienne n'est rien moins qu'un véritable miracle pour la santé, souvent ignoré dans notre quête de perfection sportive.
>
> Imaginez une petite randonnée tous les jours sans même vous en rendre compte. C'est exactement ce que représente une séance régulière de marche à pied. L'effet bénéfique est immédiat et palpable dès les premières heures. La fatigue physique se transforme progressivement en énergie, votre humeur s'éclaire et l'esprit devient plus clair. En marchant, vous donnez un coup de pouce crucial à votre circulation sanguine, ce qui aide le cœur à fonctionner sans effort excessif et nettoie votre système digestif naturellement.
>
> Au-delà du physique, c'est une opportunité unique pour la tête. Que vous soyez dans un parc verdoyant ou sur un trottoir encombré, le mouvement déclenche des mécanismes mentaux puissants. La marche à pied agit comme un antidépresseur léger et efficace. Elle réduit le niveau de stress accumulé lors des journées chargées et permet au cerveau de recharger ses batteries. C'est dans ces moments que les idées viennent souvent, où les angoisses s'apaisent et où la perspective prend une couleur plus positive.
>
> N'hésitez donc pas à intégrer ce geste simple dans votre quotidien, peu importe l'heure du jour. Vous n'aurez même pas besoin d'équipement coûteux. Prenez un casque de musique ou écoutez simplement le bruit de vos propres pas. Laissez votre corps et votre esprit s'épanouir grâce à cette discipline douce mais redoutablement efficace qui transforme la vie au fil des kilomètres parcourus. Voici un conseil pratique pour commencer dès aujourd'hui : préparez-vous un petit pique-nique ou une collation agréable qu'il vous faudra manger après avoir parcouru deux ou trois kilomètres, afin de rendre l'aventure encore plus satisfaisante et motivante.


### Points clés pour Qwen 3.5 4b
 
- **Le mode réflexion doit être désactivé** : 11 min 24 s sans réponse avec, 1 min 15 avec un article complet sans.
- **Fragilité** : le plugin dépend du réglage `"think": false`. S'il est oublié, ou si le comportement par défaut change, la génération devient inutilisable.
- **Hypothèse de départ à revoir** : les retours Reddit sur son français ne sont pas confirmés sur ce premier essai (un seul essai, à confirmer).
- **Contrôles nécessaires dans le plugin** : `content` vide et `done_reason` = `length` doivent être détectés et ne jamais produire de brouillon.
- **Leçons pour le message système** : une consigne ambiguë est mal interprétée (« pas de questions ») et peut avoir des effets de bord (« pas de Markdown » → pas de titre).


## Gemma3:4b 
### Test 1 — premier chargement après téléchargement
 
**Date** : 2026-10-06, 14:15 UTC — modèle non chargé, pas de cache.  Mesures de vitesse **peu fiables** : premier chargement depuis le disque, et machine probablement occupée 
 
| Étape | Durée | Part du total |
|---|---|---|
| Chargement du modèle | **20,5 s** | 22 % |
| Lecture du prompt (113 tokens) | 6,1 s → 18,6 tokens/s | 6 % |
| Génération (452 tokens) | 68,5 s → **6,6 tokens/s** | 72 % |
| **Total** | **95,1 s** | |
 
| Mesure | Valeur |
|---|---|
| `done_reason` | `stop` |
| Longueur | 303 mots (`wc -w`) |
 
**Évaluation du texte**
 
| Critère | Résultat |
|---|---|
| Français correct et naturel |  fluide, mais « **un** habitude » au lieu de « **une** habitude » ; tournures étranges (« un peu creux », titre « Filez, ça fait du bien ! ») |
| Ton conversationnel et bienveillant |  très naturel (« On a tous des jours où… ») |
| Mot-clé « marche à pied » |  7 occurrences |
| Longueur (~300 mots) |  303 mots |
| Conseil pratique final |  pertinent (commencer petit, progresser) |
| Markdown |  titre en `##`, « **Conseil pratique :** » en gras |
| Source inventée |  aucune |
| Exactitude |  affirmations générales et consensuelles |
| Structure |  fluide, pas d'énumération scolaire |
| Tics | doubles espaces après les points ; questions rhétoriques (« Saviez-vous… ? », « Alors, prêt… ? ») |
 
**Texte généré**
 
```text
## Filez, ça fait du bien ! Les bienfaits insoupçonnés de la marche à pied
 
On a tous des jours où on se sent un peu rouillés, un peu creux, et on a envie de se faire du bien. Si vous cherchez une solution simple, accessible et gratuite, vous devriez sérieusement envisager la marche à pied.  Plus qu'un simple moyen de transport, c'est un véritable cadeau que vous vous faites pour la santé et le bien-être.
 
Saviez-vous que même une courte marche quotidienne peut avoir un impact énorme sur votre vie ?  La marche à pied est une activité physique douce qui bénéficie à de nombreux aspects de votre santé.  Au niveau physique, c'est un excellent moyen de renforcer vos muscles, d'améliorer votre endurance et d'augmenter votre niveau d'énergie.  Elle aide également à contrôler votre poids, à améliorer votre circulation sanguine et à réduire le risque de maladies cardiovasculaires.
 
Mais les bienfaits de la marche à pied ne se limitent pas à la santé physique.  Elle est aussi un véritable coup de pouce pour votre santé mentale.  La marche permet de réduire le stress et l'anxiété, de stimuler la créativité et d'améliorer votre humeur.  En vous éloignant de votre environnement quotidien, vous avez l'opportunité de vous recentrer, de vous détendre et de profiter de l'air frais.
 
De plus, la marche à pied est un excellent moyen de socialiser.  Vous pouvez l'adopter en famille, avec des amis, ou même simplement en rencontrant des voisins.  C'est une occasion de briser la routine et de découvrir de nouveaux endroits près de chez vous.
 
Alors, prêt à enfiler vos chaussures ?
 
**Conseil pratique :** Commencez petit. Même 15 à 20 minutes de marche à pied par jour peuvent faire une grande différence. Fixez-vous des objectifs réalisables et augmentez progressivement la durée et l'intensité de vos séances.  L'important, c'est de commencer et de faire de la marche à pied un habitude durable !
```
 
---
 
## Test 2 — à froid, conditions normales
 
**Date** : 2026-10-06, 14:40 UTC — modèle non chargé, pas de cache.
 
| Étape | Durée | Part du total |
|---|---|---|
| Chargement du modèle | **7,2 s** | 11 % |
| Lecture du prompt (113 tokens) | 2,8 s → 40,2 tokens/s | 4 % |
| Génération (445 tokens) | 54,7 s → **8,1 tokens/s** | 85 % |
| **Total** | **64,7 s** | |
 
| Mesure | Valeur |
|---|---|
| `done_reason` | `stop` |
| Longueur | 307 mots (`wc -w`) |
 
**Évaluation du texte**
 
| Critère | Résultat |
|---|---|
| Français correct et naturel |  fluide ; « **des** courtes sessions » au lieu de « **de** courtes sessions », « coup de boost » (anglicisme familier) |
| Ton conversationnel et bienveillant |  naturel (« On le sait, on devrait bouger plus. ») |
| Mot-clé « marche à pied » |  6 occurrences |
| Longueur (~300 mots) |  307 mots |
| Conseil pratique final |  pertinent (objectif réaliste, sessions courtes au début) |
| Titre |  majuscule à chaque mot (usage anglais) : « L'Étape Facile : Pourquoi la Marche à Pied Devrait Être Votre Meilleur Ami » |
| Markdown |  de nouveau titre en `##` et « **Conseil pratique :** » en gras |
| Source inventée |  aucune |
| Exactitude |  affirmations consensuelles (cœur, tension, diabète, os, équilibre) |
 
**Texte généré**
 
```text
## L'Étape Facile : Pourquoi la Marche à Pied Devrait Être Votre Meilleur Ami
 
On le sait, on devrait bouger plus. Mais entre le travail, les enfants, les obligations… il est facile de laisser l'activité physique au second plan. Pourtant, il existe une solution simple, accessible à tous et qui offre des bienfaits incroyables : la marche à pied.
 
La marche à pied n'est pas juste un moyen de se déplacer d'un point A à un point B. C'est une véritable boite à outils pour votre santé physique et mentale. Pour commencer, la marche est un excellent exercice cardiovasculaire. Elle renforce votre cœur, améliore la circulation sanguine et aide à réguler votre pression artérielle. C'est un moyen naturel de lutter contre le risque de maladies cardiaques, le diabète de type 2 et d'autres problèmes de santé liés au mode de vie sédentaire.
 
Mais les avantages de la marche à pied ne s'arrêtent pas là. Elle est également un formidable allié pour votre bien-être mental. La marche libère des endorphines, ces hormones du bonheur qui vous donnent un coup de boost et réduisent le stress et l'anxiété. Une simple promenade dans la nature, ou même dans un quartier agréable, peut vous aider à vous ressourcer et à améliorer votre humeur.
 
Et ce n'est pas tout ! La marche à pied contribue à renforcer vos muscles et vos os, améliore votre équilibre et votre coordination. C'est une activité à faible impact, idéale pour tous les âges et tous les niveaux de forme physique. Vous n'avez pas besoin d'équipement spécifique, juste de bonnes chaussures confortables !
 
Alors, pourquoi ne pas commencer dès aujourd'hui ? Intégrez la marche à pied à votre quotidien, même de manière progressive.
 
**Conseil pratique :** Fixez-vous un objectif réaliste, comme une marche de 30 minutes par jour, et commencez par des courtes sessions si nécessaire. Le plus important est de trouver ce qui fonctionne pour vous et de vous y tenir !
```
 
---
 
## Points clés pour Gemma 3 4b
 
- **Chargement** : 20,5 s au tout premier chargement après téléchargement (fichier pas encore dans le cache disque du système), puis 7,2 s à froid en conditions normales.
- **Vitesse** : 8,1 tokens/s en conditions normales (le 6,6 du test 1 est faussé par une machine occupée). Environ 65 s pour un article de ~300 mots.
- **Systématique (2 essais sur 2)** :
  - consigne « ni Markdown » ignorée (titre `##`, conseil en gras) ;
  - mot-clé surutilisé (6 à 7 occurrences) ;
  - même plan à chaque fois : introduction, bienfaits physiques, bienfaits mentaux, question rhétorique, « **Conseil pratique :** » ;
  - aucune source inventée, affirmations prudentes et consensuelles.
- **Ponctuel** : fautes de français différentes à chaque essai (« un habitude », « des courtes sessions »), titre avec majuscules à l'anglaise.
- **Point d'attention pour le plugin** : le paramètre `format` séparera le titre, mais n'empêchera pas le Markdown à l'intérieur du champ `contenu`. À tester avec une consigne de format **positive** (décrire le format attendu plutôt qu'interdire le Markdown), ou à gérer par une conversion côté PHP.
- **Conclusion provisoire** : le contenu le plus prudent et le plus régulier des modèles testés, mais le moins respectueux de la consigne de format.

# Test — Ministral 3 8b (`ministral-3:8b`)
 
## Contexte
 
- **Rôle dans la comparaison** : point de **référence**, pas candidat. Même famille que Ministral 3 3b, mais plus gros : permet de mesurer ce que la taille apporte en qualité et ce qu'elle coûte en temps, à comportement égal.

## Test 1 — à froid
 
**Date** : 2026-10-06, 15:03 UTC — modèle non chargé, pas de cache.
 
| Étape | Durée | Part du total |
|---|---|---|
| Chargement du modèle | 11,6 s | 8 % |
| Lecture du prompt (102 tokens) | 5,7 s → 18,0 tokens/s | 4 % |
| Génération (528 tokens) | 124,1 s → **4,3 tokens/s** | 88 % |
| **Total** | **141,4 s (2 min 21 s)** | |
 
| Mesure | Valeur |
|---|---|
| `done_reason` | `stop` |
| Longueur | 380 mots (`wc -w`) |
 
**Évaluation du texte**
 
| Critère | Résultat |
|---|---|
| Français correct et naturel |  fluide, sans faute de grammaire visible ; petites maladresses (« poids santé », « sollicite… sans les solliciter trop ») |
| Ton conversationnel et bienveillant |  (« Détrompez-vous ! », « cerise sur le gâteau ») |
| Mot-clé « marche à pied » |  **3 occurrences**, intégration naturelle |
| Longueur (~300 mots) |  380 mots |
| Conseil pratique final |  le plus concret des tests (descendre un arrêt de bus plus tôt, pause déjeuner en plein air) |
| Markdown |  titre en `**gras**`, « **Conseil pratique :** » en gras |
| Source inventée |  aucune |
| Exactitude |  affirmations consensuelles ;  cite une marque (Google Fit), potentiellement datée |
| Structure |  fluide (« D'abord… Sur le plan physique… Enfin ») |
| Typographie | guillemets anglais `"…"` au lieu de « … » |
 
**Texte généré**
 
```text
**La marche à pied : un trésor quotidien pour votre bien-être**
 
Vous pensez que marcher, c'est juste une façon de vous déplacer d'un point A à un point B ? Détrompez-vous ! La marche à pied est bien plus qu'un simple exercice physique : c'est une véritable alliée pour votre santé, votre humeur et même votre esprit. Et le meilleur ? Elle ne demande aucun équipement particulier, juste une paire de bonnes chaussures et un peu de régularité.
 
D'abord, la marche à pied est un remède doux mais efficace contre le stress. En bougeant, votre corps libère des endorphines, ces hormones du bonheur qui apaisent l'anxiété et améliorent votre moral. Pas besoin de courir un marathon : une promenade de 20 à 30 minutes par jour suffit souvent à faire la différence. Et si vous marchez en pleine nature, les bienfaits sont encore amplifiés – la nature a ce pouvoir magique de calmer l'esprit.
 
Sur le plan physique, la marche renforce votre cœur, améliore votre circulation sanguine et aide à maintenir un poids santé. Elle sollicite aussi vos muscles et vos articulations sans les solliciter trop, ce qui en fait un excellent exercice pour les débutants ou les personnes en rééducation. Et cerise sur le gâteau : elle est gratuite et accessible à tous, peu importe l'âge ou la condition physique.
 
Enfin, la marche quotidienne est un excellent moyen de prendre du temps pour soi. Dans un monde où le rythme effréné nous pousse souvent à courir après le temps, s'accorder une pause pour marcher, observer les détails du paysage ou simplement écouter ses pensées peut transformer votre journée. C'est une invitation à ralentir, à respirer et à se reconnecter à soi.
 
Alors, cette semaine, pourquoi ne pas essayer de marcher un peu plus ? Pas besoin de tout changer du jour au lendemain. Commencez par de petites étapes : descendez un arrêt de bus plus tôt, faites une pause déjeuner en plein air ou explorez un nouveau quartier à pied. Votre corps et votre esprit vous remercieront.
 
**Conseil pratique :** Pour bien démarrer, fixez-vous un objectif simple comme "marcher 10 minutes par jour" et augmentez progressivement la durée. Utilisez une appli de suivi (comme Google Fit ou une simple montre) pour vous motiver et célébrer chaque petite victoire. Et surtout, écoutez votre corps : la marche doit rester un plaisir, pas une corvée !
```
 
---
 
## Ce que le 8b apprend sur l'effet de la taille
 
- **La taille corrige les défauts de contenu** observés sur le 3b : plus de source inventée, mot-clé intégré naturellement (3 fois contre 6), pas de faute de grammaire, conseils plus concrets.
- **La taille ne corrige pas le format** : le Markdown est toujours là. C'est donc un problème de **prompt**, pas de modèle → consigne de format positive et paramètre `format`.
- **Le coût est conforme à la prévision** : environ **2 fois plus lent** que le 3b (4,3 contre 8,1 tokens/s à froid ; 2 min 21 contre 1 min 08 au total). Le texte plus long (380 mots) allonge encore l'attente.
- **Conclusion** : le meilleur texte de tous les essais, mais un temps d'attente difficilement acceptable sur ma machine. Avec un GPU ou une machine plus puissante, ce serait le candidat naturel.


### Dernier test avec un nouveau requete.json plus détailler

|Élément	|Choix	|Pourquoi|
|---|---|---|
|Format de sortie	|format avec les champs titre et contenu	|La séparation est forcée par Ollama, et ne dépend plus de la bonne volonté du modèle|
|Contenu|	HTML simple : seulement \<p> et \<h2>	|WordPress stocke du HTML, donc pas de conversion. Une liste fermée de deux balises est facile à vérifier côté PHP.|
|Consignes de format|	Décrites en positif (« uniquement des balises \<p> et \<h2> »)|	Leçon des tests : interdire le Markdown ne suffit pas|
|Questions|	« sans jamais demander de précisions »	|Leçon de Qwen : « ne pose pas de questions » était ambigu|
|Mot-clé|	« deux ou trois fois, de façon naturelle »	|Un chiffre précis au lieu d'une consigne floue ; les tests montraient 6 à 7 occurrences|
|Sources|	« ne cite aucune étude, statistique ni source »	|Leçon de Ministral 3b et son étude inventée|
|Longueur|	~300 mots, fixée dans le message système	|Le temps d'attente est proportionnel à la longueur sur ta machine. |
|Message utilisateur|	Une ligne par champ du futur formulaire (sujet, mot-clé, ton, langue, consigne)|	Il simule exactement ce que le plugin construira|
|Température|	Non fixée : chaque modèle garde sa valeur recommandée	|Imposer la même valeur à tous pourrait désavantager certains modèles|

### Comparaison

## Méthode
 
**Brief de test** 

``` json
{
  "model": "gemma3:4b",
  "messages": [
    {
      "role": "system",
      "content": "Tu es rédacteur web pour une rédaction. À partir du brief fourni, tu rédiges directement un article complet d'environ 300 mots, sans jamais demander de précisions.\nTu réponds uniquement avec un objet JSON contenant deux champs :\n- \"titre\" : le titre de l'article, en texte simple ;\n- \"contenu\" : le corps de l'article en HTML, avec uniquement des balises <p> pour les paragraphes et <h2> pour les intertitres.\nRègles :\n- Écris dans la langue indiquée dans le brief et respecte le ton demandé.\n- Utilise le mot-clé deux ou trois fois, de façon naturelle.\n- Reste sur des informations générales et reconnues : ne cite aucune étude, statistique ni source.\n- Le texte ne contient ni émoji ni mise en forme autre que les balises <p> et <h2>."
    },
    {
      "role": "user",
      "content": "Sujet : les bienfaits de la marche quotidienne\nMot-clé : marche à pied\nTon : conversationnel et bienveillant\nLangue : français\nConsigne : termine par un conseil pratique"
    }
  ],
  "format": {
    "type": "object",
    "properties": {
      "titre": {
        "type": "string"
      },
      "contenu": {
        "type": "string"
      }
    },
    "required": [
      "titre",
      "contenu"
    ]
  },
  "stream": false,
  "think": false
}
```

 
## 1. Qualité — requête finale (1 essai par modèle, 2 pour Ministral)
 
| Critère | Ministral 3 3b | Qwen 3.5 4b | Gemma 3 4b |
|---|---|---|---|
| JSON valide | ✓ | ✓ | ✓ |
| Article complet |  **tronqué, 2 essais sur 2** : un seul paragraphe (64 mots), puis une suite d'espaces et de tabulations avant la fermeture du JSON | ✓ 4 paragraphes + 1 intertitre | ✓ 5 paragraphes |
| Longueur (~300 mots) |  64 mots |  209 mots |  197 mots |
| Balises autorisées (`<p>`, `<h2>`) | ✓ |  ajoute `<em>` (3 fois) |  ajoute `<b>` (3 fois) |
| HTML bien formé | ✓ | ✓ |  dernier `<p>` non fermé |
| Markdown | ✓ aucun | ✓ aucun | ✓ **aucun** (défaut principal corrigé) |
| Mot-clé (2-3 fois) | — (2 en 64 mots) | ✓ 2 (+ « marcher à pied »), mais en italique |  4, dont 3 en gras |
| Source inventée | ✓ aucune | ✓ aucune | ✓ aucune |
| Français | — trop court pour juger (« en quête… en quête ») |  dégradé : « ces gestes **légères** », « votre posture s'éclaircit », « le cadencier bat sa mesure » | ✓ fluide (« poids de corps sain » maladroit) |
| Exactitude | — |  « vous engagez tous les groupes musculaires » | ✓ consensuelle |
| Conseil pratique |  absent |  vague | ✓ pertinent (30 min par jour, marcher à deux) |
| `done_reason` | `stop` | `stop` | `stop` |
 
---
 
## 2. Fragilités par modèle
 
| Modèle | Fragilités |
|---|---|
| **Ministral 3 3b** | Source inventée récurrente. **Article tronqué avec le format imposé, 2 essais sur 2 : systématique, donc éliminatoire** avec cette requête (cause probable : la contrainte de format JSON sur un petit modèle ; piste non explorée faute de temps). Prompt système par défaut à remplacer obligatoirement. |
| **Qwen 3.5 4b** | Inutilisable sans `"think": false` : le plugin dépend d'un réglage propre à ce modèle. Le plus lent des trois. Qualité du français très variable d'un essai à l'autre (bonne au premier, dégradée au final). |
| **Gemma 3 4b** | Ajoute des balises non autorisées et peut laisser une balise non fermée. Mot-clé un peu surutilisé. Premier chargement long après installation (20 s). |
 

 
## 3. Enseignements pour le plugin (valables quel que soit le modèle)
 
- **`format` garantit la forme, pas le fond** : tous les JSON sont valides, mais Ministral produit un article tronqué à chaque essai.
- **`done_reason: stop` ne suffit pas** : l'article tronqué de Ministral se termine par `stop`. Il faut aussi vérifier une **longueur minimale** du contenu.
- **La liste de balises n'est jamais respectée à 100 %** (`<em>`, `<b>`, balise non fermée) : le plugin doit **nettoyer le HTML** côté PHP avec une liste blanche (`<p>`, `<h2>`), en retirant les balises en trop sans perdre le texte. Ce nettoyage est de toute façon nécessaire par sécurité.
- **Les consignes de format positives fonctionnent** : plus aucun Markdown chez aucun modèle.
- **Longueur en baisse** (~200 mots au lieu de ~300) avec la requête finale.
- **Prompt plus long** (233 à 256 tokens) : 5 à 7 s de lecture à froid, un coût acceptable.
- **Aucun modèle n'est fiable sur les faits** : la relecture humaine prévue par le sujet est indispensable.
- **Effet de la taille** (Ministral 8b) : meilleur contenu, mais environ 2 fois plus lent, et le format reste un problème de prompt.

 
## 4. Synthèse
 
| Modèle | Points forts | Points faibles |
|---|---|---|
| **Ministral 3 3b** | Le plus rapide ; licence Apache 2.0 ; famille déclinée en plusieurs tailles | Source inventée récurrente ; article tronqué avec le format imposé (2 essais sur 2) ; français scolaire |
| **Qwen 3.5 4b** | Mot-clé bien dosé ; pas de source inventée ; bon premier essai | Le plus lent ; dépend de `"think": false` ; français dégradé au test final |
| **Gemma 3 4b** | Le plus rapide sur un article complet au test final ; contenu prudent ; plan régulier ; Markdown corrigé par la consigne positive | Balises en trop, HTML parfois mal fermé ; mot-clé un peu surutilisé |
 
**Choix retenu** : Gemma3:4b
 
**Justification** : Je choisis de garder le modèle Gemma 3, car parmi les petits modèles, il est le plus rapide, avec des réponses assez proches de ce qui est demandé. Il a aussi la syntaxe la plus naturelle et agréable à lire, ce qui est selon moi un point important pour la rédaction d'articles. Ses défauts sont facilement corrigeables par un être humain. Avec plus de tests et dans des configurations différentes, je pourrais obtenir de meilleurs résultats et peut-être changer d'avis, mais ici, par manque de temps, j'écarte les deux autres modèles : Qwen a un français discutable et dépend trop de la désactivation du paramètre think. Ma première intuition était de partir sur Ministral 3, mais mes tests m'ont montré que le modèle avait trop souvent des problèmes (article tronqué, mauvaises formulations, sources inventées).

# Choix de l'appel à l'api :

|Même serveur (mon cas)|	Ollama et Wordpress tout deux installé sur ma machine |	Simple ; rien n'est exposé sur le réseau ; appel HTTP depuis le serveur (PHP), pas depuis le navigateur	| Le serveur doit être puissant; il ne taite qu'une requete à la fois donc pendant la génération l'admin sera bloqué|
|---|---|---|---|
|Serveur LLM dédié|	Ollama sur une autre machine (avec GPU), appelée via son adresse sur le réseau interne|	Le site reste léger|Il faut sécuriser : Ollama n'a aucune authentification par défaut, il ne doit jamais être exposé sur Internet (réseau privé, pare-feu, ou serveur intermédiaire avec authentification et HTTPS); pas possible dans on cas, je nais pas de deuxième serveur/machine|
|API cloud (OpenAI, Anthropic, Mistral…)	|Appel à l'API d'un fournisseur, avec une clé|	Rapide, puissant, aucune machine à gérer|	Payant à l'usage ; les textes sortent de l'entreprise (confidentialité, RGPD) ; dépendance au fournisseur ; une clé secrète à protéger; pas demandé pour ce test|
|Hébergeurs de modèles ouverts	|Des services qui font tourner des modèles open source (Llama, Mistral, Gemma…) et les exposent via une API	|Entre les deux : modèles ouverts, sans machine à gérer	|Mêmes questions de coût et de confidentialité que le cloud|