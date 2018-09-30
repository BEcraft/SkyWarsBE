<?php

namespace NoSign;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\{CommandSender, Command};

class Main extends PluginBase {

    public function onEnable(): void {
        $this->getLogger()->notice("Cargado");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $params): bool {
        if ($command->getName() === "skywars") {

            if (!($sender instanceof Player)) {
                $sender->sendMessage("[Error] Solo lo puedes usar en juego.");
                return false;
            }

            $skywars = $this->getServer()->getPluginManager()->getPlugin("SkyWarsBE");

            if ($skywars === null) {
                $sender->sendMessage("[Error] SkyWars no existe.");
                return false;
            }

            if ($skywars->conseguirGestorDeSecciones()->estaJugando($sender->getName()) === true) {
                $sender->sendMessage("[Error] Ya estas en una partida.");
                return false;
            }

            $soloMode = true;

            if (isset($params[0])) {
                if ($params[0] === "duos") {
                    $soloMode = false;
                }
            }

            $sender->sendMessage("Buscando una partida en modo: " . ($soloMode === true ? "Solo" : "Duos"));

            $toSend = null;

            foreach ($skywars->partidas as $game) {
                if ($game->esSolo() === $soloMode) {
                    if ($game->estaDisponible() === true) {
                        $toSend = $game;
                        break;
                    }
                }
            }

            if ($toSend === null) {
                $sender->sendMessage("[Error] No hay partidas disponibles.");
            } else {
                $sender->sendMessage("[Información] Se ha encontrado una partida, enviándote hacia: " . $toSend->conseguirID());
                if ($toSend->estaDisponible() === true) {
                    $toSend->agregarJugador($sender);
                }
            }

            return true;
        }
    }
}
