<?php

declare(strict_types=1);

namespace SkyWars\Eventos;

/** PocketMine */
use pocketmine\Player;
use pocketmine\event\player\PlayerEvent;

abstract class SkyWarsEvento extends PlayerEvent
{

    public function __construct(Player $jugador)
    {
        $this->player = $jugador;
    }

    /**
     * Conseguir jugador
     * @return Player
     */
    public function conseguirJugador(): Player
    {
        return $this->player;
    }

}