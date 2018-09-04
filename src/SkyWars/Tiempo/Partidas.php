<?php

declare(strict_types=1);

namespace SkyWars\Tiempo;

/** SkyWars */
use SkyWars\Cargador;

/** PocketMine */
use pocketmine\scheduler\Task;

class Partidas extends Task
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

    /** Actualizar partidas */
    public function onRun(int $tick): void
    {
        foreach ($this->cargador->partidas as $partida) {
            $partida->actualizarPartida();
        }
    }

}