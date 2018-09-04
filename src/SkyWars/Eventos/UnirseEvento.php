<?php

declare(strict_types=1);

namespace SkyWars\Eventos;

/** PocketMine */
use pocketmine\Player;
use pocketmine\event\Cancellable;

class UnirseEvento extends SkyWarsEvento implements Cancellable
{

    /**
     * Identificador de la partida a la que el jugador se unirá.
     * @var string
     */
    public $partida;

    public function __construct(Player $jugador, string $identificador)
    {
        parent::__construct($jugador);
        $this->partida = $identificador;
    }

    /**
     * Retorna el identificador de la partida.
     * @return string
     */
    public function conseguirPartida(): string
    {
        return $this->partida;
    }

    public function getGame(): string
    {
        return $this->conseguirPartida();
    }

}