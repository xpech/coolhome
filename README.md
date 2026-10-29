# coolhome

## Partage des maisons

Appliquer la migration avant de deployer cette version :

```sh
php spark migrate
```

Configurer le serveur SMTP avec les variables `mail.*` de `.env.example`
(ou la configuration historique `application/config/mail.yml`) pour l'envoi
par email. Le SMTP est facultatif : le lien d'invitation est aussi affiche
avec un bouton **Copier le lien**. Si aucun SMTP n'est configure ou si l'envoi
echoue, l'invitation reste valide et peut etre transmise manuellement.
`app.baseURL` doit correspondre a l'URL publique du site pour les liens envoyes.

Dans une maison, **Partager / gerer les membres** permet aux Owners d'inviter
une personne par email, de changer son role, de retirer son acces et d'annuler
une invitation.

- **Owner** : gestion complete de la maison, de ses pieces, des appareils qui
  y sont rattaches et du partage.
- **Habitant** : consultation, modification des modes, des temperatures et des
  programmes (horaires inclus), sans modification de la structure ni des membres.
- L'Owner initial conserve son acces permanent : une maison garde toujours
  au moins un Owner. Les maisons existantes conservent leur proprietaire.

Une invitation est valable **7 jours**, liee a l'email invite et utilisable
une seule fois. Un nouvel envoi remplace le lien precedent. La personne se
connecte avec cet email si elle possede deja un compte ; sinon elle choisit
un mot de passe directement depuis l'invitation. L'acceptation ajoute la
maison a sa liste sans creer une maison personnelle supplementaire.
Un habitant ne peut pas administrer un appareil rattache a la maison,
meme si cet appareil etait initialement enregistre sur son compte.

### Tests du partage

```sh
composer test
```

Les tests MySQL utilisent les parametres `database.default.*` pour creer une
base ephemere `coolhome_test_<aleatoire>`, puis la supprimer en fin de suite.
Le compte MySQL de developpement doit donc avoir les droits CREATE/DROP DATABASE.
Aucune table de la base applicative n'est modifiee et aucun email reel n'est envoye.

## Composant list

* Wemos D1 min https://www.amazon.fr/dp/B0754N794H/ref=cm_sw_em_r_mt_dp_Z94774CJWAAM69HYJPQ6?_encoding=UTF8&psc=1
* DHT11 https://www.amazon.fr/dp/B07WQZWXZ5/ref=cm_sw_em_r_mt_dp_P7Z57AHRXDR10R0AZ7DP
* DS18B20 Capteur de température numérique : https://www.amazon.fr/dp/B01LXQF9B5/ref=cm_sw_em_r_mt_dp_M2SS16485VXC5N1XPH55
* connecteur electrique 400V : https://fr.rs-online.com/web/p/borniers-pour-circuits-imprimes/1753943P/
* HLK-PM01 Module abaisseur de tension AC/DC 220 V à 5 V : https://www.amazon.fr/dp/B01768OJMS/ref=cm_sw_em_r_mt_dp_NNH650YKW9K4QJ47G0YZ?_encoding=UTF8&psc=1
* relais Omron G2RL Relais : https://fr.rs-online.com/web/p/relais-sans-accrochage/3650529/
* resistances
* 2 diodes
* Transistors Bipolaire NPN 2N2222A https://www.amazon.fr/dp/B016SB4P4Q/ref=cm_sw_em_r_mt_dp_ZWZA7TFX1PRRQXRXFVNF
