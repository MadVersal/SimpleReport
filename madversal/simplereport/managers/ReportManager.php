<?php

declare(strict_types=1);

namespace madversal\simplereport\managers;

use pocketmine\world\Position;
use pocketmine\utils\Config;
use madversal\simplereport\Main;

class ReportManager {
    
    private Main $plugin;
    private Config $dataConfig;
    private array $lastReportTime = [];
    
    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        $this->initializeDataFile();
    }
    
    private function initializeDataFile(): void {
        $dataPath = $this->plugin->getDataFolder() . "reports.json";
        
        if (!file_exists($dataPath)) {
            file_put_contents($dataPath, json_encode([], JSON_PRETTY_PRINT));
        }
        
        $this->dataConfig = new Config($dataPath, Config::JSON);
    }
    
    /**
     * Create a new report
     */
    public function createReport(string $reporter, string $reported, string $reason, Position $position): int {
        $reports = $this->dataConfig->getAll();
        
        // Generate unique report ID
        $reportId = $this->generateReportId($reports);
        
        $report = [
            'id' => $reportId,
            'reporter' => $reporter,
            'reported' => $reported,
            'reason' => $reason,
            'timestamp' => time(),
            'world' => $position->getWorld()->getFolderName(),
            'x' => round($position->getX(), 2),
            'y' => round($position->getY(), 2),
            'z' => round($position->getZ(), 2),
            'resolved' => false,
            'resolver' => null,
            'resolve_timestamp' => null
        ];
        
        $reports[$reportId] = $report;
        $this->dataConfig->setAll($reports);
        $this->dataConfig->save();
        
        // Update spam protection
        $this->lastReportTime[$reporter] = time();
        
        return $reportId;
    }
    
    /**
     * Get all reports
     */
    public function getAllReports(): array {
        return array_values($this->dataConfig->getAll());
    }
    
    /**
     * Get a specific report by ID
     */
    public function getReport(int $id): ?array {
        $reports = $this->dataConfig->getAll();
        return $reports[$id] ?? null;
    }
    
    /**
     * Mark a report as resolved
     */
    public function resolveReport(int $id, string $resolver): bool {
        $reports = $this->dataConfig->getAll();
        
        if (!isset($reports[$id])) {
            return false;
        }
        
        $reports[$id]['resolved'] = true;
        $reports[$id]['resolver'] = $resolver;
        $reports[$id]['resolve_timestamp'] = time();
        
        $this->dataConfig->setAll($reports);
        $this->dataConfig->save();
        
        return true;
    }
    
    /**
     * Check if a player is spamming reports
     */
    public function isSpamming(string $playerName): bool {
        if (!isset($this->lastReportTime[$playerName])) {
            return false;
        }
        
        $cooldown = $this->plugin->getConfig()->get("report-cooldown", 60);
        $timeSinceLastReport = time() - $this->lastReportTime[$playerName];
        
        return $timeSinceLastReport < $cooldown;
    }
    
    /**
     * Get reports by a specific player
     */
    public function getReportsByPlayer(string $playerName, bool $asReporter = true): array {
        $reports = $this->getAllReports();
        $field = $asReporter ? 'reporter' : 'reported';
        
        return array_filter($reports, function($report) use ($playerName, $field) {
            return strcasecmp($report[$field], $playerName) === 0;
        });
    }
    
    /**
     * Get recent reports (within specified hours)
     */
    public function getRecentReports(int $hours = 24): array {
        $reports = $this->getAllReports();
        $cutoffTime = time() - ($hours * 3600);
        
        return array_filter($reports, function($report) use ($cutoffTime) {
            return $report['timestamp'] >= $cutoffTime;
        });
    }
    
    /**
     * Generate a unique report ID
     */
    private function generateReportId(array $existingReports): int {
        if (empty($existingReports)) {
            return 1;
        }
        
        $maxId = max(array_keys($existingReports));
        return $maxId + 1;
    }
    
    /**
     * Delete old resolved reports (cleanup)
     */
    public function cleanupOldReports(int $daysOld = 30): int {
        $reports = $this->dataConfig->getAll();
        $cutoffTime = time() - ($daysOld * 24 * 3600);
        $deletedCount = 0;
        
        foreach ($reports as $id => $report) {
            if ($report['resolved'] && $report['resolve_timestamp'] < $cutoffTime) {
                unset($reports[$id]);
                $deletedCount++;
            }
        }
        
        if ($deletedCount > 0) {
            $this->dataConfig->setAll($reports);
            $this->dataConfig->save();
        }
        
        return $deletedCount;
    }
}
