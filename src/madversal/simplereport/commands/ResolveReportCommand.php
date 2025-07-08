<?php

declare(strict_types=1);

namespace madversal\simplereport\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use madversal\simplereport\Main;

class ResolveReportCommand extends Command {
    
    private Main $plugin;
    
    public function __construct(Main $plugin) {
        parent::__construct("resolvereport", "Mark a report as resolved", "/resolvereport <id>");
        $this->plugin = $plugin;
        $this->setPermission("simplereport.admin");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$this->testPermission($sender)) {
            return false;
        }
        
        if (count($args) < 1) {
            $sender->sendMessage("§cUsage: /resolvereport <id>");
            return false;
        }
        
        if (!is_numeric($args[0])) {
            $sender->sendMessage("§cReport ID must be a number!");
            return false;
        }
        
        $reportId = (int)$args[0];
        $report = $this->plugin->getReportManager()->getReport($reportId);
        
        if ($report === null) {
            $sender->sendMessage("§cReport with ID #$reportId not found!");
            return false;
        }
        
        if ($report['resolved']) {
            $sender->sendMessage("§cReport #$reportId is already resolved by " . $report['resolver'] . "!");
            return false;
        }
        
        $success = $this->plugin->getReportManager()->resolveReport($reportId, $sender->getName());
        
        if ($success) {
            $sender->sendMessage("§aReport #$reportId has been marked as resolved!");
            
            // Notify other admins
            $message = "§a[REPORT RESOLVED] §f" . $sender->getName() . " resolved report #$reportId (" . $report['reporter'] . " -> " . $report['reported'] . ")";
            foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
                if ($player->hasPermission("simplereport.admin") && $player !== $sender) {
                    $player->sendMessage($message);
                }
            }
            
            $this->plugin->getLogger()->info($sender->getName() . " resolved report #$reportId");
        } else {
            $sender->sendMessage("§cFailed to resolve report #$reportId!");
        }
        
        return true;
    }
}
