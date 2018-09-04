<?php

declare(strict_types=1);

namespace SkyWars\Partidas;

/** PocketMine */
use pocketmine\level\Position;
use SkyWars\Sesiones\Usuario;

class Duo extends SkyWars
{

    public function __construct(int $minimo, int $maximo)
    {
        $this->minimoDeJugadores = $minimo;
        $this->maximoDeJugadores = $maximo;

        if ($this->partidaID === "") {
            $this->asignarID();
        }
    }

    /**
     * Conseguir el ganador o los ganadores de la partida.
     * @return null | array
     */
    public function conseguirGanador(): ?array
    {
        if ($this->conseguirCantidad() === 1) {
            $ganador = $this->conseguirJugadores();

            if ($ganador !== null) {

                $ganador = $ganador->current();
                $ganador2 = $this->jugadoresTotales[$ganador->conseguirEquipo()] ?? null;

                if ($ganador2 !== null) {
                    return array($ganador, $ganador2);
                }

                return array($ganador);
            }

        } else {
            if ($this->conseguirCantidad() === 2) {
                $ganadores = array();

                foreach ($this->conseguirJugadores() as $ganador) {
                    if ($ganador !== null) {
                        $ganadores[] = $ganador;
                    }
                }

                $usuario1 = $usuario2 = null;

                if (!empty($ganadores)) {

                    if (count($ganadores) === 2) {
                        list($usuario1, $usuario2) = $ganadores;
                    } else {
                        list($usuario1) = $ganadores;
                    }

                    if (isset($usuario2)) {
                        if ($usuario1->esEquipo($usuario2->conseguirUsuario()->getName()) === true) {
                            return $ganadores;
                        }
                    }

                }

            }
        }

        return null;
    }

    public function actualizarPartida(): bool
    {
        $actualizar = parent::actualizarPartida();

        if ($this->conseguirEstado() === self::CORRIENDO) {
            $ganadores = $this->conseguirGanador();
            if ($ganadores !== null) {
                $this->agregarCelebracion($ganadores);
                $this->estadoDePartida = self::ACABANDO;
            }
        }

        return $actualizar;
    }

    public function transportarJugadores(): void
    {
        $mapa = $this->conseguirInstancia()->conseguirGestorDeMapas()->conseguirMapa($this->mapaGanador);

        if ($mapa !== null) {

            $usuarios = $this->conseguirJugadoresTotales();
            $posiciones = $mapa->conseguirPosiciones();
            $posicion = 0;

            for ($i = 0; $i < count($usuarios); $i += 2) {

                $equipos = array_slice($usuarios, $i, $i + 2);
                $usuario1 = null;
                $usuario2 = null;

                if (count($equipos) === 2) {
                    list($usuario1, $usuario2) = $equipos;
                } else {
                    if (count($equipos) === 1) {
                        list($usuario1) = $equipos;
                    }
                }

                $vector = $posiciones[$posicion++];
                $enviar = new Position((float)$vector->x, (float)$vector->y, (float)$vector->z, $this->mapa);

                /**
                 * @var Usuario $usuario1
                 * @var Usuario $usuario2
                 */

                if (isset($usuario1)) {
                    if (isset($usuario2)) {
                        $usuario1->asignarEquipo($usuario2->conseguirUsuario()->getName());
                        $usuario1->conseguirUsuario()->teleport($enviar);
                        $usuario1->conseguirUsuario()->setSpawn($enviar);
                        $usuario1->iniciar();
                        $usuario2->asignarEquipo($usuario1->conseguirUsuario()->getName());
                        $usuario2->conseguirUsuario()->teleport($enviar);
                        $usuario2->conseguirUsuario()->setSpawn($enviar);
                        $usuario2->iniciar();
                    } else {
                        $usuario1->conseguirUsuario()->teleport($enviar);//forever alone? XD
                        $usuario1->conseguirUsuario()->setSpawn($enviar);
                        $usuario1->iniciar();

                        break;
                    }
                }

            }

        } else {
            $this->estadoDePartida = self::ACABANDO;
        }
    }

}