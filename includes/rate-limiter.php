<?php
// includes/rate-limiter.php - Rate Limiting System

require_once __DIR__ . '/../config/env.php';

class RateLimiter {
    private $pdo;
    private $maxAttempts;
    private $lockoutSeconds;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->maxAttempts = (int)env('RATE_LIMIT_ATTEMPTS', 5);
        $this->lockoutSeconds = (int)env('RATE_LIMIT_LOCKOUT', 600);
    }
    
    public function check($identifier, $action = 'login'): bool {
        $this->cleanup($action);
        
        $stmt = $this->pdo->prepare(
            "SELECT attempts, last_attempt FROM rate_limits 
             WHERE identifier = ? AND action = ? AND locked_until > NOW()"
        );
        $stmt->execute([$identifier, $action]);
        $record = $stmt->fetch();
        
        if ($record && $record['attempts'] >= $this->maxAttempts) {
            return false;
        }
        
        return true;
    }
    
    public function record($identifier, $action = 'login'): void {
        $stmt = $this->pdo->prepare(
            "SELECT id, attempts FROM rate_limits WHERE identifier = ? AND action = ?"
        );
        $stmt->execute([$identifier, $action]);
        $record = $stmt->fetch();
        
        if ($record) {
            $stmt = $this->pdo->prepare(
                "UPDATE rate_limits SET attempts = attempts + 1, last_attempt = NOW(), 
                 locked_until = DATE_ADD(NOW(), INTERVAL ? SECOND)
                 WHERE id = ? AND attempts + 1 >= ?"
            );
            $stmt->execute([$this->lockoutSeconds, $record['id'], $this->maxAttempts]);
            
            if ($stmt->rowCount() === 0) {
                $stmt = $this->pdo->prepare(
                    "UPDATE rate_limits SET attempts = attempts + 1, last_attempt = NOW() WHERE id = ?"
                );
                $stmt->execute([$record['id']]);
            }
        } else {
            $stmt = $this->pdo->prepare(
                "INSERT INTO rate_limits (identifier, action, attempts, last_attempt, locked_until) 
                 VALUES (?, ?, 1, NOW(), DATE_ADD(NOW(), INTERVAL ? SECOND))"
            );
            $stmt->execute([$identifier, $action, $this->lockoutSeconds]);
        }
    }
    
    public function reset($identifier, $action = 'login'): void {
        $stmt = $this->pdo->prepare("DELETE FROM rate_limits WHERE identifier = ? AND action = ?");
        $stmt->execute([$identifier, $action]);
    }
    
    public function getRemainingAttempts($identifier, $action = 'login'): int {
        $stmt = $this->pdo->prepare(
            "SELECT attempts FROM rate_limits WHERE identifier = ? AND action = ?"
        );
        $stmt->execute([$identifier, $action]);
        $record = $stmt->fetch();
        
        if (!$record) {
            return $this->maxAttempts;
        }
        
        return max(0, $this->maxAttempts - $record['attempts']);
    }
    
    public function getLockoutRemaining($identifier, $action = 'login'): int {
        $stmt = $this->pdo->prepare(
            "SELECT locked_until FROM rate_limits 
             WHERE identifier = ? AND action = ? AND locked_until > NOW()"
        );
        $stmt->execute([$identifier, $action]);
        $record = $stmt->fetch();
        
        if (!$record) {
            return 0;
        }
        
        return max(0, strtotime($record['locked_until']) - time());
    }
    
    private function cleanup($action): void {
        $this->pdo->prepare(
            "DELETE FROM rate_limits WHERE action = ? AND locked_until < DATE_SUB(NOW(), INTERVAL 1 HOUR)"
        )->execute([$action]);
    }
    
    public function createTable(): void {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS rate_limits (
                id INT AUTO_INCREMENT PRIMARY KEY,
                identifier VARCHAR(255) NOT NULL,
                action VARCHAR(50) NOT NULL,
                attempts INT DEFAULT 0,
                last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                locked_until TIMESTAMP NULL,
                INDEX (identifier, action),
                INDEX (locked_until)
            )
        ");
    }
}
