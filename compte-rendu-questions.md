# Reponse aux questions du compte-rendu
## 1. Relier deux LAN Ethernet via un bridge WIFI

### a) Est-ce normal ? Que se passe-t-il probablement ? Quel est le risque éventuel ?

#### Est-ce normal?
Non.

Un vrai bridge en couche 2 devrait relayer les trames mais conserver l'adresse MAC source originale de l'expéditeur.

#### Que se passe-t-il probablement?

Les access points en mode "bridge" fonctionnent plutôt en mode **relais avec réécritureMAC** (MAC rewriting/source MAC replacement):

1. Du côté LAN1: L'Access point reçoit une trame d'une machine (ex: MAC_A)
2. En transit: Au lieu de conserver MAC_A, l'AP **remplace l'adresse MAC source par sa propre adresse MAC** (MAC_AP1)
3. Du côté LAN2: Toutes les machines du LAN1 apparaissent avec l'adresse MAC de l'AP du LAN1

Ce qui expliquerait pourquoi toutes les machines du LAN1 possèdent la même adresse : ce serait celle de l'Access point.

Cependant, tout fonctionne "normalement" car TCP/IP utilise les **adresses IP** (couche 3), pas les adresses MAC, pour le routage général. On aura des problèmes en cas d'utilisation de protocole dépendant des adresses MAC.

#### Risques éventuels?

**Perte d'information d'adressage**
- Il est impossible d'identifier la véritable machine source du LAN1 à partir du LAN2.
- Les adresses MAC originales sont perdues.

**Problèmes avec ARP**
- Les tables ARP ne reflètent pas la topologie réelle
- Une machine du LAN2 qui veut faire un ping à une machine du LAN1 doit passer par l'AP, pas directement. Il y a donc un risque d'**incohérence ARP** si les réponses ARP ne sont pas cohérentes avec le flux de données

**Problèmes de sécurité**
- Certains filtres / politiques de sécurité ne s'exécuteraient pas, car on pourrait penser que tout vient du même AP à cause de l'adresse MAC identique

## b) Consultez le format de trame 802.11 et expliquez comment les 4 adresses devraient être utilisées, si cette fonction bizarre n'était pas activée

Une trame 802.11 contient jusqu'à **4 champs d'adresse MAC**, dont l'utilisation dépend de deux flags :
- **To DS** (To Distribution System) : 1 = la trame est destinée au système de distribution (AP)
- **From DS** (From Distribution System) : 1 = la trame provient du système de distribution (AP)

![alt text](img/format_trame_802_11.jpg)

#### Utilisation des 4 adresses selon To DS / From DS :

| To DS | From DS | Addr1 | Addr2 | Addr3 | Addr4 | Contexte |
|-------|---------|-------|-------|-------|-------|----------|
| 0 | 0 | DA | SA | BSSID | N/A | Ad-hoc, infra client→AP |
| 0 | 1 | DA | BSSID | SA | N/A | AP → client |
| 1 | 0 | BSSID | SA | DA | N/A | Client → AP (distribution) |
| 1 | 1 | Rcv AP | Transmit AP | DA | SA | **WDS/Bridge** ← Mode bridge transparent |   

On remarque que le mode "ad-hoc" est utilisé quand les deux champs "To DS" et "from DS" sont à 0, et que le mode bridge se passe quand les deux champs sont à 1.

C'est le **mode WDS/bridge transparent** qui devrait être utilisé pour un vrai bridge 802.11. Cela permettrait de préserver à la fois l'identité des APs (sans fil) et l'identité des machines Ethernet originales (câblées).

Ce n'est pas le cas avec la configuration utilisée ici: les APs simples en mode "bridge" n'utilisent pas le mode 4 adresses, mais le mode **(1,0)**.

