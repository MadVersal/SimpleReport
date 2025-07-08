<?php

declare(strict_types=1);

namespace madversal\simplereport\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use madversal\simplereport\Main;

class ViewReportsCommand extends Command {
    
    private Main $plugin;
    
    public function __construct(Main $plugin) {
        parent::__construct("viewreports", "View all reports", "/viewreports [page]");
        $this->plugin = $plugin;
        $this->setPermission("simplereport.viewreports");
        $this->setAliases(["reports"]);
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$this->testPermission($sender)) {
            return false;
        }
        
        $page = 1;
        if (isset($args[0]) && is_numeric($args[0])) {
            $page = max(1, (int)$args[0]);
        }
        
        $reportsPerPage = $this->plugin->getConfig()->get("reports-per-page", 10);
        $reports = $this->plugin->getReportManager()->getAllReports();
        
        if (empty($reports)) {
            $sender->sendMessage("§eNo reports found!");
            return true;
        }
        
        // Sort reports by date (newest first)
        usort($reports, function($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });
        
        $totalReports = count($reports);
        $totalPages = (int)ceil($totalReports / $reportsPerPage);
        
        if ($page > $totalPages) {
            $sender->sendMessage("§cInvalid page! Maximum page: $totalPages");
            return false;
        }
        
        $startIndex = ($page - 1) * $reportsPerPage;
        $endIndex = min($startIndex + $reportsPerPage, $totalReports);

        $sender->sendMessage("§r§7---------------");
        $sender->sendMessage("§7Reports (Page $page/$totalPages)");
        
        for ($i = $startIndex; $i < $endIndex; $i++) {
            $report = $reports[$i];
            $date = date("Y-m-d H:i:s", $report['timestamp']);
            $status = $report['resolved'] ? "§a[RESOLVED]" : "§c[OPEN]";
            
            $sender->sendMessage("§f#" . $report['id'] . " $status §f" . $report['reporter'] . " -> " . $report['reported']);
            $sender->sendMessage("  §7Reason: " . $report['reason']);
            $sender->sendMessage("  §7Date: $date");
            
            if (!empty($report['resolver']) && $report['resolved']) {
                $sender->sendMessage("  §7Resolved by: " . $report['resolver']);
            }
            
            $sender->sendMessage("§r§7---------------"); // Empty line for readability
        }
        
        if ($totalPages > 1) {
            $sender->sendMessage("§7Use /viewreports <page> to view other pages");
        }
        
        // Show additional commands info
        $sender->sendMessage("§7Tip: Use /resolvereport <id> to mark a report as resolved");
        
        return true;
    }
}
