<?php

/**
 * ./config/cli-config.php
 *
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://miw.etsisi.upm.es E.T.S. de Ingeniería de Sistemas Informáticos
 */

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use MiW\DemoDoctrine\Utility\DoctrineConnector;
use MiW\DemoDoctrine\Utility\Utils;

// Load env variables from .env + (.docker || .local )
Utils::loadEnv(dirname(__DIR__));

$entityManager = DoctrineConnector::getEntityManager();

return ConsoleRunner::createHelperSet($entityManager);
