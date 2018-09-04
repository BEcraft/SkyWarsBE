<?php

declare(strict_types=1);

namespace SkyWars\Sesiones;

/** SkyWars */
use SkyWars\Cargador;
use SkyWars\Partidas\SkyWars;

class GestorSeccion
{

    /**
     * Jugadores que están en modo creador.
     * @var array
     */
    public $creandoMapa = array();

    /**
     * Verifica si algún jugador está creando un mapa.
     * @param  string $nombre
     * @return null | Creador
     */
    public function estaCreando(string $nombre): ?Creador
    {
        return $this->creandoMapa[$nombre] ?? null;
    }

    /**
     * Elimina algún jugador que este en el modo creador.
     * @param  string $nombre
     * @return void
     */
    public function eliminarCreador(string $nombre): void
    {
        unset($this->creandoMapa[$nombre]);
    }

    /**
     * Verifica si cierto jugador esta en una partida de SkyWars.
     * @param  string $nombre
     * @return null | Usuario
     */
    public function estaJugando(string $nombre): ?Usuario
    {
        /** @var SkyWars $partida */
        foreach (self::conseguirInstancia()->partidas as $partida) {
            if (array_key_exists($nombre, $partida->conseguirJugadoresTotales(true))) {
                return $partida->conseguirJugadoresTotales(true)[$nombre];
            }
        }

        return null;
    }

    /**
     * Cargador.
     * @return Cargador
     */
    public static function conseguirInstancia(): Cargador
    {
        return Cargador::$instancia;
    }

}