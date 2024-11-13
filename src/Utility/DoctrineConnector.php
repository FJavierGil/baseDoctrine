<?php

/**
 * src/Utility/DoctrineConnector.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    https://miw.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace MiW\DemoDoctrine\Utility;

use Doctrine\DBAL\{ Connection, DriverManager };
use Doctrine\ORM\{ EntityManager, EntityManagerInterface, ORMSetup };
use Doctrine\ORM\Proxy\ProxyFactory;
use Exception;
// use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Throwable;

/**
 * Class DoctrineConnector
 */
final class DoctrineConnector
{
    private static EntityManager|null $instance = null;

    /**
     * Generate the Entity Manager
     *
     * @return EntityManagerInterface|null
     */
    public static function getEntityManager(): ?EntityManagerInterface
    {
        if (self::$instance instanceof EntityManager) {
            return self::$instance;
        }

        if (
            !isset(
                $_ENV['DATABASE_NAME'],
                $_ENV['DATABASE_USER'],
                $_ENV['DATABASE_PASSWD'],
                $_ENV['ENTITY_DIR'],
                $_ENV['SERVER_VERSION'],
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
            'serverVersion' => $_ENV['SERVER_VERSION'],
        ];

        $entityDir = dirname(__DIR__, 2) . '/' . $_ENV['ENTITY_DIR'];
        // $queryCache = new PhpFilesAdapter('doctrine_queries');
        // $metadataCache = new PhpFilesAdapter('doctrine_metadata');
        // $resultsCache = new PhpFilesAdapter('doctrine_results');
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: [ $entityDir ],            // paths to mapped entities
            isDevMode: true,                  // developper mode
            proxyDir: (string) ini_get('sys_temp_dir') // Proxy dir
        );
        // $config->setQueryCache($queryCache);
        // $config->setMetadataCache($metadataCache);
        // $config->setResultCache($resultsCache);
        $config->setAutoGenerateProxyClasses(ProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS_OR_CHANGED);

        // configuring the database connection
        /** @var Connection $connection */
        $connection = DriverManager::getConnection($dbParams, $config);

        try {
            $entityManager = new EntityManager($connection, $config);
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

    protected function __clone()
    {
    }

    /**
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new Exception('Cannot unserialize a Singleton.');
    }
}
