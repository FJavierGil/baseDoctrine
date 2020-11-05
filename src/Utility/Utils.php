<?php

/**
 * PHP version 7.4
 * src/Utility/Utils.php
 */

namespace MiW\DemoDoctrine\Utility;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup;

/**
 * Trait Utils
 */
trait Utils
{
    /**
     * Generate the Entity Manager
     *
     * @return null|EntityManagerInterface
     */
    public static function getEntityManager(): ?EntityManagerInterface
    {
        if (!isset(
            $_ENV['DATABASE_NAME'],
            $_ENV['DATABASE_USER'],
            $_ENV['DATABASE_PASSWD'],
            $_ENV['ENTITY_DIR']
        )) {
            fwrite(STDERR, 'Faltan variables de entorno por definir' . PHP_EOL);
            die(1);
        }

        // Cargar configuración de la conexión
        $dbParams = [
            'host'      => $_ENV['DATABASE_HOST'] ?? '127.0.0.1',
            'port'      => $_ENV['DATABASE_PORT'] ?? 3306,
            'dbname'    => $_ENV['DATABASE_NAME'],
            'user'      => $_ENV['DATABASE_USER'],
            'password'  => $_ENV['DATABASE_PASSWD'],
            'driver'    => $_ENV['DATABASE_DRIVER'] ?? 'pdo_mysql',
            'charset'   => $_ENV['DATABASE_CHARSET'] ?? 'UTF8',
        ];

        $debug = $_ENV['DEBUG'] ?? false;
        $entityDir = dirname(__DIR__, 2) . $_ENV['ENTITY_DIR'];
        $config = Setup::createAnnotationMetadataConfiguration(
            [ $entityDir ],            // paths to mapped entities
            $debug,                    // developper mode
            ini_get('sys_temp_dir'),   // Proxy dir
            null,                      // Cache implementation
            false                      // use Simple Annotation Reader
        );
        $config->setAutoGenerateProxyClasses(true);
        if ($debug) {
            $config->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());
        }

        try {
            $entityManager = EntityManager::create($dbParams, $config);
        } catch (\Throwable $e) {
            $msg = sprintf('ERROR (%d): %s', $e->getCode(), $e->getMessage());
            fwrite(STDERR, $msg . PHP_EOL);
            die(1);
        }

        return $entityManager;
    }

    /**
     * Load the environment/configuration variables
     * defined in .env file + (.env.docker || .env.local)
     *
     * @param string $dir   project root directory
     */
    public static function loadEnv(string $dir): void
    {
        /** @noinspection PhpIncludeInspection */
        require_once $dir . '/vendor/autoload.php';

        if (!class_exists(\Dotenv\Dotenv::class)) {
            fwrite(STDERR, 'ERROR: No se ha cargado la clase Dotenv' . PHP_EOL);
            die(1);
        }

        try {
            // Load environment variables from .env file
            if (file_exists($dir . '/.env')) {
                $dotenv = \Dotenv\Dotenv::createMutable($dir, '.env');
                $dotenv->load();
            } else {
                fwrite(STDERR, 'ERROR: no existe el fichero .env' . PHP_EOL);
                die(1);
            }

            // Overload (if they exist) with .env.docker or .env.local
            if (filter_has_var(INPUT_SERVER, 'DOCKER') && file_exists($dir . '/.env.docker')) {
                $dotenv = \Dotenv\Dotenv::createMutable($dir, '.env.docker');
                $dotenv->load();
            } elseif (file_exists($dir . '/.env.local')) {
                $dotenv = \Dotenv\Dotenv::createMutable($dir, '.env.local');
                $dotenv->load();
            }
        } catch (\Throwable $e) {
            die(get_class($e) . ': ' . $e->getMessage() . PHP_EOL);
        }
    }
}
