<?php

/**
 * src/Utility/DoctrineConnector.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace MiW\DemoDoctrine\Utility;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Throwable;

/**
 * Class DoctrineConnector
 */
final class DoctrineConnector
{
    private static ?EntityManager $instance = null;

    /**
     * Generate the Entity Manager
     *
     * @return EntityManagerInterface|null
     */
    public static function getEntityManager(): ?EntityManagerInterface
    {
        if (null !== self::$instance) {
            return self::$instance;
        }

        if (
            !isset(
                $_ENV['DATABASE_NAME'],
                $_ENV['DATABASE_USER'],
                $_ENV['DATABASE_PASSWD'],
                $_ENV['ENTITY_DIR']
            )
        ) {
            fwrite(STDERR, 'Faltan variables de entorno por definir' . PHP_EOL);
            exit(1);
        }

        // Cargar configuración de la conexión.
        $dbParams = [
            'host'      => $_ENV['DATABASE_HOST'] ?? '127.0.0.1',
            'port'      => $_ENV['DATABASE_PORT'] ?? 3306,
            'dbname'    => $_ENV['DATABASE_NAME'],
            'user'      => $_ENV['DATABASE_USER'],
            'password'  => $_ENV['DATABASE_PASSWD'],
            'driver'    => $_ENV['DATABASE_DRIVER'] ?? 'pdo_mysql',
            'charset'   => $_ENV['DATABASE_CHARSET'] ?? 'UTF8',
        ];

        $entityDir = dirname(__DIR__, 2) . '/' . $_ENV['ENTITY_DIR'];
        $queryCache = new PhpFilesAdapter('doctrine_queries');
        // $metadataCache = new PhpFilesAdapter('doctrine_metadata');
        $config = Setup::createAnnotationMetadataConfiguration(
            [ $entityDir ],            // Paths to mapped entities
            true,                       // Developper mode
            ini_get('sys_temp_dir'),    // Proxy dir
            null,                       // Cache implementation
            false                       // Use Simple Annotation Reader
        );
        $config->setQueryCache($queryCache);
        // $config->setMetadataCache($metadataCache);
        // $config->setAutoGenerateProxyClasses(true);

        try {
            $entityManager = EntityManager::create($dbParams, $config);
        } catch (Throwable $e) {
            $msg = sprintf('ERROR (%d): %s', $e->getCode(), $e->getMessage());
            fwrite(STDERR, $msg . PHP_EOL);
            exit(1);
        }

        return $entityManager;
    }

    protected function __construct()
    {
    }

    private function __clone()
    {
    }

//    private function __wakeup()
//    {
//    }
}
