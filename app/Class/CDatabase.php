<?php
/**
 * CDatabase - Wrapper CodeIgniter 4 Database
 *
 * Fournit une interface simplifiée pour les opérations
 * courantes de base de données en s'appuyant sur la connexion
 * brute CodeIgniter 4.
 */
namespace App\Class;

class CDatabase
{
    /**
     * Instance du connecteur de base de données sous-jacent
     */
    protected $db;

    /**
     * Nom du groupe de connexion utilisé
     */
    protected $connectionGroup;

    /**
     * Construire le wrapper et obtenir automatiquement la connexion DB
     *
     * @param string|null $connectionGroup Groupe de connexion à utiliser (null = défaut)
     */
    public function __construct(?string $connectionGroup = null)
    {
        // Utiliser db_connect() pour obtenir la connexion CodeIgniter
        $this->db = \Config\Database::connect($connectionGroup);
        $this->connectionGroup = $connectionGroup ?? config('Database')->defaultGroup;
    }

    /**
     * Obtenir la connexion brute PDO
     *
     * @return \PDO
     */
    public function getConnection(): \PDO
    {
        return $this->db->getConnection();
    }

    /**
     * Exécuter une requête SQL
     *
     * @param string $query Requête SQL
     * @param array|mixed|null $params Paramètres de liaison
     * @return \PDOStatement|false
     */
    public function query(string $query, ...$params)
    {
        if ($params) {
            $query = vsprintf($query, $params);
        }
        log_message('debug', 'CDatabase query: '.$query);
        return $this->db->query($query);
    }

    /**
     * Exécuter une requête sans résultat
     *
     * @param string $query Requête SQL
     * @param array|null $params Paramètres de liaison
     * @return bool
     */
    public function exec(string $query, ?array $params = null): bool
    {
        if ($params === null) {
            return $this->db->query($query);
        }
        $query = vsprintf($query, $params);
        return $this->db->execute($query);
    }

    /**
     * Obtenir toutes les lignes d'une requête
     *
     * @param string $query Requête SQL
     * @return array
     */
    public function getAll(string $query, ... $params): array
    {
        $stmt = $this->query($query, $params);
        if ($stmt === false) {
            return [];
        }

        return $stmt->getResult('array');
    }

    /**
     * Obtenir une seule ligne
     *
     * @param string $query Requête SQL
     * @param mixed ...$params Paramètres de liaison
     * @return array|false
     */
    public function getRow(string $query, ... $params)
    {
        if ($params) {
            $query = vsprintf($query, $params);
        }
        $stmt = $this->query($query, $params);
        if ($stmt === false) {
            return false;
        }
        return $stmt->getRow('array');
    }

    /**
     * Obtenir une seule valeur
     *
     * @param string $query Requête SQL
     * @return mixed|false
     */
    public function getOne(string $query, ... $params)
    {
        $stmt = $this->query($query, $params);
        if ($stmt === false) {
            return false;
        }

        $row = $stmt->getRow('array');
        return $row ? array_values($row)[0] : false;
    }

    /**
     * Obtenir le nombre de lignes affectées
     *
     * @return int
     */
    public function getAffectedRows(): int
    {
        return $this->db->affectedRows();
    }

    /**
     * Obtenir l'ID de la dernière insertion
     *
     * @return int
     */
    public function getLastInsertId(): int
    {
        return $this->db->insertID();
    }

    /**
     * Démarrer une transaction
     *
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->db->transStart();
    }

    /**
     * Valider une transaction
     *
     * @return bool
     */
    public function commit(): bool
    {
        return $this->db->transComplete();
    }

    /**
     * Annuler une transaction
     *
     * @return bool
     */
    public function rollback(): void
    {
        $this->db->transRollback();
    }

    /**
     * Vérifier le statut de la transaction
     *
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->db->transStatus() === false;
    }

    /**
     * Échapper une chaîne pour la base de données
     *
     * @param string $value Valeur à échapper
     * @return string
     */
    public function escape(string $value): string
    {
        return $this->db->escapeString($value);
    }

    /**
     * Échapper une chaîne LIKE
     *
     * @param string $value Valeur à échapper pour LIKE
     * @return string
     */
    public function escapeLikeString(string $value): string
    {
        return $this->db->escapeLikeString($value);
    }

    /**
     * Fermer la connexion
     *
     * @return void
     */
    public function close(): void
    {
        $this->db->close();
    }

    /**
     * Vérifier si la connexion est active
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->db->connect() !== false;
    }

    /**
     * Obtenir le nom du driver de base de données
     *
     * @return string
     */
    public function getDriver(): string
    {
        return $this->db->DBDriver;
    }

    /**
     * Obtenir le nom de la base de données
     *
     * @return string
     */
    public function getDatabaseName(): string
    {
        return $this->db->database;
    }

    /**
     * Obtenir le hostname de la connexion
     *
     * @return string
     */
    public function getHostname(): string
    {
        return $this->db->hostname;
    }

    /**
     * Obtenir les erreurs de la base de données
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->db->getErrors();
    }

    /**
     * Méthode statique pour obtenir l'instance DB (compatibilité avec le code existant)
     *
     * @return CDatabase
     */
    public static function DB(): self
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }

    /**
     * Formater une chaîne pour SQL (échappement)
     *
     * @param string|null $val Valeur à formater
     * @return string
     */
    public function STRING(?string $val): string
    {
        if ($val === null) return "null";
        return "'" . $this->escape($val) . "'";
    }

    /**
     * Formater un mot de passe (hashage BCRYPT)
     *
     * @param string $val Mot de passe à hacher
     * @return string
     */
    public function PASSWORD(string $val): string
    {
        return "'" . password_hash($val, PASSWORD_BCRYPT) . "'";
    }

    /**
     * Exécuter une requête et obtenir un seul résultat
     *
     * @param string $query Requête SQL avec placeholders
     * @param mixed ...$args Arguments pour vsprintf
     * @return mixed|false
     */
    public function oneValue(string $query, ...$args)
    {
        if ($args) {
            $query = vsprintf($query, $args);
        }
        $stmt = $this->query($query);
        if ($stmt === false) {
            return false;
        }
        $row = $stmt->getRow('array');
        return $row ? $row[0] : false;
    }

    /**
     * Exécuter une requête et obtenir des résultats indexés par clé
     *
     * @param string $query Requête SQL avec placeholders
     * @param mixed ...$args Arguments pour vsprintf
     * @return array
     */
    public function keyedRows(string $query, ...$args): array
    {
        if ($args) {
            $query = vsprintf($query, $args);
        }
        $stmt = $this->query($query);
        if ($stmt === false) {
            return [];
        }
        $results = [];
        while ($row = $stmt->getUnbufferedRow('array')) {
            log_message('debug', 'CDatabase keyedRows row: '.json_encode($row));
            $results[] = $row;
        }
        return $results;
    }

    /**
     * Obtenir le nombre de lignes
     *
     * @param string $query Requête SQL
     * @return int
     */
    public function getNumRows(string $query): int
    {
        $stmt = $this->query($query);
        if ($stmt === false) {
            return 0;
        }
        return $stmt->rowCount();
    }

    /**
     * Formater une date pour SQL
     *
     * @param int $d Timestamp
     * @return string
     */
    public function DATE(int $d): string
    {
        if (!$d) return "null";
        return strftime("'%Y-%m-%d'", $d);
    }

    /**
     * Formater un datetime pour SQL
     *
     * @param int $d Timestamp
     * @return string
     */
    public function DATETIME(int $d): string
    {
        if (!$d) return "null";
        return date("'Y-m-d H:i:s'", $d);
    }

    /**
     * Formater un tableau d'entiers pour SQL
     *
     * @param array $arr Tableau d'entiers
     * @return string
     */
    public function ARRAY_INT(array $arr): string
    {
        if (!$arr) return '()';
        $r = "";
        foreach ($arr as $a) {
            if ($r != "") $r .= ",";
            $r .= $a;
        }
        return "(" . $r . ")";
    }

    public function ID($val): string
    {
        if ($val === null) return "null";
        return (string) ((int) $val);
    }
    public function INT($val): string
    {
        return (string) ((int) $val);
    }
    public function FLOAT($val): string
    {
        return (string) ((float) $val);
    }

}
