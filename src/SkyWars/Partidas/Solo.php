<?php

declare(strict_types=1);

namespace SkyWars\Partidas;

/** PocketMine */
use pocketmine\level\Position;

class Solo extends SkyWars
{

    public function __construct(int $minimo, int $maximo)
    {
        $this->minimoDeJugadores = $minimo;
        $this->maximoDeJugadores = $maximo;

        if ($this->partidaID === "") {
            $this->asignarID();
        }
    }

    public function conseguirGanador(): ?array
    {
        $usuario = $this->conseguirJugadores();
        if ($usuario != null) {
            return array($usuario->current());
        }

        return null;
    }

    public function actualizarPartida(): bool
    {
        $actualizar = parent::actualizarPartida();

        if ($this->conseguirEstado() === self::CORRIENDO) {
            if ($this->conseguirCantidad() === 1) {
                $ganador = $this->conseguirGanador();
                if ($ganador !== null) {
                    $this->agregarCelebracion($ganador);
                }
                $this->estadoDePartida = self::ACABANDO;
            }
        }

        return $actualizar;
    }

    public function transportarJugadores()
    {
        $mapa = $this->conseguirInstancia()->conseguirGestorDeMapas()->conseguirMapa($this->mapaGanador);

        if ($mapa != null) {

            $posicion = 0;
            $posiciones = $mapa->conseguirPosiciones();

            foreach ($this->conseguirJugadores() as $usuario) {
                $vector = $posiciones[$posicion++];
                $usuario->conseguirUsuario()->teleport(new Position($vector->x, $vector->y, $vector->z, $this->conseguirMapa()));
                $usuario->conseguirUsuario()->setSpawn($vector);
                $usuario->iniciar();
            }

        } else {
            $this->estadoDePartida = self::ACABANDO;
        }
    }

}