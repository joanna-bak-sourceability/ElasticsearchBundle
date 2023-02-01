<?php

namespace M6Web\Bundle\ElasticsearchBundle\Elasticsearch\ConnectionPool\Selector;

use Opensearch\Common\Exceptions\NoNodesAvailableException;
use Opensearch\ConnectionPool\Selectors\SelectorInterface;
use Opensearch\Connections\ConnectionInterface;

class RandomStickySelector implements SelectorInterface
{
    protected $current;

    /**
     * Select a random connection from the provided array and stick with it.
     *
     * @inheritdoc
     * @throws NoNodesAvailableException
     */
    public function select(array $connections): ConnectionInterface
    {
        if (empty($connections)) {
            throw new NoNodesAvailableException('No node to select from…');
        }

        if (($this->current === null) || !isset($connections[$this->current])) {
            $this->current = array_rand($connections);
        }

        return $connections[$this->current];
    }
}
