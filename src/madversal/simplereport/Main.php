<?php

declare(strict_types=1);

namespace madversal\simplereport;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\player\Player;
use pocketmine\Server;
use libs\CortexPE\DiscordWebhookAPI\Embed;
use libs\CortexPE\DiscordWebhookAPI\Message;
use libs\CortexPE\DiscordWebhookAPI\Webhook;
use madversal\simplereport\commands\ReportCommand;
use madversal\simplereport\commands\ViewReportsCommand;
use madversal\simplereport\commands\ResolveReportCommand;
use madversal\simplereport\managers\ReportManager;

class Main extends PluginBase {
    
    private ReportManager $reportManager;
    private Config $config;
    
    public function onEnable(): void {
        // Create data folder if it doesn't exist
        $this->saveDefaultConfig();
        
        // Initialize managers
        $this->reportManager = new ReportManager($this);
        
        // Register commands
        $this->getServer()->getCommandMap()->register("simplereport", new ReportCommand($this));
        $this->getServer()->getCommandMap()->register("simplereport", new ViewReportsCommand($this));
        $this->getServer()->getCommandMap()->register("simplereport", new ResolveReportCommand($this));
        
        $this->getLogger()->info("SimpleReport has been enabled successfully!");
    }
    
    public function onDisable(): void {
        $this->getLogger()->info("SimpleReport has been disabled!");
    }
    
    public function getReportManager(): ReportManager {
        return $this->reportManager;
    }
    
    /**
     * Notify all online admins about a new report
     */
    public function notifyAdmins(string $reporter, string $reported, string $reason): void {
        $message = $this->getConfig()->get("admin-notification-format", "§c[REPORT] §f{reporter} reported {reported} for: {reason}");
        $message = str_replace(["{reporter}", "{reported}", "{reason}"], [$reporter, $reported, $reason], $message);
        
        foreach ($this->getServer()->getOnlinePlayers() as $player) {
            if ($player->hasPermission("simplereport.admin")) {
                $player->sendMessage($message);

     /**
      * Function webhook submit
      */
                $webHook = new Webhook($this->getConfig()->get('report.webhook'));
                $msg = new Message();
                $embed = new Embed();
                $embed->setTitle("New Report");                    
                $embed->addField("👤 User:", "$reported");
                $embed->addField("📝 Reason:", "$reason");
                $embed->addField("⚠️ Reported By:", "$reporter");
                $embed->setColor(mt_rand(0x000000, 0xFFFFFF));
                $embed->setFooter("test");
                $msg->addEmbed($embed);
                $webHook->send($msg);                    
            }
        }
        
        // Also log to console
        $this->getLogger()->info("Report: $reporter reported $reported for: $reason");
    }
}
