<?php

declare(strict_types=1);

namespace SkyWars\Eventos;

/** PocketMine */
use pocketmine\Player;

class RequerirNickEvento extends SkyWarsEvento
{

    /**
     * Nick del jugador
     * @var string
     */
    public $nick = "";

    public function __construct(Player $jugador)
    {
        parent::__construct($jugador);
    }

    /**
     * Asigna un nuevo nick para este jugador.
     * @param  string $nick
     * @return void
     */
    public function asignarNick(string $nick): void
    {
        $this->nick = $nick;
    }

    public function setNick(string $nick): void
    {
        $this->asignarNick($nick);
    }

    /**
     * Consigue el nick para el jugador.
     * @return string
     */
    public function conseguirNick(): string
    {
        return $this->nick;
    }

    public function getNick(): string
    {
        return $this->conseguirNick();
    }

}