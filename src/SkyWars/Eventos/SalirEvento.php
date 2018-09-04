<?php

declare(strict_types=1);

namespace SkyWars\Eventos;

/** PocketMine */
use pocketmine\Player;

class SalirEvento extends SkyWarsEvento
{

    /**
     * Numero de asesinatos.
     * @var integer
     */
    public $asesinatos = 0;

    public function __construct(Player $jugador, int $asesinatos)
    {
        parent::__construct($jugador);
        $this->asesinatos = $asesinatos;
    }

    /**
     * Numero de asesinatos cometidos por el jugador.
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