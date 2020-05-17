<?php declare(strict_types=1);


namespace Useful\DataStore\Driver;


use Useful\DataStore\Repository\QueryException;
use Useful\DataStore\Repository\UnexpectedResultException;
use Exception;
use PDO;

class PdoDriver implements DriverInterface
{
    /**
     * @var PDO $pdo Valid PDO instance or null.
     */
    private PDO $pdo;

    /**
     * @var array $preparedStatements Contains an array of PDO prepared statements.
     */
    private array $preparedStatements = [];

    public const SORT_ASCENDING = 'ASC';
    public const SORT_DESCENDING = 'DESC';

    public const GARBAGE_COLLECTION = false;


    /**
     * {@inheritdoc}
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return PDO A PDO Object instanced with the DataStore Configuration.
     */
    final public function getPdo(): PDO
    {
        return $this->pdo;
    }


    /**
     * Fetches a single row from the DataStore
     *
     * @param string $sql A prepared SQL statement.
     * @param array $bind Values to bind to the prepared statement.
     * @return array|null Returns an array representing a single row from the DataStore or null if none found.
     * @throws UnexpectedResultException If the query returned more than one row.
     * @throws QueryException If the query failed
     */
    final public function simpleFetchOne(string $sql, array $bind = []): ?array
    {
        $retVal = $this->simpleFetch($sql, $bind);

        if (empty($retVal)) {
            return null;
        }

        if (count($retVal) > 1) {
            throw new UnexpectedResultException(
                'SIMPLE_FETCH_ONE_RETURNED_MORE_THAN_ONE_RESULT',
                [
                    'sql' => $sql,
                    'bind' => $bind,
                    'return' => $retVal
                ]);
        }

        return $retVal[0];
    }

    /**
     * Fetches a single row from the DataStore
     *
     * @param string $sql A prepared SQL statement.
     * @param array $bind Values to bind to the prepared statement.
     * @return array|null Returns an array of rows from the DataStore. Returns an empty array if no rows found.
     * @throws QueryException If the query resulted in an Exception.
     */
    final public function simpleFetch(string $sql, array $bind = []): array
    {
        try {
            $query = $this->prepareBind($sql, $bind);
            $this->prepare($query['sql']);
            $rows = $this->fetch($query['bind']);
        } catch (Exception $e) {
            throw new QueryException(
                'SIMPLE_QUERY_EXCEPTION',
                [
                    'sql' => $sql,
                    'bind' => $bind,
                    'exception' => $e
                ],
                $e);
        }

        return $rows;
    }

    /**
     * Executes an SQL query without returning a result set.
     *
     * @param string $sql A prepared SQL statement.
     * @param array $bind Values to bind to the prepared statement.
     * @return array|null Returns an array of query result data.
     * @throws QueryException If the query resulted in an Exception.
     */
    final public function simpleExecute(string $sql, array $bind = []): array
    {
        try {
            $query = $this->prepareBind($sql, $bind);
            $this->prepare($query['sql']);
            $return = $this->execute($query['bind']);
        } catch (Exception $e) {
            throw new QueryException(
                'SIMPLE_EXECUTE_EXCEPTION',
                [
                    'sql' => $sql,
                    'bind' => $bind,
                    'exception' => $e
                ],
                $e);
        }

        return $return;
    }

    /**
     * Prepares an SQL Statement
     *
     * @param string $sql A prepared SQL statement.
     * @param string $identifier An identifier to disambiguate between multiple concurrent queries
     * @return PdoDriver
     * @throws QueryException If the query resulted in an Exception.
     */
    final public function prepare(string $sql, string $identifier = 'single'): PdoDriver
    {
        try {
            $this->preparedStatements[$identifier] = $this->pdo->prepare($sql);
        } catch (Exception $e) {
            throw new QueryException(
                'PREPARE_EXCEPTION',
                [
                    'sql' => $sql,
                    'exception' => $e
                ],
                $e);
        }
        return $this;
    }

    /**
     * Executes a prepared SQL Statement
     *
     * @param array $bind Array of binds for the prepared statement.
     * @param string $identifier An identifier to disambiguate between multiple concurrent queries
     * @return array
     * @throws QueryException If the query resulted in an Exception.
     */
    final public function execute(array $bind, string $identifier = 'single'): array
    {
        try {
            $this->preparedStatements[$identifier]->execute($bind);
        } catch (Exception $e) {
            throw new QueryException(
                'EXECUTE_EXCEPTION',
                [
                    'bind' => $bind,
                    'exception' => $e
                ],
                $e);
        }

        $return = [
            'lastInsertId' => (int)$this->pdo->lastInsertId(),
            'rowCount' => (int)$this->preparedStatements[$identifier]->rowCount()
        ];

        $this->preparedStatements[$identifier]->closeCursor();

        return $return;
    }

    /**
     * Executes an SQL Statement
     *
     * @param array $bind Array of binds for the prepared statement.
     * @param string $identifier An identifier to disambiguate between multiple concurrent queries
     * @return array
     * @throws QueryException If the query resulted in an Exception.
     */
    final public function fetch(array $bind, string $identifier = 'single'): array
    {
        try {
            $this->preparedStatements[$identifier]->execute($bind);
        } catch (Exception $e) {
            throw new QueryException(
                'PREPARE_EXCEPTION',
                [
                    'bind' => $bind,
                    'exception' => $e
                ],
                $e);
        }

        $rows = $this->preparedStatements[$identifier]->fetchAll(PDO::FETCH_ASSOC);
        $this->preparedStatements[$identifier]->closeCursor();
        if ($rows === false) {
            $rows = [];
        }
        return $rows;
    }

    /**
     * @return PDODriver
     */
    final public function beginTransaction(): PDODriver
    {
        $this->pdo->beginTransaction();
        return $this;
    }

    /**
     * @return PDODriver
     */
    final public function commit(): PDODriver
    {
        $this->pdo->commit();
        return $this;
    }

    /**
     * @return PDODriver
     */
    final public function rollback(): PDODriver
    {
        $this->pdo->rollBack();
        return $this;
    }

    /**
     * Clears a prepared statement.
     *
     * @param string $identifier An identifier to disambiguate between multiple concurrent queries
     * @return PDODriver
     */
    final public function clear(string $identifier = 'single'): PDODriver
    {
        $this->preparedStatements[$identifier] = null;
        return $this;
    }

    /**
     * Builds an appendable LIMIT/ORDER BY stub for pagination
     *
     * @param string $sortField Orders the results by this field name
     * @param string $sortDirection ASC or DESC
     * @param int $limit Limits the number of results
     * @param int $page Returns the $page'th page of results limited by $limit
     * @return string Returns an appendable LIMIT stub.
     */
    final public function buildPageLimits(string $sortField = '', string $sortDirection = 'ASC', int $limit = null, int $page = null): string
    {
        $sqlStub = '';

        if (empty($sortDirection)) {
            $sortDirection = 'ASC';
        }

        if (!empty($sortField)) {
            $sqlStub .= "ORDER BY {$sortField} {$sortDirection}";
        }

        if (!empty($limit)) {
            if (!empty($page)) {
                $offset = $limit * $page;
                $sqlStub .= " LIMIT {$offset}, {$limit}";
            } else {
                $sqlStub .= " LIMIT {$limit}";
            }
        }

        return ltrim($sqlStub, ' ');
    }

    /**
     * Prepares a bind array. Converts bind array parameters into CSV strings.
     *
     * @param string $sql
     * @param array $bind
     * @return array
     * @throws QueryException
     */
    final public function prepareBind(string $sql, array $bind): array
    {
        foreach ($bind as $identifier => $bindItem) {
            if (is_array($bindItem)) {
                if (empty($bindItem)) {
                    throw new QueryException(
                        'BIND_PARAM_EMPTY',
                        [
                            'sql' => $sql,
                            'bind' => $bind
                        ]);
                }

                $bindIdentifiers = [];
                foreach ($bindItem as $id => $bindItemParameter) {
                    $bind[$identifier . '_' . $id] = $bindItemParameter;
                    $bindIdentifiers[] = ':' . $identifier . '_' . $id;
                }
                unset($bind[$identifier]);

                $sql = str_replace(':' . $identifier, implode(', ', $bindIdentifiers), $sql);
            }
        }

        return [
            'bind' => $bind,
            'sql' => $sql
        ];
    }
}
