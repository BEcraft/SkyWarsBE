<?php

declare(strict_types=1);

namespace SkyWars\Eventos;

/** PocketMine */
use pocketmine\Player;

class AsesinarEvento extends SkyWarsEvento
{

    /**
     * Victima
     * @var Player
     */
    private $victima;

    public function __construct(Player $asesino, Player $victima)
    {
        parent::__construct($asesino);
        $this->victima = $victima;
    }

    /**
     * Conseguir la victima
     * @return Player
     */
    public function conseguirVictima(): Player
    {
        return $this->victima;
    }

    public function getVictim(): Player
    {
        return $this->conseguirVictima();
    }

}