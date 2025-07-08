<?php

declare(strict_types=1);

namespace madversal\simplereport\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use madversal\simplereport\Main;

class ReportCommand extends Command {
    
    private Main $plugin;
    
    public function __construct(Main $plugin) {
        parent::__construct("report", "Report a player", "/report <player> <reason>");
        $this->plugin = $plugin;
        $this->setPermission("simplereport.use");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§cThis command can only be used by players!");
            return false;
        }
        
        if (!$this->testPermission($sender)) {
            return false;
        }
        
        if (count($args) < 2) {
            $sender->sendMessage("§cUsage: /report <player> <reason>");
            return false;
        }
        
        $targetName = array_shift($args);
        $reason = implode(" ", $args);
        
        // Check if target player exists or has played before
        $targetPlayer = $this->plugin->getServer()->getPlayerExact($targetName);
        if ($targetPlayer === null) {
            // Check if player has played before by looking for player data
            $playerDataPath = $this->plugin->getServer()->getDataPath() . "players/" . strtolower($targetName) . ".dat";
            if (!file_exists($playerDataPath)) {
                $sender->sendMessage("§cPlayer '$targetName' not found or has never joined the server!");
                return false;
            }
        } else {
            $targetName = $targetPlayer->getName(); // Get exact case
        }
        
        // Prevent self-reporting
        if (strtolower($sender->getName()) === strtolower($targetName)) {
            $sender->sendMessage("§cYou cannot report yourself!");
            return false;
        }
        
        // Check for spam protection
        if ($this->plugin->getReportManager()->isSpamming($sender->getName())) {
            $cooldown = $this->plugin->getConfig()->get("report-cooldown");
            $sender->sendMessage("§cYou must wait $cooldown seconds between reports!");
            return false;
        }
        
        // Validate reason length
        $minLength = $this->plugin->getConfig()->get("min-reason-length");
        $maxLength = $this->plugin->getConfig()->get("max-reason-length");
        
        if (strlen($reason) < $minLength) {
            $sender->sendMessage("§cReason must be at least $minLength characters long!");
            return false;
        }
        
        if (strlen($reason) > $maxLength) {
            $sender->sendMessage("§cReason cannot be longer than $maxLength characters!");
            return false;
        }
        
        // Create the report
        $reportId = $this->plugin->getReportManager()->createReport(
            $sender->getName(),
            $targetName,
            $reason,
            $sender->getPosition()
        );
        
        $sender->sendMessage("§aReport submitted successfully! Report ID: #$reportId");
        
        // Notify
        $this->plugin->notifyAdmins($sender->getName(), $targetName, $reason);
        
        return true;
    }
}
