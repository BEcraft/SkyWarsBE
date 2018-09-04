<?php

declare(strict_types=1);

namespace SkyWars\Mapas;

/** SkyWars */
use SkyWars\Cargador;

/** PocketMine */
use pocketmine\math\Vector3;
use pocketmine\level\Level;

class GestorMapas
{

    /**
     * Mapas disponibles para jugar.
     * @var array
     */
    public $mapasDisponibles = array();

    /** Cantidad máxima de mapas para enviar. */
    private const MAPAS = 3;

    public function __construct(array $mapas)
    {
        foreach ($mapas as $nombre => $datos) {

            if (is_file(Cargador::$instancia->getDataFolder() . Cargador::MAPAS . $nombre . ".zip") === false) {
                continue;
            }

            $maximo = isset($datos["maximo"]) ? intval($datos["maximo"]) : 0;

            if ($maximo < 2) {
                continue;
            }

            $posiciones = isset($datos["posiciones"]) ? (array)$datos["posiciones"] : array();

            if (count($posiciones = $this->textosVector($posiciones)) === $maximo) {
                 $cofresMedianos = isset($datos["medianos"]) ? $this->textosVector($datos["medianos"]) : array();
                $cofresMaximos = isset($datos["maximos"]) ? $this->textosVector($datos["maximos"]) : array();
                $this->mapasDisponibles[$maximo][$nombre] = new Mapa($posiciones, $cofresMaximos, $cofresMedianos);
            }

        }
    }

    /**
     * Guarda un nuevo mapa en la configuración.
     * @param  array $datos
     * @return void
     */
    public function guardarMapa(array $datos): void
    {
        $configuracion = Cargador::$instancia->getConfig();
        $mapasGuardados = $configuracion->get("mapas", array());
        $mapasGuardados[array_shift($datos)] = $datos;
        $configuracion->set("mapas", $mapasGuardados);
        $configuracion->save();
    }

    /**
     * Verifica si un mapa existe.
     * @param  string $nombre
     * @return boolean
     */
    public function existeMapa(string $nombre): bool
    {
        return $this->conseguirMapa($nombre) != null;
    }

    /**
     * Extrae un mapa en especifico.
     * @param  string $identificador
     * @param  string $nombre
     * @return null | Level
     */
    public function extraerMapa(string $identificador, string $nombre): ?Level
    {
        $archivoZip = new \ZipArchive();

        if ($archivoZip->open(Cargador::$instancia->getDataFolder() . Cargador::MAPAS . $nombre . ".zip")) {
            $archivoZip->extractTo(Cargador::$instancia->getServer()->getDataPath() . "worlds/" . $identificador);
            $archivoZip->close();
        }

        Cargador::$instancia->getServer()->loadLevel($identificador);

        return Cargador::$instancia->getServer()->getLevelByName($identificador);
    }

    /**
     * Guarda un mapa en archivo ZIP
     * @param  string $nombre
     * @return void
     */
    public function crearArchivo(string $nombre): void
    {
        $archivoZip = new \ZipArchive();

        if ($archivoZip->open(Cargador::$instancia->getDataFolder() . Cargador::MAPAS . $nombre . ".zip", \ZipArchive::CREATE)) {

            $directorio = Cargador::$instancia->getServer()->getDataPath() . "worlds/" . $nombre;

            foreach ($this->conseguirArchivos($directorio) as $archivo) {
                if (is_file($archivo->getRealPath())) {
                    $correcion = str_replace("\\", "/", ltrim(substr($archivo->getRealPath(), strlen($directorio)), "/\\"));
                    $archivoZip->addFile($archivo->getRealPath(), $correcion);
                }
            }
        }

        $archivoZip->close();
    }

    /**
     * Conseguir los archivos de cierto directorio
     * @param  string $directorio
     * @return \RecursiveIteratorIterator
     */
    public function conseguirArchivos(string $directorio): \RecursiveIteratorIterator
    {
        $archivos = new \RecursiveDirectoryIterator($directorio, \RecursiveDirectoryIterator::SKIP_DOTS);
        return new \RecursiveIteratorIterator($archivos, \RecursiveIteratorIterator::CHILD_FIRST);
    }

    /**
     * Elimina un mundo existente.
     * @param  string $nombre
     * @return void
     */
    public function eliminarMundo(string $nombre): void
    {
        $servidor = Cargador::$instancia->getServer();
        $mundo = $servidor->getLevelByName($nombre);

        if ($mundo !== null) {

            $servidor->unloadLevel($mundo);
            $directorio = $servidor->getDataPath() . "worlds/" . $nombre;

            foreach ($this->conseguirArchivos($directorio) as $archivo) {
                if ($archivo->isDir()) {
                    rmdir($archivo->getRealPath());
                } else {
                    unlink($archivo->getRealPath());
                }
            }
            rmdir($directorio);
        }

    }

    /**
     * Conseguir una lista de mapas para votacion.
     * @param  int $cantidad
     * @return array
     */
    public function conseguirListaDeMapas(int $cantidad): array
    {
        $lista = array();
        $mapas = $this->mapasDisponibles[$cantidad] ?? array();

        if (!empty($mapas)) {

            $maximo = self::MAPAS;

            if (count($mapas) < $maximo) {
                $maximo = count($mapas);
            }

            $random = (array)array_rand($mapas, $maximo);
            shuffle($random); //Sabias que no puedes pronunciar la 'L' con la boca abierta y sin mover la lengua? XD
            foreach ($random as $mapa) {
                $lista[$mapa] = 0;
            }

        }

        return $lista;
    }

    /**
     * Consigue un vector de un texto.
     * @param  string $texto
     * @return Vector3
     */
    public function conseguirVector(string $texto): Vector3
    {
        if (strpos($texto, ":") !== false) {

            $posicion = array_pad(explode(":", $texto), 6, 1);
            list($x, $y, $z) = array_values(array_filter($posicion, function ($i): bool {
                return is_numeric($i);
            }));

            return new Vector3((float)$x, (float)$y, (float)$z);
        }

        return Vector3(0, 0, 0);
    }

    /**
     * Consigue cierto mapa.
     * @param  string $nombre
     * @return null | Mapa
     */
    public function conseguirMapa(string $nombre): ?Mapa
    {
        foreach ($this->mapasDisponibles as $maximo => $datos) {
            foreach ($datos as $nombre2 => $mapa) {
                if ($nombre2 === $nombre) {
                    return $mapa;
                }
            }
        }

        return null;
    }

    /**
     * Consigue multiples vectores.
     * @param  array $posiciones
     * @return array
     */
    public function textosVector(array $posiciones): array
    {
        $enTotal = array();

        foreach ($posiciones as $posicion) {
            $enTotal[] = $this->conseguirVector((string)$posicion);
        }

        return $enTotal;
    }

}