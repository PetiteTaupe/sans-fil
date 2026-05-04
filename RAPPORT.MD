https://gitlab-etu.ing.he-arc.ch/isc/documentation/docs/-/wikis/recommandations-rapports-labo
# Rapport - Labo sans fil
## Introduction
à faire a la fin
## Manipulations et observations
### Visualisation TTN et identification des gateways LoRaWAN
Après l'authentification sur le site TTN, nous somme arrivés sur le dashboard de notre capteur LoRaWAN. Nous avons sélectionné l'onglet "Live data" de la dashboard, puis appuyé sur le bouton du capteur afin de visualiser notre première donnée transmise.

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

Dans l'onglet "Messaging", puis la section "Simulated uplink" de la dashboard, nous pouvons simuler un message qui pourrait être envoyé par le capteur. C'est pratique quand l'appareil physique n'est pas  à porté de main.


![Exemple de simulation d'uplink](img/3.png)

Ensuite, nous pouvons vérifier dans l'onglet "Live data" que nous recevons bien un message, comme s'il provenait du capteur.

### Test et optimisation d’un décodeur JavaScript TTN
Nous avons ensuite mis en place un décodeur afin de "décrypter" les messages en hexadécimal. Nous sommes repartis de l'exemple fourni:

https://gitlab-etu.ing.he-arc.ch/isc/2025-26/niveau-2/2254-2-reseaux-et-application/-/blob/main/03_Sans-fil/code/payload-decode.js?ref_type=heads

Avec cette base, les messages en hexadécimal deviennent un format JSON qui nous retourne des informations sur la batterie:

![Résultats du décodeur de base](img/4base.png)

Puis nous l'avons complété en suivant le commentaire en haut du code.

Lien vers le code complet: [code/payload-decode.js](code/payload-decode.js)

Avec le décodeur complété nous pouvons maintenant voir en plus les informations du capteur (humidité et température):

![Résultats du décodeur complété](img/4complete.png)
