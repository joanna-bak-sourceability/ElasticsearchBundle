<?php

namespace M6Web\Bundle\ElasticsearchBundle\Elasticsearch\ConnectionPool;

use Opensearch\Common\Exceptions\NoNodesAvailableException;
use Opensearch\ConnectionPool\StaticNoPingConnectionPool;
use Opensearch\Connections\Connection;
use OpenSearch\Connections\ConnectionInterface;

/**
 * Class StaticAliveNoPingConnectionPool
 *
 * > Extend StaticNoPingConnectionPool and add the ability to remove any failed connection
 *    from the list of eligible connections to be chosen from by the selector.
 *
 * @package M6Web\Bundle\ElasticsearchBundle\Elasticsearch\ConnectionPool
 */
class StaticAliveNoPingConnectionPool extends StaticNoPingConnectionPool
{
    /**
     * @var int
     */
    private $pingTimeout    = 60;

    /**
     * @var int
     */
    private $maxPingTimeout = 3600;

    /**
     * > Allow to customize the ping time out.
     *
     * @param int $pingTimeout
     */
    public function setPingTimeout($pingTimeout)
    {
        $this->pingTimeout = $pingTimeout;
    }

    /**
     * > Allow to customize the ping max time out.
     *
     * @param int $maxPingTimeout
     */
    public function setMaxPingTimeout($maxPingTimeout)
    {
        $this->maxPingTimeout = $maxPingTimeout;
    }

    /**
     * @throws \Opensearch\Common\Exceptions\NoNodesAvailableException
     */
    public function nextConnection(bool $force = false): ConnectionInterface
    {
        // > Replace $this->connections by $connections in order to modify the list later.
        $connections = $this->connections;
        $total = count($connections);
        while ($total--) {
            /** @var Connection $connection */
            $connection = $this->selector->select($connections);
            if ($connection->isAlive() === true) {
                return $connection;
            }

            if ($this->readyToRevive($connection) === true) {
                return $connection;
            }

            // > Remove the failed connection from the list of eligible connections.
            $connections = array_filter(
                $connections,
                function ($baseConnection) use ($connection) {
                    return $baseConnection !== $connection;
                }
            );
        }

        throw new NoNodesAvailableException("No alive nodes found in your cluster");
    }

    /**
     * > Same as parent private method.
     *
     * @param Connection $connection
     *
     * @return bool
     */
    protected function readyToRevive(Connection $connection)
    {
        $timeout = min(
            $this->pingTimeout * pow(2, $connection->getPingFailures()),
            $this->maxPingTimeout
        );

        if ($connection->getLastPing() + $timeout < time()) {
            return true;
        } else {
            return false;
        }
    }
}
