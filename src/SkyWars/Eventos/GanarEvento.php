<?php

declare(strict_types=1);

namespace SkyWars\Eventos;

/** PocketMine */
use pocketmine\Player;

class GanarEvento extends SkyWarsEvento
{

    /**
     * Numero de asesinatos.
     * @var integer
     */
    public $asesinatos;

    public function __construct(Player $jugador, int $asesinatos)
    {
        parent::__construct($jugador);
        $this->asesinatos = $asesinatos;
    }

    /**
     * Conseguir todos los asesinatos que el jugador cometió en la partida.
     * @return integer
     */
    public function conseguirAsesinatos(): int
    {
        return $this->asesinatos;
    }

    public function getKills(): int
    {
        return $this->conseguirAsesinatos();
    }

}