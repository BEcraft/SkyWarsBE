<?php

namespace SkyWars\Ventana;

/** SkyWars */
use SkyWars\Cargador;

/** PocketMine */
use pocketmine\Player;
use pocketmine\form\Form;

class Ventana implements Form
{

	/**
	 * Datos.
	 * @var array
	 */
	private $datos;

	/**
	 * Acción al terminar.
	 * @var callable
	 */
	private $proceso;

	public function __construct(array $datos, callable $proceso)
	{
		$this->datos = $datos;
		$this->proceso = $proceso;
	}

    /**
     * Respuesta del jugador.
     * @param  Player $player
     * @param  mixed  $data
     * @return void
     */
	public function handleResponse(Player $player, $data): void
	{
        $llamada = $this->proceso;
        $llamada($player, $data);
	}

	public function jsonSerialize()
	{
		return $this->datos;
	}

}