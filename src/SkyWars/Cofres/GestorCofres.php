<?php

declare(strict_types=1);

namespace SkyWars\Cofres;

/** SkyWars */
use SkyWars\Cargador;

/** PocketMine */
use pocketmine\level\Level;
use pocketmine\tile\Chest;
use pocketmine\item\{
    Tool, Armor, ItemBlock, Food, ProjectileItem
};

class GestorCofres
{

    /**
     * Arreglo con todos los objetos.
     * @var array
     */
    private $recursos = array();

    public function __construct(array $cofres)
    {
        $cantidad = count($cofres);
        while ($cantidad > 0) {
            $nombre = array_keys($cofres)[--$cantidad];
            $objetos = $cofres[$nombre];
            $this->recursos[$nombre] = new Objetos($objetos);
        }
    }

    /**
     * Recarga los cofres del mapa.
     *
     * @param  Level  $mundo
     * @param  string $nombre
     *
     * @return void
     */
    public function cargarCofres(Level $mundo, string $nombre): void
    {
        $mapa = $this->conseguirInstancia()->conseguirGestorDeMapas()->conseguirMapa($nombre);

        if ($mapa !== null) {
            foreach ($mundo->getTiles() as $cofre) {

                if(!($cofre instanceof Chest)) {
                    continue;
                }

                $inventario = $cofre->getInventory();
                $inventario->clearAll(true);

                $tipo = $this->recursos[$mapa->conseguirTipoDeCofre($cofre->asVector3())] ?? null;

                if (!($tipo instanceof Objetos)) {
                    continue;
                }

                $tipo = $tipo->conseguirObjetos();
                $capacidad = $cofre->getInventory()->getDefaultSize();
                $repetidos = array();

                for ($i = 0; $i < $capacidad; ++$i) {

                    if (mt_rand(0, 3) !== 1) {
                        continue;
                    }

                    $tipoDeObjeto = $this->conseguirRandom(mt_rand(1, 100));
                    $objeto = $tipo[$tipoDeObjeto][array_rand($tipo[$tipoDeObjeto])];

                    if ($objeto instanceof Tool or $objeto instanceof Armor) {
                        if (!in_array($objeto->getId(), $repetidos)) {
                            $repetidos[] = $objeto->getId();
                        }else{
                            continue;
                        }
                    }

                    $cantidad = $objeto->getCount();

                    if (($objeto instanceof ItemBlock and $objeto->getCount() >= 5) or (($objeto instanceof Food or $objeto instanceof ProjectileItem) and $objeto->getCount() > 1)) {
                        $objeto->setCount(mt_rand(0, 1) === 1 ? intval(round($objeto->getCount() / 2)) : $objeto->getCount()); //no se si sea la mejor opción...
                    }

                    $inventario->setItem($i, $objeto);
                }

            }
        }
    }

    /**
     * Consigue el tipo de objeto mediante un por ciento.
     * @param  int    $numero
     * @return string
     */
    private function conseguirRandom(int $numero): string {
        switch ($numero) {

            case ($numero > 0 and $numero <= 30):
                return "bloques";

            case ($numero > 30 and $numero <= 35):
                return "comida";

            case ($numero > 35 and $numero <= 55):
                return "herramientas";

            case ($numero > 55 and $numero <= 80):
                return "armadura";

            case ($numero > 80 and $numero < 95):
                return "tirar";
            
            default:
                return "otros";
                
        }
    }

    /**
     * Conseguir el cargador.
     * @return Cargador
     */
    public function conseguirInstancia(): Cargador
    {
        return Cargador::$instancia;
    }

}