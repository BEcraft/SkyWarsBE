<?php

declare(strict_types=1);

namespace SkyWars\Kits;

/** PocketMine */
use pocketmine\Player;

class KitsGestor
{

    /**
     * Todos los kits disponibles.
     * @var array
     */
    private $enTotal = array();

    public function __construct(array $lista, bool $operador)
    {
        foreach ($lista as $nombre => $datos) {
            $this->enTotal[(string)$nombre] = new Kit($datos, $operador);
        }
    }

    /**
     * Conseguir todos los kits disponibles
     * @return array
     */
    public function conseguirKits(): array
    {
        return $this->enTotal;
    }

    /**
     * Verifica si cierto kit existe
     * @param  string $nombre
     * @return boolean
     */
    public function estaDisponible(string $nombre): bool
    {
        return array_key_exists($nombre, $this->enTotal);
    }

    /**
     * Consigue cierto kit
     * @param  string $nombre
     * @return null | Kit
     */
    public function conseguirKit(string $nombre): ?Kit
    {
        if ($this->estaDisponible($nombre)) {
            return $this->enTotal[$nombre];
        }

        return null;
    }

    /**
     * Verifica si cierto jugador tiene permiso para el kit.
     * @param  Player $jugador
     * @param  string $nombre
     * @return boolean
     */
    public function tienePermiso(Player $jugador, string $nombre): bool
    {
        $kit = $this->conseguirKit($nombre);
        if ($kit instanceof Kit) {
            if ($kit->tienePermiso()) {
                return $jugador->hasPermission($kit->conseguirPermiso());
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * Agrega los objetos del kit al jugador.
     * @param  Player $jugador
     * @param  string $nombre
     * @return void
     */
    public function agregarKit(Player $jugador, string $nombre): void
    {
        if ($nombre !== "") {
            foreach ($this->conseguirKit($nombre)->conseguirObjetos() as $objeto) {
                $jugador->getInventory()->addItem($objeto);
            }
        }
    }

    /**
     * Enviar los datos para el paquete.
     * @return array
     */
    public function enviarDatos(): array
    {
        $botones = array(
            "type"    => "form",
            "title"   => "Kits",
            "content" => "",
            "buttons" => array()
        );

        foreach (array_keys($this->conseguirKits()) as $nombre) {
            $botones["buttons"][] = array("text" => $nombre);
        }

        return $botones;
    }

    /**
     * Consigue el nombre de cierto kit.
     * @param  int $boton
     * @return string
     */
    public function conseguirNombre(int $boton): string
    {
        return array_keys($this->conseguirKits())[$boton] ?? "";
    }

}