<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\Manager;
use Tequila\MongoDB\Command\Options\CommonOptions;
use Tequila\MongoDB\CommandCursor;

class DropCollection implements CommandInterface
{
    use Traits\SelectPrimaryServerTrait;

    /**
     * @var string
     */
    private $databaseName;

    /**
     * @var string
     */
    private $collectionName;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string $databaseName
     * @param string $collectionName
     * @param array $options
     */
    public function __construct($databaseName, $collectionName, array $options = [])
    {
        $this->databaseName = (string)$databaseName;
        $this->collectionName = (string)$collectionName;
        $this->options = CommonOptions::resolve($options);
    }

    public function execute(Manager $manager)
    {
        $options = ['drop' => $this->collectionName] + $this->options;

        return new CommandCursor($this->selectPrimaryServer($manager), $this->databaseName, $options);
    }
}