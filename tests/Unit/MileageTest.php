<?php

declare(strict_types=1);

namespace CarMoneyLab\Tests\Unit;

use CarMoneyLab\Domain\{ApplicationValidator, AssessmentService, DecisionEngine, LtvCalculator, VehicleAge, VinValidator, ValidationException};
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MileageTest extends TestCase
{
    private function assess(mixed $mileage, int $amount = 450000, bool $omit = false): array
    {
        $rules = require __DIR__ . '/../../backend/config/rules.php';
        $age = new VehicleAge(2026);
        $service = new AssessmentService(
            new ApplicationValidator($rules, new VinValidator($rules['vin']), $age),
            new LtvCalculator(), new DecisionEngine($rules['ltv']), $age,
            $rules['vehicle']['review_mileage_km'] ?? 400000,
        );
        $payload = ['vin' => 'XTA21099998765432', 'year' => 2022, 'mileage' => $mileage,
            'market_value' => 900000, 'requested_amount' => $amount, 'term_months' => 24];
        if ($omit) { unset($payload['mileage']); }
        return $service->assess($payload);
    }

    public static function decisions(): array
    {
        return [[0,450000,'approve'], [399999,450000,'approve'], [400000,450000,'approve'],
            [400001,450000,'review'], [500000,450000,'review'], [400001,675000,'review'],
            [400001,855000,'reject'], ['400001',450000,'review']];
    }

    #[DataProvider('decisions')]
    public function testDecisionAndLimit(mixed $mileage, int $amount, string $expected): void
    {
        $result = $this->assess($mileage, $amount);
        self::assertSame($expected, $result['decision']);
        self::assertSame($expected === 'approve' ? $amount : 0, $result['approved_limit']);
    }

    public static function invalid(): array
    {
        return [[null], [''], ['abc'], [-1], [500001], [1.5], [true], [[]]];
    }

    #[DataProvider('invalid')]
    public function testInvalidMileage(mixed $mileage): void
    {
        $this->expectException(ValidationException::class);
        $this->assess($mileage);
    }

    public function testMissingMileage(): void
    {
        $this->expectException(ValidationException::class);
        $this->assess(null, omit: true);
    }
}
