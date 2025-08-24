<?php

declare(strict_types=1);

namespace madversal\simplereport\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use madversal\simplereport\Main;

final class ReportCommand extends Command implements PluginOwned {
    use PluginOwnedTrait;

    public function __construct(Main $plugin) {
        parent::__construct("report", "Report a player", "/report <player> <reason>");
        $this->owningPlugin(); = $plugin;
        $this->setPermission("simplereport.use");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        $plugin = $this->getOwningPlugin();

        if (!$sender instanceof Player) {
            $sender->sendMessage("§cThis command can only be used by players!");
            return false;
        }

        if (!$this->testPermission($sender)) return false;

        if (count($args) < 2) {
            $sender->sendMessage("§cUsage: /report <player> <reason>");
            return false;
        }

        $targetName = array_shift($args);
        $reason = implode(" ", $args);

        $targetPlayer = $plugin->getServer()->getPlayerExact($targetName);
        if ($targetPlayer === null) {
            $playerDataPath = $plugin->getServer()->getDataPath() . "players/" . strtolower($targetName) . ".dat";
            if (!file_exists($playerDataPath)) {
                $sender->sendMessage("§cPlayer '$targetName' not found or has never joined the server!");
                return false;
            }
        } else {
            $targetName = $targetPlayer->getName();
        }

        if (strtolower($sender->getName()) === strtolower($targetName)) {
            $sender->sendMessage("§cYou cannot report yourself!");
            return false;
        }

        if ($plugin->getReportManager()->isSpamming($sender->getName())) {
            $cooldown = $plugin->getConfig()->get("report-cooldown");
            $sender->sendMessage("§cYou must wait $cooldown seconds between reports!");
            return false;
        }

        $minLength = $plugin->getConfig()->get("min-reason-length");
        $maxLength = $plugin->getConfig()->get("max-reason-length");

        if (strlen($reason) < $minLength) {
            $sender->sendMessage("§cReason must be at least $minLength characters long!");
            return false;
        }

        if (strlen($reason) > $maxLength) {
            $sender->sendMessage("§cReason cannot be longer than $maxLength characters!");
            return false;
        }

        $reportId = $plugin->getReportManager()->createReport(
            $sender->getName(),
            $targetName,
            $reason,
            $sender->getPosition()
        );

        $sender->sendMessage("§aReport submitted successfully! Report ID: #$reportId");

        $plugin->notifyAdmins($sender->getName(), $targetName, $reason);

        return true;
    }
}
