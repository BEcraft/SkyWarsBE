<?php

declare(strict_types=1);

namespace SkyWars\Mapas;

/** PocketMine */
use pocketmine\math\Vector3;

class Mapa
{

    /**
     * Lista de posiciones para este mapa.
     * @var array
     */
    public $listaDePosiciones = array();

    /**
     * Lista de posiciones de los cofres tipo medianos.
     * @var array
     */
    public $cofresMedianos = array();

    /**
     * Lista de posiciones de los cofres tipo maximo.
     * @var array
     */
    public $cofresMaximos = array();

    public function __construct(array $posiciones, array $maximos, array $medianos)
    {
        $this->listaDePosiciones = $posiciones;
        $this->cofresMedianos = $medianos;
        $this->cofresMaximos = $maximos;
    }

    /**
     * Consigue la lista de posiciones de los puntos a aparecer.
     * @return array
     */
    public function conseguirPosiciones(): array
    {
        return $this->listaDePosiciones;
    }

    /**
     * Retorna el tipo de objetos para rellenar un cofre.
     * @param  Vector3 $vector
     * @return string
     */
    public function conseguirTipoDeCofre(Vector3 $vector): string
    {
        
        foreach ($this->cofresMedianos as $vector3) {
            if ($vector3->equals($vector)) {
                return "medianos";
            }
        }

        foreach ($this->cofresMaximos as $vector3) {
            if ($vector3->equals($vector)) {
                return "maximos";
            }
        }

        return "normales";
    }

}