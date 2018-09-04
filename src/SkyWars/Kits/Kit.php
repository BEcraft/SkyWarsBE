<?php

declare(strict_types=1);

namespace SkyWars\Kits;

/** SkyWars */
use SkyWars\Cargador;

/** PocketMine */
use pocketmine\item\{
    Tool, Armor, ItemFactory
};
use pocketmine\item\enchantment\{
    Enchantment, EnchantmentInstance
};
use pocketmine\permission\Permission;

class Kit
{

    /**
     * Arreglo con todos los  objetos de este kit.
     * @var array
     */
    private $objetos = array();

    /**
     * Permiso para este kit (si tiene).
     * @var null | string
     */
    private $permiso;

    public function __construct(array $datos, bool $operador)
    {
        foreach ($datos as $identificador => $opciones) {
            if (is_numeric($identificador)) {
                if (isset($opciones["meta"]) and isset($opciones["cantidad"])) {

                    $meta = is_numeric($opciones["meta"]) === false ? 0 : intval($opciones["meta"]);
                    $cantidad = is_numeric($opciones["cantidad"]) === false ? 1 : (int)$opciones["cantidad"];

                    $objeto = ItemFactory::get(intval($identificador), $meta, $cantidad);

                    if (isset($opciones["nombre"])) {
                        $objeto->setCustomName((string)$opciones["nombre"]);
                    }

                    if (isset($opciones["encantamientos"]) and ($objeto instanceof Armor or $objeto instanceof Tool)) {
                        $encantamientos = array();
                        foreach ((array)$opciones["encantamientos"] as $valor) {
                            if (strpos((string)$valor, ":") !== false) {
                                $total = array_values(array_filter(explode(":", $valor), function ($i) {
                                    return is_numeric($i);
                                }));
                                if (count($total) === 2) {
                                    $ench = new EnchantmentInstance(Enchantment::getEnchantment((int)$total[0]));
                                    $encantamientos[] = $ench->setLevel((int)$total[1]);
                                }
                            }
                        }
                    }

                    if (isset($encantamientos)) {
                        foreach ($encantamientos as $encantamiento) {
                            $objeto->addEnchantment($encantamiento);
                        }
                    }

                    $this->objetos[] = $objeto;
                }
            } else {
                if ($identificador === "permiso") {
                    if (!is_array($opciones) and strlen($opciones) > 0) {

                        $permiso = new Permission($opciones, "", $operador === true ? "op" : "notop");
                        ($manager = Cargador::$instancia->getServer()->getPluginManager())->addPermission($permiso);

                        if ($manager->getPermission($opciones) !== null) {
                            $this->permiso = $opciones;
                        }
                    }
                }
            }
        }
    }

    /**
     * Verifica si este kit tiene permiso.
     * @return bool
     */
    public function tienePermiso(): bool
    {
        return $this->permiso !== null;
    }

    /**
     * Retorna el permiso para este kit.
     * @return null | string
     */
    public function conseguirPermiso(): ?string
    {
        return $this->permiso;
    }

    /**
     * Retorna todos los objetos que contiene este kit.
     * @return array
     */
    public function conseguirObjetos(): array
    {
        return $this->objetos;
    }

}