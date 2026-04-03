<?php
// includes/coupon.php - Coupon Validation & Application

class CouponManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function validate($code, $subtotal): array {
        $code = strtoupper(trim($code));
        
        if (empty($code)) {
            return ['valid' => false, 'message' => 'Please enter a coupon code'];
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM coupons 
                WHERE code = ? 
                AND status = 'active' 
                AND (expires_at IS NULL OR expires_at >= NOW())
                AND (max_uses IS NULL OR max_uses > used_count)
            ");
            $stmt->execute([$code]);
            $coupon = $stmt->fetch();
            
            if (!$coupon) {
                return ['valid' => false, 'message' => 'Invalid or expired coupon code'];
            }
            
            if ($coupon['min_cart_amount'] && $subtotal < $coupon['min_cart_amount']) {
                return [
                    'valid' => false, 
                    'message' => "Minimum order of " . formatPrice($coupon['min_cart_amount']) . " required for this coupon"
                ];
            }
            
            return [
                'valid' => true,
                'coupon' => $coupon,
                'discount' => $this->calculateDiscount($coupon, $subtotal)
            ];
            
        } catch (PDOException $e) {
            error_log("Coupon validation error: " . $e->getMessage());
            return ['valid' => false, 'message' => 'Error validating coupon'];
        }
    }
    
    public function calculateDiscount($coupon, $subtotal): float {
        if ($coupon['type'] === 'percentage') {
            $discount = $subtotal * ($coupon['value'] / 100);
            if ($coupon['max_discount']) {
                $discount = min($discount, $coupon['max_discount']);
            }
        } else {
            $discount = $coupon['value'];
        }
        return round($discount, 2);
    }
    
    public function incrementUsage($coupon_id): void {
        $stmt = $this->pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?");
        $stmt->execute([$coupon_id]);
    }
    
    public function createTable(): void {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS coupons (
                id INT AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(50) UNIQUE NOT NULL,
                type ENUM('fixed', 'percentage') DEFAULT 'fixed',
                value DECIMAL(10,2) NOT NULL,
                min_cart_amount DECIMAL(10,2) DEFAULT 0,
                max_discount DECIMAL(10,2) DEFAULT NULL,
                max_uses INT DEFAULT NULL,
                used_count INT DEFAULT 0,
                expires_at DATETIME DEFAULT NULL,
                status ENUM('active', 'inactive') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }
}

function apply_coupon($code, $subtotal): array {
    global $pdo;
    $manager = new CouponManager($pdo);
    return $manager->validate($code, $subtotal);
}
