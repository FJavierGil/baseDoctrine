<?php

/**
 * src/Utility/Utils.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    https://miw.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace MiW\DemoDoctrine\Utility;

use Doctrine\ORM\{ EntityManager, Tools\SchemaTool };
use Dotenv\Dotenv;
use Throwable;

/**
 * Class Utils
 */
class Utils
{
    /**
     * Load the environment/configuration variables
     * defined in .env file + (.env.docker || .env.local)
     *
     * @param string $dir   project root directory
     */
    public static function loadEnv(string $dir): void
    {
        require_once $dir . '/vendor/autoload.php';

        if (!class_exists(Dotenv::class)) {
            fwrite(STDERR, 'ERROR: No se ha cargado la clase Dotenv' . PHP_EOL);
            exit(1);
        }

        try {
            // Load environment variables from .env file
            if (file_exists($dir . '/.env')) {
                $dotenv = Dotenv::createMutable($dir, '.env');
                $dotenv->load();
            } else {
                fwrite(STDERR, 'ERROR: no existe el fichero .env' . PHP_EOL);
                exit(1);
            }

            // Overload (if they exist) with .env.docker or .env.local
            if (isset($_SERVER['DOCKER']) && file_exists($dir . '/.env.docker')) {
                $dotenv = Dotenv::createMutable($dir, '.env.docker');
                $dotenv->load();
            } elseif (file_exists($dir . '/.env.local')) {
                $dotenv = Dotenv::createMutable($dir, '.env.local');
                $dotenv->load();
            }

            // Requiring Variables to be set
            $dotenv->required([ 'DATABASE_NAME', 'DATABASE_USER', 'DATABASE_PASSWD', 'SERVER_VERSION' ]);
            $dotenv->required([ 'ENTITY_DIR' ]);
        } catch (Throwable $e) {
            fwrite(
                STDERR,
                'EXCEPCIÓN: ' . $e->getCode() . ' - ' . $e->getMessage()
            );
            exit(1);
        }
    }

    /**
     * Drop & Update database schema
     *
     * @return void
     */
    public static function updateSchema(): void
    {
        try {
            /** @var EntityManager $e_manager */
            $e_manager = DoctrineConnector::getEntityManager();
            $e_manager->clear();
            $metadata = $e_manager->getMetadataFactory()->getAllMetadata();
            $sch_tool = new SchemaTool($e_manager);
            $sch_tool->dropDatabase();
            $sch_tool->updateSchema($metadata);
        } catch (Throwable $e) {
            fwrite(
                STDERR,
                'EXCEPCIÓN: ' . $e->getCode() . ' - ' . $e->getMessage()
            );
            exit(1);
        }
    }
}
