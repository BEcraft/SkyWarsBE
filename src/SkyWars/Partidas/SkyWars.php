<?php

declare(strict_types=1);

namespace SkyWars\Partidas;

/** SkyWars */
use SkyWars\Cargador;
use SkyWars\Secciones\Usuario;
use SkyWars\Eventos\{
    SalirEvento, UnirseEvento, GanarEvento
};

/** PocketMine */
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\tile\Sign;
use pocketmine\level\Level;

abstract class SkyWars
{

    /**
     * Todos los jugadores de la partida.
     * @var array
     */
    protected $jugadoresTotales = array();

    /**
     * Numero máximo de jugadores.
     * @var integer
     */
    protected $maximoDeJugadores = 0;

    /**
     * Numero minimo de jugadores.
     * @var integer
     */
    protected $minimoDeJugadores = 0;

    /**
     * Identificador de la partida.
     * @var string
     */
    protected $partidaID = "";

    /**
     * Estado de la partida.
     * @var integer
     */
    protected $estadoDePartida = self::ESPERANDO;

    /**
     * Cartel de la partida (si tiene).
     * @var null | Sign
     */
    protected $cartelDePartida = null;

    /**
     * Mapas disponibles para votación.
     * @var array
     */
    protected $mapasDisponibles = array();

    /**
     * Nombre del mapa que ha ganado la votación.
     * @var string
     */
    protected $mapaGanador = "";

    /**
     * Mapa en el cual se efectuará la partida.
     * @var null | Level
     */
    protected $mapa = null;

    /**
     * Tiempo de espera para empezar (total: 60 segundos)
     * @var integer
     */
    protected $tiempoDeEspera = self::ESPERA;

    /**
     * Tiempo para terminar la partida (total: 10 segundos).
     * @var integer
     */
    protected $tiempoFinal = self::ACABAR;

    /**
     * Tiempo total que durará la partida (total: 20 minutos).
     * @var integer
     */
    protected $tiempoTotal = self::TIEMPO;

    /** Estados de la partida */
    public const ESPERANDO = 0;
    public const INICIANDO = 1;
    public const CORRIENDO = 2;
    public const ACABANDO = 3;

    /** Tiempos de la partida */
    public const ESPERA = 60;
    public const ACABAR = 10;
    public const TIEMPO = 20 * 60;

    /**
     * Conseguir los usuarios que están vivos o en modo espectador
     * @param boolean $espectador
     * @return null | \Generator
     */
    public function conseguirJugadores(bool $espectador = false): \Generator
    {
        foreach ($this->jugadoresTotales as $nombre => $usuario) {
            if ($usuario->esEspectador() === $espectador) {
                yield $usuario;
            }
        }
    }

    /**
     * Consigue todos los usuarios de la partida (sin excepción).
     * @param bool $llaves
     * @return array
     */
    public function conseguirJugadoresTotales(bool $llaves = false): array
    {
        if ($llaves === false) {
            return array_values($this->jugadoresTotales);
        } else {
            return $this->jugadoresTotales;
        }
    }

    /**
     * Consigue la cantidad de jugadores vivos.
     * @return integer
     */
    public function conseguirCantidad(): int
    {
        $cantidad = 0;

        foreach ($this->conseguirJugadores() as $jugador) {
            $cantidad++;
        }

        return $cantidad;
    }

    /**
     * Asignar un nuevo identificador a la partida.
     * @return void
     */
    public function asignarID(): void
    {
        $this->partidaID = "SkyWars=" . substr(str_shuffle("ABCDE") . "-" . mt_rand(), 0, 10);
    }

    /**
     * Conseguir el indentificador de la partida.
     * @return string
     */
    public function conseguirID(): string
    {
        return $this->partidaID;
    }

    /**
     * Conseguir el máximo de jugadores para esta partida.
     * @return integer
     */
    public function conseguirMaximo(): int
    {
        return $this->maximoDeJugadores;
    }

    /**
     * Conseguir el mínimo de jugadores para esta partida.
     * @return integer
     */
    public function conseguirMinimo(): int
    {
        return $this->minimoDeJugadores;
    }

    /**
     * Consigue la lista de mapas para votación.
     * @return array
     */
    public function enviarDatos(): array
    {
        $datos = array(
            "type"    => "form",
            "title"   => "Voting",
            "buttons" => array(),
            "content" => ""
        );

        if (empty($this->mapasDisponibles) === false) {
            foreach ($this->mapasDisponibles as $mapa => $votos) {
                $datos["buttons"][] = array("text" => $mapa . " > " . $votos);
            }
        } else {
            $datos["content"] = "----------------"; //esperando XD
        }

        return $datos;
    }

    /**
     * Asignar el nombre del mapa que ha ganado la votación.
     * @return void
     */
    public function asignarMapaGanador(): void
    {
        $this->mapaGanador = array_search(max($this->mapasDisponibles), $this->mapasDisponibles);
    }

    /**
     * Asignar el mapa que ha ganado la votación.
     * @return void
     */
    public function asignarMapa(): void
    {
        $this->mapa = $this->conseguirInstancia()->conseguirGestorDeMapas()->extraerMapa((string)$this->partidaID, $this->mapaGanador);
    }

    /**
     * Consigue el mapa de la partida.
     * @return null | Level
     */
    public function conseguirMapa(): ?Level
    {
        return $this->mapa;
    }

    /**
     * Consigue el estado de la partida.
     * @return integer
     */
    public function conseguirEstado(): int
    {
        return $this->estadoDePartida;
    }

    /**
     * Verifica si la partida es modo Solo o Dúo.
     * @return boolean
     */
    public function esSolo(): bool
    {
        return $this instanceof Solo;
    }

    /**
     * Agrega un nuevo jugador a la partida.
     * @param  Player $jugador
     * @return void
     */
    public function agregarJugador(Player $jugador): void
    {
        $this->conseguirServidor()->getPluginManager()->callEvent($evento = new UnirseEvento($jugador, $this->partidaID));

        if (!$evento->isCancelled()) {
            $usuario = new Usuario($jugador, $this);
            $this->jugadoresTotales[$jugador->getName()] = $usuario;
            $this->enviarMensajes("§eSkyWars §7> §a" . $usuario->conseguirNick() . " §7(" . $this->conseguirCantidad() . "/" . $this->conseguirMaximo() . ")");
        }

    }

    /**
     * Elimina cierto jugador de la partida.
     * @param  Player      $jugador
     * @param  int|integer $asesinatos
     * @return void
     */
    public function removerJugador(Player $jugador, int $asesinatos = 0): void
    {
        $this->conseguirServidor()->getPluginManager()->callEvent($evento = new SalirEvento($jugador, $asesinatos));

        if ($this->conseguirEstado() <= self::INICIANDO) {
            $usuario = $this->jugadoresTotales[$jugador->getName()];
            $this->enviarMensajes("§eSkyWars §7> §c" . $usuario->conseguirNick() . " §7(" . ($this->conseguirCantidad() - 1) . "/" . $this->conseguirMaximo() . ")");
        }

        unset($this->jugadoresTotales[$jugador->getName()]);
    }

    /**
     * Verifica si la partida tiene el mapa ganador.
     * @return boolean
     */
    public function tieneMapa(): bool
    {
        return $this->mapaGanador !== "";
    }

    /**
     * Asigna un nuevo cartel para esta partida.
     * @param  null | Sign $cartel
     * @return void
     */
    public function asignarCartel(?Sign $cartel): void
    {
        $this->cartelDePartida = $cartel;
    }

    /**
     * Conseguir el cartel que esta partida esta usando.
     * @return null | Sign
     */
    public function conseguirCartel(): ?Sign
    {
        return $this->cartelDePartida;
    }

    /**
     * Verifica si esta partida esta disponible.
     * @return boolean
     */
    public function estaDisponible(): bool
    {
        return $this->estadoDePartida <= self::INICIANDO and $this->conseguirCantidad() < $this->maximoDeJugadores;
    }

    /**
     * Envia cierto mensaje a los jugadores.
     * @param  [type] $mensaje
     * @param  array $datos
     * @return void
     */
    public function enviarMensajes($mensaje, $datos = array()): void
    {
        foreach ($this->conseguirJugadoresTotales() as $usuario) {
            $mensaje = $this->conseguirInstancia()->conseguirLenguaje()::traducir($usuario->conseguirUsuario()->getLocale(), $mensaje, $datos);
            $usuario->conseguirUsuario()->sendMessage($mensaje);
            $usuario->conseguirUsuario()->getLevel()->broadcastLevelEvent($usuario->conseguirUsuario(), 1030);
        }
    }

    /**
     * Envia cierto titulo a los jugadores.
     * @param  [type] $mensaje
     * @param  array $datos
     * @return void
     */
    public function enviarTitulos($mensaje, $datos = array()): void
    {
        foreach ($this->conseguirJugadoresTotales() as $usuario) {
            $mensaje = $this->conseguirInstancia()->conseguirLenguaje()::traducir($usuario->conseguirUsuario()->getLocale(), $mensaje, $datos);
            $usuario->conseguirUsuario()->addTitle("§eSkyWars", $mensaje, 10, 10, 10);
            $usuario->conseguirUsuario()->getLevel()->broadcastLevelEvent($usuario->conseguirUsuario(), 1009);
        }
    }

    /**
     * Reinicia la partida una vez terminada.
     * @return void
     */
    public function reiniciarPartida(): void
    {
        $this->jugadoresTotales = array();
        $this->mapaGanador = "";
        $this->mapasDisponibles = array();
        $this->mapa = null;
        $this->tiempoTotal = self::TIEMPO;
        $this->tiempoDeEspera = self::ESPERA;
        $this->tiempoFinal = self::ACABAR;
        $this->conseguirInstancia()->conseguirGestorDeMapas()->eliminarMundo($this->partidaID);
        $this->asignarID();
        $this->estadoDePartida = self::ESPERANDO;
    }

    /**
     * Consigue el nombre de un mapa.
     * @param  int $boton
     * @return string
     */
    public function conseguirNombre(int $boton): string
    {
        return array_keys($this->mapasDisponibles)[$boton] ?? "";
    }

    /**
     * Aumenta la cantidad votos para cierto mapa.
     * @param  string $mapa
     * @return void
     */
    public function aumentarVotos(string $mapa): void
    {
        if (array_key_exists($mapa, $this->mapasDisponibles)) {
            $this->mapasDisponibles[$mapa]++;
        }
    }

    /**
     * Disminuye la cantidad de votos de cierto mapa.
     * @param  string $mapa
     * @return void
     */
    public function disminuirVotos(string $mapa): void
    {
        if (array_key_exists($mapa, $this->mapasDisponibles)) {
            $this->mapasDisponibles[$mapa]--;
        }
    }

    /**
     * Asigna una lista de mapas disponibles para votar.
     * @return void
     */
    public function asignarMapas(): void
    {
        $maximo = $this->esSolo() ? $this->conseguirMaximo() : round($this->conseguirMaximo() / 2);
        $mapas = $this->conseguirInstancia()->conseguirGestorDeMapas()->conseguirListaDeMapas((int)$maximo);
        if (!empty($mapas)) {
            $this->mapasDisponibles = $mapas;
        }
    }

    /**
     * Consigue el Cargador.
     * @return Cargador
     */
    public function conseguirInstancia(): Cargador
    {
        return Cargador::$instancia;
    }

    /**
     * Consigue el servidor.
     * @return Server
     */
    public function conseguirServidor(): Server
    {
        return $this->conseguirInstancia()->getServer();
    }

    /**
     * Celebración al ganar una partida.
     * @param  array $usuarios
     * @return void
     */
    public function agregarCelebracion(array $usuarios): void
    {
        foreach ($usuarios as $ganador) {

            $ganador->conseguirUsuario()->sendMessage($this->conseguirInstancia()->conseguirLenguaje()::traducir($ganador->conseguirUsuario()->getLocale(), "partida.ganar"));
            $ganador->conseguirUsuario()->addTitle($this->conseguirInstancia()->conseguirLenguaje()::traducir($ganador->conseguirUsuario()->getLocale(), "partida.ganar.titulo"), "§7§l#1", 15, 20 * 5, 15);
            $ganador->agregarEfecto();

            for ($i = 0; $i < 5; ++$i) {
                $ganador->conseguirUsuario()->broadcastEntityEvent(65);
            }

            $this->conseguirInstancia()->getServer()->getPluginManager()->callEvent(new GanarEvento($ganador->conseguirUsuario(), $ganador->conseguirAsesinatos()));
        }
    }

    /**
     * Actualiza la partida.
     * @return boolean
     */
    protected function actualizarPartida(): bool
    {
        if ($this->conseguirEstado() === self::ESPERANDO) {

            if (($this->tiempoDeEspera++ % 30) === 0) {
                $this->enviarMensajes("partida.espera");
            }

            if ($this->conseguirCantidad() === $this->conseguirMinimo()) {
                $this->enviarMensajes("partida.iniciando");

                if ($this->tiempoDeEspera > self::ESPERA) {
                    $this->tiempoDeEspera = self::ESPERA;
                }

                $this->estadoDePartida = self::INICIANDO;

                if (empty($this->mapasDisponibles)) {
                    $this->asignarMapas();
                }

            }

            return true;
        }
        if ($this->conseguirEstado() === self::INICIANDO) {

            foreach ($this->conseguirJugadoresTotales() as $usuario) {

                $experiencia = $usuario->conseguirUsuario()->getXpProgress();

                if ($experiencia > 0.0333) {
                    $experiencia = ($this->tiempoDeEspera * 1.65) / (5 * 20);
                    $usuario->conseguirUsuario()->setXpProgress($experiencia);
                } else {
                    $usuario->conseguirUsuario()->setXpProgress(0);
                }
            }

            if (($this->tiempoDeEspera-- % 10) === 0 or $this->tiempoDeEspera <= 5) {
                $this->enviarTitulos("partida.iniciando.tiempo", array($this->tiempoDeEspera));
            }

            if ($this->conseguirCantidad() < $this->conseguirMinimo()) {

                if ($this->tiempoDeEspera < self::ESPERA) {
                    $this->tiempoDeEspera = self::ESPERA;
                }

                foreach ($this->conseguirJugadoresTotales() as $usuario) {
                    $usuario->conseguirUsuario()->setXpProgress($usuario::EXPERIENCIA);
                }

                $this->estadoDePartida = self::ESPERANDO;

                return false;
            }

            if ($this->tiempoDeEspera === 10) {
                if ($this->mapa === null) {
                    $this->asignarMapaGanador();
                    $this->enviarMensajes("partida.mapa.ganador", array($this->mapaGanador));
                    $this->asignarMapa();
                }

                if ($this->mapa === null) {
                    $this->estadoDePartida = self::ACABANDO;
                }
            }

            if ($this->tiempoDeEspera === 0) {
                $this->transportarJugadores();
                $this->conseguirMapa()->setTime(7000);
                $this->conseguirMapa()->stopTime();

                $this->estadoDePartida = self::CORRIENDO;
            }

            return true;
        }
        if ($this->conseguirEstado() === self::CORRIENDO) {
            $this->tiempoTotal--;

            if (($mediante = self::TIEMPO - 5) < $this->tiempoTotal) {
                $this->enviarTitulos("partida.iniciando.tiempo", array($this->tiempoTotal - $mediante));
            }

            if ($mediante === $this->tiempoTotal) {

                foreach ($this->conseguirJugadores() as $usuario) {
                    $usuario->conseguirUsuario()->setImmobile(false);
                    $usuario->agregarKit();
                }

                $this->enviarMensajes("partida.iniciada", array($this->mapaGanador, $this->conseguirCantidad(), gmdate("i:s", self::TIEMPO), ($usuario->conseguirKit() !== "" ? $usuario->conseguirKit() : "~")));
                $this->conseguirInstancia()->conseguirGestorDeCofres()->cargarCofres($this->mapa, $this->mapaGanador);
            }

            if (($this->tiempoTotal % 60) === 0) {
                $this->enviarMensajes("partida.informacion", array($this->conseguirCantidad(), gmdate("i:s", $this->tiempoTotal)));
            }

            if ($this->conseguirCantidad() === 0 or $this->tiempoTotal === 0) {
                $this->estadoDePartida = self::ACABANDO;
            }

            return true;
        }
        if ($this->estadoDePartida === self::ACABANDO) {
            $this->tiempoFinal--;

            if ($this->tiempoFinal === 5) {
                foreach ($this->conseguirJugadoresTotales() as $usuario) {
                    $usuario->terminar();
                }
            }

            if ($this->tiempoFinal === 0) {
                $this->reiniciarPartida();
            }

            return true;
        }

        return true;
    }

    /**
     * Conseguir el ganador o los ganadores de la partida.
     * @return array
     */
    abstract public function conseguirGanador();

    /**
     * Envia a los jugadores a sus islas.
     * @return void
     */
    abstract public function transportarJugadores();

}