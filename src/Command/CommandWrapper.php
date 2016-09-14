<?php

namespace Tequilla\MongoDB\Command;

use MongoDB\Driver\ReadPreference;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\CommandCursor;
use Tequilla\MongoDB\Connection;
use Tequilla\MongoDB\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException as OptionsResolverException;
use Tequilla\MongoDB\Util\TypeUtils;

/**
 * Class Command
 * @package Tequilla\MongoDB\Command
 */
class CommandWrapper
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $databaseName;

    /**
     * @var ReadPreference
     */
    private $readPreference;

    /**
     * @var string
     */
    private $commandClass;

    /**
     * @var OptionsResolver[]
     */
    private static $cachedResolvers;

    /**
     * Command constructor.
     * @param Connection $connection
     * @param string $databaseName
     * @param string $commandClass
     */
    public function __construct(
        Connection $connection,
        $databaseName,
        $commandClass
    ) {
        TypeUtils::ensureIsSubclassOf($commandClass, CommandTypeInterface::class);

        $this->databaseName = (string) $databaseName;
        $this->connection = $connection;
        $this->commandClass = (string) $commandClass;
        $this->readPreference = call_user_func([$commandClass, 'getDefaultReadPreference']);
    }

    /**
     * @param array $options
     * @return CommandCursor
     */
    public function execute(array $options = [])
    {
        $commandOptions = $this->resolveCommandOptions($options);

        return $this->connection->executeCommand(
            $this->databaseName,
            $commandOptions,
            $this->readPreference
        );
    }

    /**
     * @param ReadPreference $readPreference
     * @return $this
     */
    public function setReadPreference(ReadPreference $readPreference)
    {
        $commandClass = $this->commandClass;
        if (!call_user_func([$commandClass, 'supportsReadPreference'], $readPreference)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Command "%s" does not support "%s" read preference',
                    $commandClass,
                    $readPreference->getMode()
                )
            );
        }

        $this->readPreference = $readPreference;

        return $this;
    }

    /**
     * @param array $options
     * @throws InvalidArgumentException
     * @return array
     */
    private function resolveCommandOptions(array $options)
    {
        $commandName = call_user_func([$this->commandClass, 'getCommandName']);
        $resolver = self::getCachedResolver($this->commandClass);

        try {
            $options = $resolver->resolve($options);
        } catch(OptionsResolverException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }

        $commandValue = $options[$commandName];
        return [$commandName => $commandValue] + $options;
    }

    /**
     * @param $commandClass
     * @return OptionsResolver
     */
    private static function getCachedResolver($commandClass)
    {
        if (!isset(self::$cachedResolvers[$commandClass])) {
            $resolver = new OptionsResolver();
            call_user_func([$commandClass, 'configureOptions'], $resolver);
            $commandName = call_user_func([$commandClass, 'getCommandName']);
            $resolver->setRequired($commandName);

            self::$cachedResolvers[$commandClass] = $resolver;
        }

        return self::$cachedResolvers[$commandClass];
    }
}