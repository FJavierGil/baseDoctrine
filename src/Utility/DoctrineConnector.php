<?php

/**
 * src/Utility/DoctrineConnector.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    https://miw.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace MiW\DemoDoctrine\Utility;

use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\{ORM, DBAL};
use Exception;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Throwable;

/**
 * Class DoctrineConnector
 */
final class DoctrineConnector
{
    private static ORM\EntityManager | null $instance = null;

    /**
     * Generate the Entity Manager
     *
     * @return ORM\EntityManagerInterface|null
     */
    public static function getEntityManager(): ?ORM\EntityManagerInterface
    {
        if (self::$instance instanceof ORM\EntityManager) {
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
        // $debug = $_ENV['DEBUG'] ?? false;
        $queryCache = new PhpFilesAdapter('doctrine_queries');
        // $metadataCache = new PhpFilesAdapter('doctrine_metadata');
        $resultsCache = new PhpFilesAdapter('doctrine_results');
        $config = ORM\ORMSetup::createAttributeMetadataConfiguration(
            [ $entityDir ],            // paths to mapped entities
            true,                      // developper mode
            (string) ini_get('sys_temp_dir')   // Proxy dir
        );
        $config->setQueryCache($queryCache);
        // $config->setMetadataCache($metadataCache);
        $config->setResultCache($resultsCache);
        $config->setAutoGenerateProxyClasses((bool) AbstractProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS_OR_CHANGED);
        // if ($debug) {
        //     $config->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());
        // }

        try {
            $connection    = DBAL\DriverManager::getConnection($dbParams, $config);
            $entityManager = new ORM\EntityManager($connection, $config);
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
        throw new Exception("Cannot unserialize a Singleton.");
    }
}
