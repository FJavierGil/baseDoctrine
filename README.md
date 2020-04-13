TDW: base Doctrine
======================================

[![MIT license](http://img.shields.io/badge/license-MIT-brightgreen.svg)](http://opensource.org/licenses/MIT)
[![Minimum PHP Version](https://img.shields.io/badge/php-%5E7.2-blue.svg)](http://php.net/)

> Proyecto básico con ORM Doctrine y DotENV

Este proyecto pretende servir como base para hacer más sencilla la gestión de datos en PHP.
En concreto, se ha utilizado el ORM [Doctrine][doctrine], que es un Object-Relational
Mapper que proporciona persistencia transparente para objetos PHP. Emplea el patrón [Data Mapper][dataMapper]
con el objetivo de obtener un desacoplamiento completo entre la lógica de negocio y la
persistencia de los datos en los sistema de gestión de bases de datos (SGBD).

Adicionalmente, este proyecto se apoya en el componente [DotENV][dotenv] para facilitar
su configuración a través de variables de entorno. Esto permite que cualquier configuración
que pueda variar entre diferentes entornos pueda ser establecida en variables de entorno,
tal como se aconseja en la metodología [“The twelve-factor app”][12factor].

## Instalación de la aplicación

Para realizar la instalación de la aplicación crearán un usuario, contraseña y base de datos
en el SGBD. A continuación se debe crear una copia del fichero `./.env` y renombrarla
como `./.env.local`. Se debe editar este fichero para asignar los parámetros:

* (opcional) Configuración del servidor de bases de datos
* Nombre de la base de datos
* Configuración del acceso a la base de datos (usuario y contraseña)

Una vez editado el anterior fichero, desde el directorio raíz del proyecto se ejecutarán los comandos:
```
$> composer install
$> bin\doctrine orm:schema:update --dump-sql --force
```

Para comprobar la validez de la información de mapeo y la sincronización con la base de datos:
```
$> bin\doctrine orm:schema:validate
```

##Estructura del proyecto:

A continuación se describe el contenido y estructura del proyecto:

* Directorio `bin`:
    - Ejecutables (*doctrine*)
* Directorio `config`:
    - `cli-config.php`: configuración de la consola de comandos de Doctrine
* Directorio `src`:
    - Subdirectorio `src/Entity`: entidades PHP (incluyen anotaciones de mapeo del ORM)
    - Subdirectorio `src/scripts`: scripts de ejemplo
* Directorio `vendor`:
    - Componentes desarrollados por terceros (Doctrine, DotENV, etc.)

[dataMapper]: http://martinfowler.com/eaaCatalog/dataMapper.html
[doctrine]: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/
[dotenv]: https://packagist.org/packages/vlucas/phpdotenv
[12factor]: https://www.12factor.net/es/