# Rapport - Labo sans fil
## Introduction
Dans ce laboratoire, nous nous sommes intéressés à la transmission et au traitement de données dans un contexte IoT à l'aide du réseau LoRaWAN et de la plateforme TTN (The Things Network).

L'objectif était de comprendre le fonctionnement complet d'une chaîne de communication, depuis lenvoi de données par un capteur jusquà leur réception, leur décodage et leur exploitation sur un serveur. Nous avons notamment observé les messages uplink, mis en place un décodeur de payload, puis configuré un webhook HTTP afin de recevoir les données dans un conteneur.

Nous avons ensuite développé un script PHP permettant de traiter, stocker et afficher les données reçues, ainsi que d'identifier des situations anormales. Enfin, nous avons comparé différents formats de transmission (JSON et Protocol Buffers) afin d'évaluer leur impact sur l'exploitation des données.

Ce laboratoire s'inscrit dans la continuité des notions vues en réseau et en développement web, et permet d'illustrer concrètement le fonctionnement d'une architecture IoT complète, depuis la communication sans fil jusqu'au traitement côté serveur.

## Manipulations et observations
### Visualisation TTN et identification des gateways LoRaWAN
Après l'authentification sur le site TTN, nous sommes arrivés sur le dashboard de notre capteur LoRaWAN. Nous avons sélectionné l'onglet "Live data" du dashboard, puis appuyé sur le bouton du capteur afin de visualiser notre première donnée transmise.

![Première donnée transmise](img/2.png)

Nous observons les étapes de traitement de l'uplink:
1. Forward uplink: réception du message
2. Decode uplink: tentative de décodage (échoue, pas de décodeur)
3. Schedule downlink: éventuelle réponse planifiée
4. Successfully processed: fin du traitement

En cliquant sur une de ces étapes, cela ouvre le détail de cette dernière au format JSON. C'est ici que nous pouvons voir par quel gateway notre message passe.

![Gateway dans le détail de l'étape](img/2gateway.png)

Dans notre cas, nous pouvons lire que notre message passe par un gateway de la HE-Arc.

### Test de simulation des données

Dans l'onglet "Messaging", puis la section "Simulated uplink" du dashboard, nous pouvons simuler un message qui pourrait être envoyé par le capteur. C'est pratique quand l'appareil physique n'est pas  à portée de main.


![Exemple de simulation d'uplink](img/3.png)

Ensuite, nous pouvons vérifier dans l'onglet "Live data" que nous recevons bien un message, comme s'il provenait du capteur.

### Test et optimisation d'un décodeur JavaScript TTN
Nous avons ensuite mis en place un décodeur afin de "décrypter" les messages en hexadécimal. Nous sommes repartis de l'exemple fourni du cours.

Avec cette base, les messages en hexadécimal deviennent un format JSON qui nous retourne des informations sur la batterie:

![Résultats du décodeur de base](img/4base.png)

Puis nous l'avons complété en suivant le commentaire en haut du code.

Lien vers le code complet: [code/payload-decode.js](code/payload-decode.js)

Avec le décodeur complété nous pouvons maintenant voir en plus les informations du capteur (humidité et température):

![Résultats du décodeur complété](img/4complete.png)

### Accès au conteneur ALPHANET-DS
Après la connexion à la machine distante via SSH, nous avons mis à jour le système du conteneur, installé netcat et ouvert un serveur TCP sur le port 80 grâce à la commande `nc`.

Ensuite, depuis un navigateur web, nous sommes allés sur l'adresse http://193.72.186.153:8087/script.php.
Nous voyons bien que le serveur ouvert précédemment a bien reçu une requête GET avec les entêtes client (User-Agent, Host, Accept, etc).

![Serveur TCP ouverture et réception](img/5.png)

### Intégration Webhook HTTP dans ALPHANET-DS
#### Installation de PHP et déploiement de l'application

Nous avons installé PHP en mode standalone. Nous avons ensuite créé un répertoire `/html` afin d'héberger les fichiers de l'application. Nous avons ensuite lancé le serveur web intégré de PHP pour servir le contenu du répertoire courant.

Ce serveur minimal permet de tester rapidement une application.

Un script PHP de base nous a été fourni, nous l'avons déployé sur le conteneur à l'aide d'une commande scp.

Afin de simuler le comportement d'une future intégration avec TTN, nous avons testé notre script PHP en envoyant une requête HTTP POST.

![Test serveur php minimal](img/6base.png)

#### Intégration Webhook avec TTN

Sur le site TTN, nous avons créé une nouvelle intégration webhook qui renseigne le lien de notre script PHP.

En allant via un navigateur sur notre script, nous pouvons voir ce message dans notre serveur php, ce qui nous intéresse c'est le payload:

![Test webhook without payload formatter](img/6withoutpayloadformatter.png)

Le payload ci-dessus reçu n'était pas exploitable directement. Nous avions toutefois déjà effectué des tests avec un payload formatter. Nous l'avons activé.

Maintenant, les données deviennent interprétables et peuvent être lues au format JSON.

![Test webhook with payload formatter](img/6withpayloadformatter.png)

### Affichage des données

Après la mise en place du webhook et du déploiement du script PHP sur le conteneur, nous avons mis en place l'affichage des données reçues.

Le script récupère les messages envoyés par TTN via une requête HTTP POST. Les données sont extraites depuis le payload JSON et stockées dans un fichier local afin de conserver la dernière valeur reçue.

Ces informations sont ensuite affichées sur une page web générée en PHP. Les valeurs de température, d'humidité et de batterie sont directement visibles dans le navigateur.

![Affichage des données](img/7.png)

La page se met à jour automatiquement toutes les 10 secondes afin de permettre un suivi quasi temps réel des données du capteur.

### Gestion des alertes
En complément de l'affichage des données brutes, nous avons ajouté un système d'alertes afin d'interpréter les valeurs reçues et détecter des situations anormales.

Le script PHP récupère les données capteurs (température, humidité et tension de batterie) et les compare à des seuils définis. Si une valeur sort de la plage considérée comme normale, une alerte correspondante est générée.

![Gestion des alertes](img/8.png)

Les alertes générées sont ensuite affichées dynamiquement sur la page web sous forme de liste. Cela permet une lecture rapide de l'état du système sans analyser directement les valeurs numériques.

### Webhook JSON vs Protocol Buffers
Afin d'obtenir des logs exploitables, nous sommes revenus au script PHP de base et avons désactivé le payload formatter.

Nous avons ensuite simulé un uplink depuis TTN afin d'observer la requête POST reçue par notre serveur. Dans un premier temps, nous avons utilisé le format JSON (format par défaut), puis nous avons testé le format Protocol Buffers.

![TTN changer le format du Webhook](img/9jsontopb.png)

En format JSON, les données sont lisibles et structurées. Le payload est présent dans le champ frm_payload. Bien que ce contenu ne soit pas directement interprétable, il reste facilement décodable et exploitable.

En revanche, en format Protocol Buffers, les données reçues apparaissent sous forme binaire. Elles ne sont pas lisibles dans le terminal et ne peuvent pas être traitées directement par le script PHP.

![Terninal JSON vs Protocol Buffers](img/9compare.png)

Nous en déduison que le format JSON est plus adapté dans notre cas, car il permet une manipulation simple des données, contrairement au format Protocol Buffers qui nécessite un décodage spécifique. Cependant, ce dernier est plus compact et optimisé pour la transmission, ce qui le rend plus efficace côté machine.

## Conclusion
Au cours de ce laboratoire, nous avons observé le fonctionnement d'un capteur LoRaWAN via TTN, notamment la réception des messages uplink et leur traitement.

Nous avons mis en place un décodeur permettant de transformer les données brutes en informations exploitables (température, humidité et batterie). Nous avons également utilisé un conteneur pour héberger un serveur recevant ces données via un webhook HTTP.

Les données reçues ont ensuite été traitées par un script PHP, stockées localement, puis affichées sur une interface web avec une mise à jour automatique. Un système d'alertes a également été ajouté afin de signaler les valeurs anormales.

Enfin, nous avons comparé les formats JSON et Protocol Buffers pour la transmission des données. Nous avons constaté que le format JSON est directement exploitable dans notre cas, tandis que le format Protocol Buffers nécessite un traitement supplémentaire car il est binaire.

## Annexes
[Payload formatter](code/payload-decode.js)

[script.php](code/script.php)