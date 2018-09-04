<?php

declare(strict_types=1);

namespace SkyWars\Cofres;

/** PocketMine */
use pocketmine\item\{
    ItemFactory, ItemBlock, Tool, Armor, Food, ProjectileItem
};

class Objetos
{

    /**
     * Arreglo con todos los objetos que trae este cofre.
     * @var array
     */
    private $objetos = array(
        "bloques"      => array(),
        "comida"       => array(),
        "herramientas" => array(),
        "armadura"     => array(),
        "tirar"        => array(),
        "otros"        => array()
    );

    public function __construct(array $objetos)
    {
        foreach ($objetos as $identificador => $datos) {
            if (is_numeric($identificador)) {

                foreach ($datos as $dato) {
                    if (!is_numeric($dato)) {
                        unset($datos[array_search($dato, $datos)]);
                    }
                }
                //===================================================================
                if (count($datos) === 2) {

                    $objeto = ItemFactory::get($identificador, $datos[0], $datos[1]);

                    if ($objeto instanceof ItemBlock) {
                        $this->objetos["bloques"][] = $objeto;
                    } else if ($objeto instanceof Tool) {
                        $this->objetos["herramientas"][] = $objeto;
                    } else if ($objeto instanceof Armor) {
                        $this->objetos["armadura"][] = $objeto;
                    } else if ($objeto instanceof Food) {
                        $this->objetos["comida"][] = $objeto;
                    } else if ($objeto instanceof ProjectileItem) {
                        $this->objetos["tirar"][] = $objeto;
                    } else {
                        $this->objetos["otros"][] = $objeto;
                    }
                }
                //===================================================================
            }
        }
    }

    /**
     * Conseguir todos los objetos disponibles.
     * @return array
     */
    public function conseguirObjetos(): array
    {
        return $this->objetos;
    }

}