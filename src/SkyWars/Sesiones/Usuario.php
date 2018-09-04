<?php

declare(strict_types=1);

namespace SkyWars\Sesiones;

/** SkyWars */
use SkyWars\Partidas\SkyWars;
use SkyWars\Eventos\RequerirNickEvento;

/** PocketMine */
use pocketmine\Player;
use pocketmine\item\ItemBlock;
use pocketmine\entity\{
    Effect, EffectInstance
};
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\SetSpawnPositionPacket;

class Usuario
{

    /**
     * Jugador
     * @var Player
     */
    public $usuario;

    /**
     * Partida de SkyWars en la que esta jugando.
     * @var SkyWars
     */
    private $partida;

    /**
     * Kit del jugador
     * @var string
     */
    public $kit = "";

    /**
     * Cantidad de asesinatos.
     * @var integer
     */
    public $asesinatos = 0;

    /**
     * Voto del jugador.
     * @var string | boolean
     */
    private $voto = false;

    /**
     * Estado del modo espectador.
     * @var boolean
     */
    public $espectador = false;

    /**
     * Nombre y tiempo de la ultíma persona que ha golpeado a este jugador.
     * @var array
     */
    private $golpeador = array("nombre" => "", "tiempo" => 0);

    /**
     * Nombre del compañero de equipo (Solo si la partida es para Duos).
     * @var string
     */
    private $equipo = "";

    /**
     * Solo por si el jugador tiene un nick proporcionado por otro plugin, ver: RequerirNickEvento.php
     * @var string
     */
    public $nick = "";

    /**
     * Solo para veríficar cuando el jugador quiera salir.
     * @var integer
     */
    public $salir = 0;

    /**
     * Posicion del jugador antes de entrar
     * @var Position
     */
    private $posicion;

    /**
     * Sospechas de que el jugador esta interfiriendo con la partida
     * @var integer
     */
    private $sospechas = 0;

    /** Vida del jugador al entrar */
    private const VIDA = 20;

    /** Comida al entrar */
    private const COMIDA = 20;

    /** Experiencia del jugador al entrar */
    public const EXPERIENCIA = 1;
    public const EXPERIENCIA_NIVEL = 0;

    /**
     * Usuario constructor.
     * @param Player  $usuario
     * @param SkyWars $partida
     */
    public function __construct(Player $usuario, SkyWars $partida)
    {
        $this->usuario = $usuario;
        $this->partida = $partida;

        if ($usuario->getGamemode() != $usuario::ADVENTURE) {
            $usuario->setGamemode($usuario::ADVENTURE);
        }

        $this->posicion = clone $usuario->getPosition();

        GestorSeccion::conseguirInstancia()->getServer()->getPluginManager()->callEvent($evento = new RequerirNickEvento($usuario));

        $this->nick = $evento->conseguirNick();

        $usuario->setHealth(self::VIDA);
        $usuario->setFood(self::COMIDA);
        $usuario->setXpProgress(self::EXPERIENCIA);
        $usuario->setXpLevel(self::EXPERIENCIA_NIVEL);

        $usuario->getInventory()->clearAll(true);
        $usuario->getArmorInventory()->clearAll(true);
        $usuario->getInventory()->setHeldItemIndex(4);

        $this->conseguirUsuario()->getInventory()->setItem(8, (ItemBlock::get(152))->setCustomName("§c§lLOBBY"));

        $usuario->getInventory()->addItem((ItemBlock::get(133))->setCustomName(GestorSeccion::conseguirInstancia()->conseguirLenguaje()->translateString("votar")));
        $usuario->getInventory()->addItem((ItemBlock::get(120))->setCustomName(GestorSeccion::conseguirInstancia()->conseguirLenguaje()->translateString("kit")));

        $usuario->teleport(GestorSeccion::conseguirInstancia()->conseguirLobby()->getSafeSpawn());
    }

    /**
     * Asignar el modo espectador para el jugador.
     * @param boolean $haMuerto
     * @return void
     */
    public function asignarEspectador(bool $haMuerto = false): void
    {

        $this->espectador = true;

        $usuario = $this->conseguirUsuario();

        foreach ($usuario->getInventory()->getContents() as $objeto) {
            $usuario->getLevel()->dropItem($usuario, $objeto);
        }

        $usuario->getInventory()->clearAll(true);
        $usuario->getArmorInventory()->clearAll(true);

        $usuario->teleport($usuario->getSpawn());

        $usuario->getInventory()->setItem(8, (ItemBlock::get(152))->setCustomName("§c§lLOBBY"));
        $usuario->getInventory()->setHeldItemIndex(1);

        $this->agregarEfecto();

        $usuario->addTitle(GestorSeccion::conseguirInstancia()->conseguirLenguaje()->translateString("partida.perder.titulo"), "", 15, 15, 15);

        /** @var Usuario $jugador */
        foreach ($this->conseguirPartida()->conseguirJugadoresTotales() as $jugador) {
            $jugador->conseguirUsuario()->hidePlayer($usuario);
        }

        $usuario->setGamemode($usuario::ADVENTURE);
        $usuario->setHealth(self::VIDA);
        $usuario->setFood(self::COMIDA);
        $usuario->setAllowFlight(true);
        $usuario->setFlying(true);

        $this->conseguirPartida()->enviarMensajes($this->enviarMensaje($haMuerto));
    }

    /**
     * Agrega el efecto de ceguera al jugador.
     * @return void
     */
    public function agregarEfecto(): void
    {
        $this->conseguirUsuario()->addEffect((new EffectInstance(Effect::getEffect(15)))->setDuration(3 * 20)->setAmplifier(10));
    }

    /**
     * Retorna con un mensaje dependiendo si ha muerto o ha salido.
     * @param  boolean $muerto
     * @return string
     */
    public function enviarMensaje(bool $muerto = false): string
    {
        $golpeador = $this->conseguirGolpeador();
        $traductor = GestorSeccion::conseguirInstancia()->conseguirLenguaje();

        if ($muerto === true) {

            if ($golpeador === null) {
                $mensaje = $traductor->translateString("evento.morir.uno", array($this->conseguirNick()));
            } else {
                $mensaje = $traductor->translateString("evento.morir.dos", array($this->conseguirNick(), $golpeador));
            }

        } else {
            $mensaje = $traductor->translateString("evento.salir", array($this->conseguirNick()));
        }

        return $mensaje ?? "";
    }

    /**
     * Preparar el jugador para comenzar la partida.
     * @return void
     */
    public function iniciar(): void
    {
        $usuario = $this->conseguirUsuario();

        $usuario->setGamemode(0);
        $usuario->setImmobile(true);
        $usuario->getInventory()->clearAll(true);
        $usuario->getArmorInventory()->clearAll(true);

        //agregar efecto
        $this->agregarEfecto();
    }

    /**
     * Agrega el kit que ha seleccionado el jugador.
     * @return void
     */
    public function agregarKit(): void
    {
        GestorSeccion::conseguirInstancia()->conseguirGestorDeKits()->agregarKit($this->conseguirUsuario(), $this->conseguirKit());
    }

    /**
     * Restablecer jugador.
     * @param boolean $votar
     * @return void
     */
    public function terminar($votar = false): void
    {
        //solo por si ha votado
        if ($this->haVotado() and $this->conseguirPartida()->conseguirEstado() < 2) {
            $this->conseguirPartida()->disminuirVotos($this->conseguirVoto());
        }

        $usuario = $this->conseguirUsuario();
        $this->conseguirPartida()->removerJugador($usuario, $this->conseguirAsesinatos());

        if ($this->conseguirPartida()->conseguirEstado() > 1) {
            $usuario->sendMessage($this->calcularJuego());
        }

        if ($usuario->isImmobile()) {
            $usuario->setImmobile(false);
        }

        $usuario->sendMessage(GestorSeccion::conseguirInstancia()->conseguirLenguaje()->translateString("evento.abandonar"));

        $usuario->setGamemode(intval(GestorSeccion::conseguirInstancia()->getConfig()->get("juego", 0)));

        $usuario->setHealth(self::VIDA);
        $usuario->setFood(self::COMIDA);

        $usuario->setXpProgress(0);
        $usuario->setXpLevel(self::EXPERIENCIA_NIVEL);

        $usuario->getInventory()->clearAll(true);
        $usuario->getArmorInventory()->clearAll(true);

        $usuario->removeAllEffects();

        if ($this->esEspectador()) {
            foreach (GestorSeccion::conseguirInstancia()->getServer()->getOnlinePlayers() as $jugador) {
                $jugador->showPlayer($usuario);
            }
        }

        $posicion = $this->conseguirPosicion();

        if ($votar === false) {
            $usuario->teleport($posicion);
        } else {
            $usuario->setPosition($posicion);
        }

    }

    /**
     * Ejecutar accion al salir
     * @return boolean
     */
    public function accionSalir(): bool
    {
        if ($this->salir < 0.0001) {
            $this->salir = microtime(true);
        }

        $tiempo = round(microtime(true) - $this->salir, 3);

        if ($tiempo >= 0.0001 and $tiempo <= 5) {
            return true;
        } else {
            $this->salir = microtime(true);
        }

        return false;
    }

    /**
     * Actualizar compass
     * @return void
     */
    public function actualizarCompass(): void
    {
        $usuario = $this->conseguirUsuario();
        if ($usuario->getInventory()->getItemInHand()->getId() === 345) {

            $cercanos = array();

            foreach ($this->conseguirPartida()->conseguirJugadores() as $jugador) {
                if ($jugador !== $this and $this->esEquipo($jugador->conseguirUsuario()->getName()) === false) {
                    $cercanos[$jugador->conseguirUsuario()->distance($usuario)] = array($jugador->conseguirNick(), $jugador->conseguirUsuario()->asVector3());
                }
            }

            if (!empty($cercanos)) {

                $minimo = min(array_keys($cercanos));
                list($nombre, $vector) = $cercanos[$minimo];

                $distanciaX = $minimo / 2;
                $distanciaY = $vector->getY() - $usuario->getY();

                $this->conseguirUsuario()->sendMessage("§e" . $nombre . " §7| §f> §a" . $distanciaX . "s §7| §f^ §a" . $distanciaY . "s");

                $paquete = new SetSpawnPositionPacket();
                $paquete->x = (int)$vector->x;
                $paquete->y = (int)$vector->y;
                $paquete->z = (int)$vector->z;
                $paquete->spawnType = SetSpawnPositionPacket::TYPE_WORLD_SPAWN;
                $paquete->spawnForced = true;

                $usuario->dataPacket($paquete);
            }
        }
    }

    /**
     * Aumenta el numero de asesinados a +1.
     * @return void
     */
    public function aumentarAsesinatos(): void
    {
        $this->asesinatos++;
    }

    private function calcularJuego(): string
    {
        $datos = array($this->conseguirAsesinatos());

        return GestorSeccion::conseguirInstancia()->conseguirLenguaje()->translateString("partida.calcular", $datos);
    }

    /**
     * Asigna un nuevo golpeador.
     * @param  string $golpeador
     * @return void
     */
    public function asignarGolpeador(string $golpeador): void
    {
        $this->golpeador = array("nombre" => $golpeador, "tiempo" => microtime(true));
    }

    /**
     * Consigue el nombre del ultimo golpeador con un tiempo menor a los 5 segundos.
     * @return null | string
     */
    public function conseguirGolpeador(): ?string
    {
        if ($this->golpeador["nombre"] !== "") {
            if (round(microtime(true) - $this->golpeador["tiempo"], 3) <= 5) {
                return $this->golpeador["nombre"];
            }
        }

        return null;
    }

    /**
     * Aumentar las sospechas
     * @return void
     */
    public function aumentarSospechas(): void
    {
        if ($this->sospechas++ === 2) {
            $this->terminar();
        } else {
            $this->conseguirUsuario()->sendMessage("§c[!] §e(" . $this->sospechas . "/2)");
        }
    }

    /**
     * Consigue la cantidad de asesinatos cometidos por este jugador.
     * @return integer
     */
    public function conseguirAsesinatos(): int
    {
        return $this->asesinatos;
    }

    /**
     * Verifica si el jugador ya ha votado.
     * @return boolean
     */
    public function haVotado(): bool
    {
        return $this->voto !== false;
    }

    /**
     * Consigue el nombre del mapa por el cual este jugador ha votado (si lo ha hecho).
     * @return boolean | string
     */
    public function conseguirVoto()
    {
        return $this->voto;
    }

    public function conseguirPosicion(): Position
    {
        return $this->posicion;
    }

    /**
     * Asigna el nombre del mapa por el cual este jugador votó.
     * @param  string $voto
     * @return void
     */
    public function asignarVoto(string $voto): void
    {
        $this->voto = $voto;
    }

    /**
     * Consigue el jugador.
     * @return Player
     */
    public function conseguirUsuario(): Player
    {
        return $this->usuario;
    }

    /**
     * Consigue la partida donde este jugador está.
     * @return SkyWars
     */
    public function conseguirPartida(): SkyWars
    {
        return $this->partida;
    }

    /**
     * Consigue el nombre del kit que ha seleccionado el jugador (si lo ha hecho).
     * @return string
     */
    public function conseguirKit(): string
    {
        return $this->kit;
    }

    /**
     * Verífica si el jugador esta en modo espectador.
     * @return boolean
     */
    public function esEspectador(): bool
    {
        return $this->espectador;
    }

    /**
     * Asigna un nuevo kit para esta sección.
     * @param  string $nuevoKit
     * @return void
     */
    public function asignarKit(string $nuevoKit = ""): void
    {
        $this->kit = $nuevoKit;
    }

    /**
     * Asigna el equipo para este sección.
     * @param  string $equipo
     * @return void
     */
    public function asignarEquipo(string $equipo): void
    {
        $this->equipo = $equipo;
    }

    /**
     * Verifica si algún jugador es equipo
     * @param  string $golpeador
     * @return boolean
     */
    public function esEquipo(string $golpeador): bool
    {
        return $this->equipo === $golpeador;
    }

    /**
     * Conseguir el nombre del companero de equipo.
     * @return string
     */
    public function conseguirEquipo(): string
    {
        return $this->equipo;
    }

    /**
     * Consigue el nick del jugador (si no tiene devolverá el nombre original de este jugador).
     * @return string
     */
    public function conseguirNick(): string
    {
        return $this->nick !== "" ? $this->nick : $this->conseguirUsuario()->getName();
    }


}