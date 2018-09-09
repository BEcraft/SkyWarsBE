<?php

declare(strict_types=1);

namespace SkyWars\Eventos;

/** SkyWars */
use SkyWars\{
    Cargador, Cartel
};
use SkyWars\Ventana\Ventana;
use SkyWars\Secciones\Usuario;

/** PocketMine */
use pocketmine\Player;
use pocketmine\item\ItemFactory;
use pocketmine\tile\Sign;
use pocketmine\block\Sign as SignC;
use pocketmine\event\{
    Listener, Cancellable
};
use pocketmine\event\block\{
    BlockBreakEvent, BlockPlaceEvent, SignChangeEvent
};
use pocketmine\event\inventory\{
    CraftItemEvent, InventoryEvent, InventoryPickupItemEvent
};
use pocketmine\event\entity\{
    EntityDamageEvent, EntityEvent, EntityDamageByEntityEvent, EntityDamageByChildEntityEvent, EntityLevelChangeEvent
};
use pocketmine\event\player\{
    PlayerDeathEvent,
    PlayerDropItemEvent,
    PlayerExhaustEvent,
    PlayerQuitEvent,
    PlayerKickEvent,
    PlayerTransferEvent,
    PlayerItemHeldEvent,
    PlayerInteractEvent
};

class Eventos implements Listener
{

    public function ejecutarEvento($evento, bool $cancelar = false, bool $sacar = false, bool $espectador = false): ?Usuario
    {
        $jugador = $evento instanceof EntityEvent ? $evento->getEntity() : ($evento instanceof InventoryEvent ? $evento->getInventory()->getHolder() : $evento->getPlayer());
        $jugando = $this->conseguirInstancia()->conseguirGestorDeSecciones()->estaJugando($jugador->getName());

        if ($jugando !== null) {

            if ($jugando->conseguirPartida()->conseguirEstado() !== 2 or $jugando->esEspectador()) {
                if ($evento instanceof Cancellable) {
                    $evento->setCancelled($cancelar);
                }
            }

            if ($sacar === true) {
                if ($espectador === true) {
                    $jugando->asignarEspectador();
                } else {
                    $jugando->terminar($evento instanceof PlayerKickEvent or $evento instanceof PlayerQuitEvent);
                }
            }

        }

        return $jugando;
    }

    public function atacar(EntityDamageEvent $evento): bool
    {
        $victima = $evento->getEntity();

        if ($victima instanceof Player) {
            $jugando = $this->ejecutarEvento($evento, true);
            if ($jugando !== null and $evento->isCancelled() === false) {

                if ($evento instanceof EntityDamageByEntityEvent or $evento instanceof EntityDamageByChildEntityEvent) {

                    $golpeador = $evento instanceof EntityDamageByEntityEvent ? $evento->getDamager() : $evento->getChild()->getOwningEntity();

                    if ($golpeador instanceof Player) {

                        $golpeador = $this->conseguirInstancia()->conseguirGestorDeSecciones()->estaJugando($golpeador->getName());

                        if ($golpeador === null) {
                            return false;
                        }

                        if ($golpeador->esEspectador() === true) {
                            $evento->setCancelled();
                            return false;
                        }

                        if ($jugando->conseguirPartida()->esSolo() === false) {
                            if ($jugando->esEquipo($golpeador->conseguirUsuario()->getName()) === true) {
                                $evento->setCancelled();
                            } else {
                                $jugando->asignarGolpeador($golpeador->conseguirUsuario()->getName());
                            }
                        } else {
                            $jugando->asignarGolpeador($golpeador->conseguirUsuario()->getName());
                        }

                    }
                }

                if ($evento->getFinalDamage() >= $victima->getHealth()) {

                    $evento->setCancelled();
                    $golpeador = $jugando->conseguirGolpeador();

                    if ($golpeador !== null) {
                        $golpeador = $this->conseguirInstancia()->conseguirGestorDeSecciones()->estaJugando($golpeador);
                        if ($golpeador !== null) {
                            $jugando->asignarGolpeador($golpeador->conseguirNick());
                            $golpeador->aumentarAsesinatos();
                            $this->conseguirInstancia()->getServer()->getPluginManager()->callEvent(new AsesinarEvento($golpeador->conseguirUsuario(), $victima));
                        }
                    }

                    $victima->getLevel()->broadcastLevelEvent($victima, 1052);
                    $jugando->asignarEspectador(true);
                }
            } else if ($jugando !== null and $evento->isCancelled() === true) {
                if ($evento instanceof EntityDamageByChildEntityEvent) {

                    if ($jugando->esEspectador() === true) {
                        $jugando->aumentarSospechas();
                    }

                }
                if ($evento->getCause() === EntityDamageEvent::CAUSE_VOID and $jugando->conseguirPartida()->conseguirEstado() < 2) {
                    $victima->teleport($victima->getLevel()->getSafeSpawn());
                    return true;
                }
            }
        }

        return true;
    }

    public function colocarBloque(BlockPlaceEvent $evento): void
    {
        if ($this->ejecutarEvento($evento, true) !== null and $evento->isCancelled() === false) {

            $jugador = $evento->getPlayer();
            $dinamita = $evento->getBlock()->getId();

            if ($dinamita === 46) {
                $evento->setCancelled();
                $evento->getBlock()->ignite();
                $objeto = $evento->getPlayer()->getInventory()->getItemInHand();
                $objeto->setCount($objeto->getCount() - 1);
                $jugador->getInventory()->setItemInHand($objeto->getCount() > 0 ? $objeto : ItemFactory::get(0));
            }

        }
    }

    public function agarrarObjeto(InventoryPickupItemEvent $evento): void
    {
        $this->ejecutarEvento($evento, true);
    }

    public function crear(CraftItemEvent $evento): void
    {
        $this->ejecutarEvento($evento, true);
    }

    public function transferirDeServidor(PlayerTransferEvent $evento): void
    {
        $this->ejecutarEvento($evento, false, true);
    }

    public function expulsarDelServidor(PlayerKickEvent $evento): void
    {
        $this->ejecutarEvento($evento, false, true);
    }

    public function SalirDelServidor(PlayerQuitEvent $evento): void
    {
        $this->ejecutarEvento($evento, false, true);
    }

    public function Agotarse(PlayerExhaustEvent $evento): void
    {
        $this->ejecutarEvento($evento, true);
    }

    public function tirarObjetos(PlayerDropItemEvent $evento): void
    {
        $this->ejecutarEvento($evento, true);
    }

    public function alMorir(PlayerDeathEvent $evento): void
    {
        $this->ejecutarEvento($evento, false, true, true);
    }

    public function mantenerObjeto(PlayerItemHeldEvent $evento): bool
    {
        $jugador = $evento->getPlayer();
        $jugando = $this->conseguirInstancia()->conseguirGestorDeSecciones()->estaJugando($jugador->getName());

        if ($jugando === null) {
            return false;
        }

        $estado = $jugando->conseguirPartida()->conseguirEstado();
        $objeto = $evento->getItem()->getId();

        if ($objeto !== 152) {
            return false;
        }

        if ($estado < 2 or $jugando->esEspectador()) {
            if ($jugando->accionSalir() === true) {
                $jugando->terminar();
            } else {
                $evento->setCancelled();
                $jugador->sendMessage($this->conseguirInstancia()->conseguirLenguaje()::traducir($jugador->getLocale(), "evento.confirmar"));
            }
        }

        return true;
    }

    public function romperBloque(BlockBreakEvent $evento): bool
    {
        $creando = $this->conseguirInstancia()->conseguirGestorDeSecciones()->estaCreando($evento->getPlayer()->getName());

        if ($creando === null) {
            $this->ejecutarEvento($evento, true);
            return true;
        }

        $bloque = $evento->getBlock();

        if ($bloque->getId() === 54) {
            $creando->removerCofre($bloque->x . ":" . $bloque->y . ":" . $bloque->z);
        }

        if ($bloque instanceof SignC) {
            $cartel = $this->cartel($bloque->getLevel()->getTile($bloque));
            if ($cartel instanceof Cartel) {
                $cartel->conseguirCartel()->close();
            }
        }

        return true;
    }

    public function cambiarMundo(EntityLevelChangeEvent $evento): bool
    {
        $entidad = $evento->getEntity();

        if (!($entidad instanceof Player)) {
            return false;
        }

        $jugando = $this->conseguirInstancia()->conseguirGestorDeSecciones()->estaJugando($entidad->getName());

        if ($jugando !== null) {
            if ($jugando->conseguirPartida()->tieneMapa() === false) {
                $jugando->terminar();
            } else {

                $mundo = $evento->getTarget()->getFolderName();

                if ($mundo !== $jugando->conseguirPartida()->conseguirMapa()->getFolderName()) {
                    $jugando->terminar();
                }

            }
        }

        return true;
    }

    public function activarCartel(SignChangeEvent $evento): bool
    {
        $jugador = $evento->getPlayer();

        if ($jugador->getLevel() !== $this->conseguirInstancia()->getServer()->getDefaultLevel()) {
            return false;
        }

        if ($evento->getLines()[0] === "[skywarsbe]" and $jugador->isOp()) {
            $cartel = $jugador->getLevel()->getTile($evento->getBlock());
            if ($cartel instanceof Sign) {
                $this->conseguirInstancia()->carteles[$cartel->__toString()] = new Cartel($cartel);
            }
        }

        return true;
    }

    public function interactuar(PlayerInteractEvent $evento): bool
    {

        if ($evento->getAction() !== 1 and $evento->getAction() !== 3) {
            return false;
        }

        $jugador = $evento->getPlayer();
        $objeto = $evento->getItem();
        $bloque = $evento->getBlock();
        $creando = $this->conseguirInstancia()->conseguirGestorDeSecciones()->estaCreando($jugador->getName());

        if ($creando === null) {

            $jugando = $this->ejecutarEvento($evento, true);
            if ($jugando === null) {

                if ($evento->getBlock()->getId() === 63 or $evento->getBlock()->getId() === 68) {
                    $cartel = $this->cartel($jugador->getLevel()->getTile($evento->getBlock()));

                    if ($cartel instanceof Cartel) {
                        $cartel->ejecutarAccion($jugador);
                    }

                }

                return true;
            }

            if ($evento->getAction() === 1) {

                if ($objeto->canBePlaced() === false) {
                    return false;
                }

                $cara = $evento->getFace();
                $objetivo = $objeto->getBlock();
                $objetivo->position($bloque->getSide($cara));

                if ($objetivo->canBePlacedAt($bloque, $evento->getTouchVector(), $cara, true) === true) {
                    $objetivo = $evento->getBlock();
                }

                if ($objetivo->isSolid() === true) {
                    foreach ($objetivo->getCollisionBoxes() as $caja) {
                        foreach ($jugador->getLevel()->getNearbyEntities($caja) as $troll) {

                            if (!($troll instanceof Player)) {
                                continue;
                            }

                            $atacante = $this->conseguirInstancia()->conseguirGestorDeSecciones()->estaJugando($troll->getName());

                            if ($atacante !== null and $atacante->esEspectador() === true) {
                                $troll->teleport($jugador);
                            }

                        }
                    }
                }

            }

            $id = $objeto->getId();

            if ($id === 345 or $id === 133 or $id === 120) {

                if ($id === 345) {
                    $jugando->actualizarCompass();
                }

                if ($id === 133) {
                    if ($jugando->conseguirPartida()->tieneMapa() === false and $jugando->haVotado() === false) {
                        $accion = function($player, $data)
                        {

                            if ($data === null) {
                                return;
                            }

                            $jugando = Cargador::$instancia->conseguirGestorDeSecciones()->estaJugando($player->getName());

                            if ($jugando !== null) {

                                if ($jugando->haVotado() === true) {
                                    return;
                                }

                                $mapa = $jugando->conseguirPartida()->conseguirNombre($data);

                                if ($mapa === "") {
                                    return;
                                }

                                $jugando->asignarVoto($mapa);
                                $jugando->conseguirPartida()->aumentarVotos($mapa);
                                $player->sendMessage($this->conseguirInstancia()->conseguirLenguaje()::traducir($player->getLocale(), "paquete.votar.correcto", array($mapa)));

                            }

                        };
                        $jugador->sendForm(new Ventana($jugando->conseguirPartida()->enviarDatos(), $accion));
                    }
                }

                if ($id === 120) {
                    if ($jugando->conseguirPartida()->conseguirEstado() < 2) {

                        $llamada = function($player, $data) {

                            if ($data === null) {
                                return;
                            }

                            $jugando = Cargador::$instancia->conseguirGestorDeSecciones()->estaJugando($player->getName());

                            if ($jugando !== null) {

                                $kit = Cargador::$instancia->conseguirGestorDeKits()->conseguirNombre($data);

                                if (Cargador::$instancia->conseguirGestorDeKits()->tienePermiso($player, $kit) === true) {
                                    $jugando->asignarKit($kit);
                                    $player->sendMessage($this->conseguirInstancia()->conseguirLenguaje()::traducir($player->getLocale(), "paquete.kit.correcto", array($kit)));
                                }

                            }
                        };

                        $jugador->sendForm(new Ventana($this->conseguirInstancia()->conseguirGestorDeKits()->enviarDatos(), $llamada));
                       // $this->agregarPaquete($jugador, $this->conseguirInstancia()->conseguirGestorDeKits()->enviarDatos());
                    }
                }

            }

        } else {
            if ($bloque->getId() === 54) {

                if ($objeto->getId() !== 265 and $objeto->getId() !== 264) {
                    return false;
                }

                $evento->setCancelled();
                $posicion = $bloque->x . ":" . $bloque->y . ":" . $bloque->z;

                if ($objeto->getId() === 265) {
                    $creando->agregarMediano($posicion);
                } else {
                    $creando->agregarMaximo($posicion);
                }

            }
        }

        return true;
    }

    /**
     * Consigue algún cartel (Si existe).
     * @param  Sign $cartel
     * @return null | Cartel
     */
    private function cartel(?Sign $cartel): ?Cartel
    {
        return $this->conseguirInstancia()->carteles[$cartel->__toString()] ?? null;
    }

    /**
     * Cargador
     * @return Cargador
     */
    public function conseguirInstancia(): Cargador
    {
        return Cargador::$instancia;
    }

}