<?php

declare(strict_types=1);

namespace SkyWars\Tiempo;

/** SkyWars */
use SkyWars\Cargador;

/** PocketMine */
use pocketmine\scheduler\Task;
use SkyWars\Cartel;
use SkyWars\Partidas\SkyWars;

class Carteles extends Task
{

    /**
     * Cargador
     * @var Cargador
     */
    private $cargador;

    public function __construct(Cargador $cargador)
    {
        $this->cargador = $cargador;
    }

    /**
     * Actualizar los carteles
     * @param int $tick
     * @return void
     */
    public function onRun(int $tick): void
    {
        /** @var Cartel $cartel */
        foreach ($this->cargador->carteles as $posicion => $cartel) {

            if ($cartel->conseguirCartel()->isClosed()) {
                unset($this->cargador->carteles[$posicion]);
                continue;
            }

            if ($cartel->conseguirPartida() === null) {
                /** @var SkyWars $partida */
                foreach ($this->cargador->partidas as $partida) {

                    if ($partida->conseguirCartel() !== null and $partida->conseguirCartel()->isClosed()) {
                        $partida->asignarCartel(null);
                    }

                    if ($partida->conseguirCartel() === null) {
                        if ($partida->estaDisponible()) {
                            $partida->asignarCartel($cartel->conseguirCartel());
                            $cartel->asignarPartida($partida);
                            continue 2;
                        }
                    }

                }
            } else {
                if ($cartel->conseguirPartida()->estaDisponible() === false) {
                    $cartel->conseguirPartida()->asignarCartel(null);
                    $cartel->asignarPartida(null);
                }
            }
        }

        foreach ($this->cargador->carteles as $posicion => $cartel) {
            $cartel->actualizarInformacion();
        }

    }

}