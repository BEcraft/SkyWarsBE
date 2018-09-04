<?php

declare(strict_types=1);

namespace SkyWars;

/** SkyWars */
use SkyWars\Partidas\SkyWars;
use SkyWars\Sesiones\Creador;

/** PocketMine */
use pocketmine\Player;
use pocketmine\command\{
    PluginCommand, CommandSender
};

class Comando extends PluginCommand
{

    /**
     * Cargador
     * @var Cargador
     */
    private $cargador;

    public function __construct(Cargador $cargador)
    {
        parent::__construct("skywars", $cargador);
        $this->cargador = $cargador;
    }

    /**
     * Ejecución del comando "skywars".
     * @param  CommandSender $sender
     * @param  string        $label
     * @param  array         $param
     * @return boolean
     */
    public function execute(CommandSender $sender, string $label, array $param): bool
    {
        if (!empty($param)) {
            if ($sender instanceof Player) {
                $jugando = $this->cargador->conseguirGestorDeSecciones()->estaJugando($sender->getName());
                $creando = $this->cargador->conseguirGestorDeSecciones()->estaCreando($sender->getName());
                $idiomas = $this->cargador->conseguirLenguaje();
                if ($jugando !== null) {

                    switch ($param[0]) {

                        case "salir":
                        case "leave":
                        case "sair":
                        case "uscire":
                            $jugando->terminar(); //sabias que eres especial?
                            break;

                    }

                } else if ($creando !== null) {

                    switch ($param[0]) {

                        case "mapa":
                        case "map":
                        case "mappa":
                            $mundo = $sender->getLevel()->getFolderName();

                            if ($mundo === $this->cargador->getServer()->getDefaultLevel()->getFolderName()) {
                                $sender->sendMessage($idiomas->translateString("creando.mapa.default"));
                                break;
                            }

                            if ($mundo === $this->cargador->conseguirLobby()->getFolderName()) {
                                $sender->sendMessage($idiomas->translateString("creando.mapa.lobby"));
                                break;
                            }

                            if ($this->cargador->conseguirGestorDeMapas()->existeMapa($mundo)) {
                                $sender->sendMessage($idiomas->translateString("creando.mapa.existe"));
                                break;
                            }

                            foreach ($this->cargador->conseguirGestorDeSecciones()->creandoMapa as $nombre => $clase) {
                                if ($clase->opciones["mundo"] === $mundo) {
                                    $sender->sendMessage($idiomas->translateString("creando.mapa.existe"));
                                    break 2;
                                }
                            }

                            //sabias que cuando te bañas... te mojas el cuerpo?
                            $creando->opciones["mundo"] = $mundo;
                            $sender->sendMessage($idiomas->translateString("creando.mapa.correcto"));
                            break;

                        case "maximo":
                        case "maximum":
                        case "massimo":
                            if (isset($param[1]) === false) {
                                $sender->sendMessage($idiomas->translateString("creando.maximo.numerico"));
                                break;
                            }

                            if ($creando->opciones["maximo"] !== null) {
                                $sender->sendMessage($idiomas->translateString("creando.maximo.existe"));
                                break;
                            }

                            if ($param[1] < 2) {
                                $sender->sendMessage($idiomas->translateString("creando.maximo.menor"));
                                return false;
                            }

                            $creando->opciones["maximo"] = intval($param[1]);
                            $sender->sendMessage($idiomas->translateString("creando.maximo.correcto", array($param[1])));
                            break;

                        case "agregar":
                        case "add":
                        case "adicionar":
                        case "aggiungere":
                            if ($creando->opciones["maximo"] === null or $creando->opciones["mundo"] === null) {
                                $sender->sendMessage($idiomas->translateString("creando.agregar.error"));
                                break;
                            }

                            if (count($creando->opciones["posiciones"]) === $creando->opciones["maximo"]) {
                                $sender->sendMessage($idiomas->translateString("creando.agregar.limite"));
                                break;
                            }

                            $creando->opciones["posiciones"][] = $sender->x . ":" . $sender->y . ":" . $sender->z;
                            $sender->sendMessage($idiomas->translateString("creando.agregar.correcto"));
                            break;

                        case "remover":
                        case "remove":
                        case "deleter":
                        case "rimuovere";
                            if (!empty($creando->opciones["posiciones"])) {
                                unset($creando->opciones["posiciones"][count($creando->opciones["posiciones"]) - 1]);
                                $sender->sendMessage($idiomas->translateString("creando.borrar.correcto"));
                            } else {
                                $sender->sendMessage($idiomas->translateString("creando.borrar.vacio"));
                            }
                            break;

                        case "rtodo":
                        case "rall":
                        case "rtudo":
                        case "rtutto";
                            $creando->opciones = array("mundo" => null, "maximo" => null, "posiciones" => array(), "medianos" => array(), "maximos" => array());
                            $sender->sendMessage($idiomas->translateString("creando.todo.correcto"));
                            break;

                        case "progreso":
                        case "progress":
                        case "progresso":
                            $sender->sendMessage($idiomas->translateString("creando.progreso.correcto", array(($creando->opciones["mundo"] ?? "-"), ($creando->opciones["maximo"] ?? 0), count($creando->opciones["posiciones"]), count($creando->opciones["medianos"]), count($creando->opciones["maximos"]))));
                            break;

                        case "guardar":
                        case "save":
                        case "salvare":
                            if ($creando->terminar()) {
                                $sender->sendMessage($idiomas->translateString("creando.guardar.correcto"));
                            } else {
                                $sender->sendMessage($idiomas->translateString("creando.guardar.error"));
                            }
                            break;

                    }

                } else {

                    switch ($param[0]) {

                        case "arena":
                            if ($sender->hasPermission("skywars.partida")) {

                                if (count($param) !== 4) {
                                    $sender->sendMessage($idiomas->translateString("normal.partida.argumentos"));
                                    break;
                                }

                                array_shift($param);

                                $maximo = intval(array_shift($param));
                                $minimo = intval(array_shift($param));
                                $duos = boolval(array_shift($param));

                                if ($maximo < 2 or $minimo < 2) {
                                    $sender->sendMessage($idiomas->translateString("normal.partida.menor"));
                                    break;
                                }

                                if ($maximo < $minimo) {
                                    $sender->sendMessage($idiomas->translateString("normal.partida.mayor"));
                                    break;
                                }

                                if ($duos === true) {
                                    if ($maximo === 2) {
                                        $maximo = 3;
                                    }
                                    if ($minimo === 2) {
                                        $minimo = 3;
                                    }
                                }

                                $partida = array("maximo" => $maximo, "minimo" => $minimo, "equipo" => intval($duos));

                                $configuracion = $this->cargador->getConfig();
                                $partidas = $configuracion->get("partidas", array());
                                $partidas[count($partidas) + 1] = $partida;
                                $configuracion->set("partidas", $partidas);
                                $configuracion->save();

                                // Baby shark doo doo do da doo doo...
                                $sender->sendMessage($idiomas->translateString("normal.partida.correcto"));
                            }
                            break;

                        case "crear":
                        case "create":
                        case "criar":
                        case "creare":
                            if ($sender->hasPermission("skywars.mapa")) {
                                //agregar por Gestor de Secciones
                                $this->cargador->conseguirGestorDeSecciones()->creandoMapa[$sender->getName()] = new Creador($sender);
                                $sender->sendMessage($idiomas->translateString("normal.crear.correcto"));
                            }
                            break;

                        case "lobby":
                            if ($sender->isOp()) {

                                /** @var SkyWars $partida */
                                foreach ($this->cargador->partidas as $partida) {
                                    if ($partida->tieneMapa() and $partida->conseguirMapa() !== null) {
                                        //ser o no ser...
                                        if ($sender->getLevel()->getFolderName() === $partida->conseguirMapa()->getFolderName()) {
                                            $sender->sendMessage($idiomas->translateString("normal.mapa.existe"));
                                            break 2;
                                        }
                                    }
                                }

                                $this->cargador->getConfig()->set("lobby", ($mapa = $sender->getLevel()->getFolderName()));
                                $this->cargador->getConfig()->save();

                                if ($this->cargador->getConfig()->get("lobby", "") === $mapa) {
                                    //felicidades :D
                                    $sender->sendMessage($idiomas->translateString("normal.mapa.correcto"));
                                } else {
                                    //... :(
                                    $sender->sendMessage($idiomas->translateString("normal.mapa.error"));
                                }
                            }
                            break;

                        case "ayuda":
                        case "help":
                        case "ajuda":
                        case "aiutare":
                            //pronto
                            break;

                    }
                }
            }
        }
        return true;
    }

}