<?php

namespace SkyWars\Lenguaje;

/** PocketMine */
use pocketmine\lang\BaseLang;

class Lenguaje
{
    /**
     * Lenguajes disponibles.
     * @var array
     */
    private static $lenguajes = array();

    /** Idioma principal */
    private const PRINCIPAL = "es";

    public function __construct(array $directorios, $directorio)
    {
        foreach ($directorios as $lenguaje => $archivo) {
            self::$lenguajes[$lenguaje] = new BaseLang($archivo, $directorio, $archivo);
        }
    }

    /**
     * Traducir los mensajes.
     * @return string
     */
    public static function traducir(string $lenguaje, string $identificador, array $datos = array()): string
    {
        $lenguaje = substr($lenguaje, 0, 2);

        if (isset(self::$lenguajes[$lenguaje]) === false) {
            $lenguaje = self::PRINCIPAL;
        }

        return self::$lenguajes[$lenguaje]->translateString($identificador, $datos) ?? "";
    }

    /*
     * Consigue los idiomas que estan disponibles.
     * @return string
     */
    public static function conseguirListaDeIdiomas(): string {
        return implode(", ", array_keys(self::$lenguajes));
    }

}