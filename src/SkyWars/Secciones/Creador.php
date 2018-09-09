<?php

declare(strict_types=1);

namespace SkyWars\Secciones;

/** PocketMine */
use pocketmine\Player;

class Creador
{

    /**
     * Jugador.
     * @var Player
     */
    private $usuario;

    /**
     * Opciones para el mapa.
     * @var array
     */
    public $opciones = array("mundo" => null, "maximo" => null, "posiciones" => array(), "medianos" => array(), "maximos" => array());

    /**
     * Creador constructor.
     * @param Player $usuario
     */
    public function __construct(Player $usuario)
    {
        $this->usuario = $usuario;
    }

    /**
     * Agregar una posición para cofre mediano
     * @param  string $texto
     * @return void
     */
    public function agregarMediano(string $texto): void
    {
        if ($this->existePosicion($texto) === false) {
            $this->opciones["medianos"][] = $texto;
            $this->usuario->sendMessage("§7[§e" . count($this->opciones["medianos"]) . "§7]");
        }
    }

    /**
     * Agregar una posición para cofre máximo.
     * @param  string $texto
     * @return void
     */
    public function agregarMaximo(string $texto): void
    {
        if ($this->existePosicion($texto) === false) {
            $this->opciones["maximos"][] = $texto;
            $this->usuario->sendMessage("§7[§e" . count($this->opciones["maximos"]) . "§7]");
        }
    }

    /**
     * Elimina una posición de la lista.
     * @param  string $texto
     * @return void
     */
    public function removerCofre(string $texto): void
    {
        if (in_array($texto, $this->opciones["medianos"])) {
            unset($this->opciones["medianos"][array_search($texto, $this->opciones["medianos"])]);
            $this->usuario->sendMessage("§7[§c" . count($this->opciones["medianos"]) . "§7]");
        }

        if (in_array($texto, $this->opciones["maximos"])) {
            unset($this->opciones["maximos"][array_search($texto, $this->opciones["maximos"])]);
            $this->usuario->sendMessage("§7[§c" . count($this->opciones["maximos"]) . "§7]");
        }
    }

    /**
     * Verificar si una posicion ya ha sido agregada.
     * @param  string $texto
     * @return boolean
     */
    public function existePosicion(string $texto): bool
    {
        if (in_array($texto, $this->opciones["medianos"])) {
            return true;
        }

        if (in_array($texto, $this->opciones["maximos"])) {
            return true;
        }

        return false;
    }

    /**
     * Termina la sección.
     * @param  boolean $forzar
     * @return boolean
     */
    public function terminar(bool $forzar = false): bool
    {
        if ($forzar === true) {
            GestorSeccion::conseguirInstancia()->conseguirGestorDeSecciones()->eliminarCreador($this->usuario->getName());
            return true;
        } else {

            if ($this->opciones["mundo"] === null) {
                return false;
            }

            if ($this->opciones["maximo"] === null) {
                return false;
            }

            if (count($this->opciones["posiciones"]) !== $this->opciones["maximo"]) {
                return false;
            }

            GestorSeccion::conseguirInstancia()->conseguirGestorDeMapas()->crearArchivo($this->opciones["mundo"]);
            GestorSeccion::conseguirInstancia()->conseguirGestorDeMapas()->guardarMapa($this->opciones);
            GestorSeccion::conseguirInstancia()->conseguirGestorDeSecciones()->eliminarCreador($this->usuario->getName());
        }

        return true;
    }

}