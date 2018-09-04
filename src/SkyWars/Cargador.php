<?php

declare(strict_types=1);

namespace SkyWars;

/** SkyWars */
use SkyWars\Eventos\Eventos;
use SkyWars\Kits\KitsGestor;
use SkyWars\Mapas\GestorMapas;
use SkyWars\Cofres\GestorCofres;
use SkyWars\Partidas\{
    Solo, Duo
};
use SkyWars\Sesiones\GestorSeccion;
use SkyWars\Tiempo\{
    Carteles, Partidas
};

/** PocketMine */
use pocketmine\plugin\PluginBase;
use pocketmine\lang\BaseLang;
use pocketmine\level\Level;
use pocketmine\tile\Sign;

class Cargador extends PluginBase
{

    /** Nombre del directorio para los mapas */
    const MAPAS = "Mapas/";

    /**
     * Lenguaje para el minijuego.
     * @var BaseLang
     */
    public $lenguaje;

    /**
     * Mapa de espera para las partidas.
     * @var Level
     */
    public $mundo;

    /**
     * Gestor para los kits de las partidas.
     * @var KitsGestor
     */
    public $kitsGestor;

    /**
     * Gestor para los cofres de las partidas.
     * @var GestorCofres
     */
    public $cofresGestor;

    /**
     * Partidas disponibles.
     * @var array
     */
    public $partidas = array();

    /**
     * Carteles de la partida.
     * @var array
     */
    public $carteles = array();

    /**
     * Gestor para los mapas.
     * @var GestorMapas
     */
    public $mapasGestor;

    /**
     * Gestor para las secciones Usuario y Creador.
     * @var GestorSeccion
     */
    public $seccionGestor;

    /**
     * Al habilitar el minijuego.
     */
    public function onEnable()
    {
        //crea los directorios
        @mkdir($directorio = $this->getDataFolder());
        @mkdir($directorio . self::MAPAS);

        //guarda algun archivo que no exista
        $archivos = array("config.yml", "espanol.ini", "ingles.ini", "portugues.ini", "italiano.ini", "Cofres.json");
        foreach ($archivos as $archivo) {
            if (!file_exists($directorio . $archivo)) {
                $this->saveResource($archivo);
            }
        }

        //configuracion
        $configuracion = $this->getConfig();

        //Verificación del idioma
        $idioma = $configuracion->get("idioma", "espanol");
        if (($datos = $this->getResource($idioma . ".ini")) === null) {
            $idioma = "espanol";
        } else {
            fclose($datos);
        }

        //Idioma para el mini-juego
        $this->lenguaje = new BaseLang($idioma, $directorio, $idioma);//cambiar ^ de folder

        //Instanciar el gestor de mapas
        $this->mapasGestor = new GestorMapas($configuracion->get("mapas", array()));

        //Veríficar y asignar el mapa de espera
        $this->getServer()->loadLevel($mapa = (string)$configuracion->get("lobby", ""));
        $this->mundo = $this->getServer()->getLevelByName($mapa);
        if ($this->mundo === null) {
            $this->mundo = $this->getServer()->getDefaultLevel();
        }

        //Instanciar el gestor para las secciones
        $this->seccionGestor = new GestorSeccion();

        //Cargar todos los carteles para las partidas
        $this->cargarCarteles($this->getConfig()->get("carteles", array()));

        //Instanciar la clase para los kits
        $this->kitsGestor = new KitsGestor($configuracion->get("kits", array()), (bool)$configuracion->get("operador", false));

        //Cargar todas las partidas
        foreach ($this->getConfig()->get("partidas", array()) as $position => $partida) {
            $this->cargarPartida($partida);
        }

        //Instanciar gestor para los cofres
        $this->cofresGestor = new GestorCofres((array)json_decode(file_get_contents($directorio . "Cofres.json"), true));

        //Iniciar los tasks
        $this->getScheduler()->scheduleRepeatingTask(new Carteles($this), 30);
        $this->getScheduler()->scheduleRepeatingTask(new Partidas($this), 20);

        //Registrar los eventos
        $this->getServer()->getPluginManager()->registerEvents(new Eventos(), $this);

        //Registrar el comando "skywars"
        $this->getServer()->getCommandMap()->register("/skywars", new Comando($this));
    }

    /**
     * Conseguir lenguaje
     * @return BaseLang
     */
    public function conseguirLenguaje(): BaseLang
    {
        return $this->lenguaje;
    }

    /**
     * Consigue el mundo de espera para las partidas.
     * @return Level
     */
    public function conseguirLobby(): Level
    {
        return $this->mundo;
    }

    /**
     * Consigue el gestor de kits.
     * @return KitsGestor
     */
    public function conseguirGestorDeKits(): KitsGestor
    {
        return $this->kitsGestor;
    }

    /**
     * Consigue el gestor de cofres.
     * @return GestorCofres
     */
    public function conseguirGestorDeCofres(): GestorCofres
    {
        return $this->cofresGestor;
    }

    /**
     * Conseguir el gestor para los mapas.
     * @return GestorMapas
     */
    public function conseguirGestorDeMapas(): GestorMapas
    {
        return $this->mapasGestor;
    }

    /**
     * Conseguir el gestor para secciones.
     * @return GestorSeccion
     */
    public function conseguirGestorDeSecciones(): GestorSeccion
    {
        return $this->seccionGestor;
    }

    /**
     * Cargar todos los carteles.
     * @param  array $carteles
     * @return void
     */
    private function cargarCarteles(array $carteles): void
    {
        foreach ($carteles as $cartel) {
            $objetivo = $this->getServer()->getDefaultLevel()->getTile($this->conseguirGestorDeMapas()->conseguirVector($cartel));

            if (!($objetivo instanceof Sign)) {
                continue;
            }

            $this->carteles[$objetivo->__toString()] = new Cartel($objetivo);
        }
    }

    /**
     * Carga una nueva partida de skywars.
     * @param  array $datos
     * @return boolean
     */
    private function cargarPartida(array $datos): bool
    {
        if (!isset($datos["equipo"])) {
            return false;
        }

        $maximo = intval($datos["maximo"]) ?? 0;
        $minimo = intval($datos["minimo"]) ?? 0;

        $requerir = boolval($datos["equipo"] ?? 0) === true ? 3 : 2;

        if ($maximo >= $requerir and $minimo >= $requerir) {

            if ($maximo < $minimo) {
                return false;
            }

            $posiciones = $requerir === 3 ? round($maximo / 2) : $maximo;

            if (count($this->conseguirGestorDeMapas()->conseguirListaDeMapas((int)$posiciones)) > 0) {
                $this->partidas[] = $requerir === 3 ? new Duo($minimo, $maximo) : new Solo($minimo, $maximo);
            }
        }

        return true;
    }

    /**
     * Cargador
     * @var Cargador
     */
    public static $instancia;

    /**
     * Asigna la instancia al cargar el plugin.
     * @return void
     */
    public function onLoad(): void
    {
        self::$instancia = $this;
    }

    /**
     * Guardar los carteles y borrar los mapas de las partidas al deshabilitar el minijuego.
     * @return void
     */
    public function onDisable(): void
    {
        $posiciones = array();

        /** @var Cartel $cartel */
        foreach (array_values($this->carteles) as $cartel) {
            $cartel = $cartel->conseguirCartel();
            $posiciones[] = $cartel->x . ":" . $cartel->y . ":" . $cartel->z;
        }

        $this->getConfig()->set("carteles", $posiciones);
        $this->getConfig()->save();

        foreach (new \DirectoryIterator($this->getServer()->getDataPath() . "worlds/") as $directorio) {
            if ($directorio->isDir() and strpos($directorio->getFilename(), "SkyWars=") !== false) {
                $this->conseguirGestorDeMapas()->eliminarMundo($directorio->getFilename());
            }
        }

    }

}