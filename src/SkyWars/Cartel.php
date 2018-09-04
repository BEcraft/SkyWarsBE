<?php

declare(strict_types=1);

namespace SkyWars;

/** SkyWars */
use SkyWars\Partidas\SkyWars;

/** PocketMine */

use pocketmine\Player;
use pocketmine\tile\Sign;

class Cartel
{

    /**
     * Partida de SkyWars.
     * @var null | SkyWars
     */
    private $partida;

    /**
     * Cartel.
     * @var Sign
     */
    private $cartel;

    /** Texto */
    private const PREFIJO = "§eSkyWars";


    public function __construct(Sign $cartel)
    {
        $this->cartel = $cartel;
    }

    /**
     * Asigna una nueva partida.
     * @param  null | SkyWars $partida
     * @return void
     */
    public function asignarPartida(?SkyWars $partida): void
    {
        $this->partida = $partida;
    }

    /**
     * Consigue la partida de skywars.
     * @return null | SkyWars
     */
    public function conseguirPartida(): ?SkyWars
    {
        return $this->partida;
    }

    /**
     * Consigue el cartel.
     * @return null | Sign
     */
    public function conseguirCartel(): ?Sign
    {
        return $this->cartel;
    }

    /**
     * Pequena animación XD
     * @var string
     */
    private static $animacion = "";

    /**
     * Actualiza la informacion del cartel.
     * @return void
     */
    public function actualizarInformacion(): void
    {
        $partida = $this->conseguirPartida();

        if ($partida instanceof SkyWars) {
            //identificador de la partida disponible, ya que no usa el nombre del mapa...
            $identificador = \strtr($partida->conseguirID(), array("SkyWars=" => "§a"));

            //pensé en poner la cantidad por porcentajes pero creo que será asi.
            $cantidad = "§f" . $partida->conseguirCantidad() . "/" . $partida->conseguirMaximo();

            //alguna otra idea para el tipo de partida?
            $tipo = "§6" . ($partida->esSolo() ? "█" : "██");

            //asignar texto al cartel.
            $this->conseguirCartel()->setText(self::PREFIJO, $identificador, $cantidad, $tipo);
        } else {
            if (strlen(self::$animacion) > 3 * 6) {
                //reinciar
                self::$animacion = "";
            } else {
                //agregar
                self::$animacion .= "§c█";
            }

            //OpenInglich mai frien
            $this->conseguirCartel()->setText(self::PREFIJO, self::$animacion, "§fNOT SIGNAL");
        }

    }

    /**
     * Agrega un nuevo jugador a la partida si esta esta disponible.
     * @param  Player $jugador
     * @return void
     */
    public function ejecutarAccion(Player $jugador): void
    {
        $partida = $this->conseguirPartida();
        if ($partida instanceof SkyWars) {

            if ($partida->estaDisponible()) {
                $partida->agregarJugador($jugador); //Bienvenido la partida chaval!
            }

        }
    }

}