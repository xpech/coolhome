# Migration CDatabase vers CodeIgniter 4

## Résumé des changements

Le wrapper CDatabase a été migré pour utiliser la connexion CodeIgniter 4 via `db_connect()` au lieu de PDO direct.

### Fichier principal
- **Emplacement**: `app/Libraries/CDatabase.php`
- **Namespace**: `App\Libraries`

### Fonctionnalités clés

1. **Connexion via db_connect()**
   - Utilise `\Config\Database::connect()` pour obtenir la connexion
   - Compatible avec tous les groupes de connexion configurés

2. **Méthodes principales**
   - `getConnection()`: Retourne la connexion PDO brute
   - `query(string $sql, ?array $params)`: Exécute une requête SQL
   - `exec(string $sql, ?array $params)`: Exécute une requête sans résultat
   - `getAll(string $sql, ?int $fetchMode)`: Obtient tous les résultats
   - `getRow(string $sql, ?int $fetchMode)`: Obtient une seule ligne
   - `getOne(string $sql)`: Obtient une seule valeur

3. **Transactions**
   - `beginTransaction()`: Démarre une transaction
   - `commit()`: Valide la transaction
   - `rollback()`: Annule la transaction
   - `inTransaction()`: Vérifie si une transaction est en cours

4. **Méthodes de compatibilité (pour le code existant)**
   - `DB()`: Méthode statique pour obtenir l'instance (compatibilité avec CDatabase::DB())
   - `STRING(string $val)`: Formate une chaîne pour SQL
   - `PASSWORD(string $val)`: Hache un mot de passe BCRYPT
   - `oneValue(string $sql, ...$args)`: Obtient un seul résultat
   - `keyedRows(string $sql, ...$args)`: Obtient des résultats indexés
   - `getNumRows(string $sql)`: Compte les lignes
   - `DATE(int $timestamp)`: Formate une date SQL
   - `DATETIME(int $timestamp)`: Formate un datetime SQL
   - `ARRAY_INT(array $arr)`: Formate un tableau d'entiers

5. **Utilitaires**
   - `escape(string $value)`: Échappe une valeur
   - `escapeLikeString(string $value)`: Échappe pour LIKE
   - `getAffectedRows()`: Nombre de lignes affectées
   - `getLastInsertId()`: ID de la dernière insertion
   - `isConnected()`: Vérifie la connexion
   - `getDriver()`, `getDatabaseName()`, `getHostname()`: Infos de connexion
   - `getErrors()`: Obtient les erreurs

### Configuration requise

Le fichier `app/Common.php` charge automatiquement le wrapper:
```php
if (! class_exists('CDatabase')) {
    $ci4DbCompatPath = APPPATH . 'Libraries/CDatabase.php';
    if (is_file($ci4DbCompatPath)) {
        require_once $ci4DbCompatPath;
    }
}
```

### Utilisation

```php
// Méthode statique (compatibilité avec le code existant)
$db = CDatabase::DB();

// Méthode directe
$db = new \App\Libraries\CDatabase();

// Exemples d'utilisation
$results = $db->query("SELECT * FROM users WHERE id = ?", [1]);
$count = $db->oneValue("SELECT COUNT(*) FROM users");
$user = $db->keyedRows("SELECT * FROM users WHERE email = ?", "test@example.com");

// Méthodes de compatibilité
$escaped = $db->STRING("Test");
$hashed = $db->PASSWORD("password123");
```

### Notes importantes

- **Pas de Query Builder**: Comme demandé, le wrapper n'utilise pas le Query Builder de CodeIgniter
- **Connexion brute**: Accès direct à la connexion PDO via `getConnection()`
- **Compatibilité**: Toutes les méthodes utilisées dans le code existant sont disponibles
- **Transactions**: Gestion des transactions via les méthodes dédiées

### Fichiers modifiés/ajoutés

- `app/Libraries/CDatabase.php` (nouveau)
- `app/Common.php` (modifié pour charger le wrapper)

### Fichiers obsolètes

- `application/models/cdatabase.php` (ancienne version avec PDO direct)
  - Ce fichier contient l'ancienne implémentation qui utilisait PDO directement
  - Il n'est plus utilisé par le nouveau wrapper

### Migration du code existant

Aucune modification du code existant n'est nécessaire car:
1. La méthode statique `CDatabase::DB()` est disponible
2. Toutes les méthodes utilisées dans l'application sont préservées
3. Le wrapper utilise la même API que l'ancienne version

### Tests

Le wrapper a été testé pour s'assurer que:
- Toutes les méthodes sont disponibles
- La méthode `DB()` est statique
- Le namespace est correct (`App\Libraries`)
- La classe est bien chargée par Common.php

## Résolution des problèmes

Si vous rencontrez des problèmes:
1. Vérifiez que `app/Libraries/CDatabase.php` existe
2. Assurez-vous que `app/Common.php` est chargé avant l'utilisation de CDatabase
3. Vérifiez la configuration de la base de données dans `app/Config/Database.php`