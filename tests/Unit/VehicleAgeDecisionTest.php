<?php

declare(strict_types=1);

namespace CarMoneyLab\Tests\Unit;

use CarMoneyLab\Domain\{ApplicationValidator, AssessmentService, DecisionEngine, LtvCalculator, VehicleAge, VinValidator, ValidationException};
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class VehicleAgeDecisionTest extends TestCase
{
    private function assess(mixed $year, int $amount, int $mileage = 84000): array
    {
        $rules = require __DIR__ . '/../../backend/config/rules.php';
        $age = new VehicleAge(2026);
        $service = new AssessmentService(
            new ApplicationValidator($rules, new VinValidator($rules['vin']), $age),
            new LtvCalculator(), new DecisionEngine($rules['ltv']), $age,
            $rules['vehicle']['review_mileage_km'], $rules['vehicle']['max_age_years'],
        );
        return $service->assess(['vin'=>'XTA21099998765432', 'year'=>$year, 'mileage'=>$mileage,
            'market_value'=>900000, 'requested_amount'=>$amount, 'term_months'=>24]);
    }

    public static function cases(): array
    {
        $cases = [];
        foreach ([19,20,21,36] as $age) {
            foreach ([450000=>'approve',675000=>'review',855000=>'reject'] as $amount=>$decision) {
                $cases[] = [2026-$age,$amount,$age>20?'reject':$decision];
            }
        }
        return $cases;
    }

    #[DataProvider('cases')]
    public function testAgeAndLtv(int $year, int $amount, string $decision): void
    {
        $result=$this->assess($year,$amount);
        self::assertSame($decision,$result['decision']);
        self::assertSame($decision==='approve'?$amount:0,$result['approved_limit']);
    }

    public function testMileageCannotDowngradeAgeRejection(): void
    {
        self::assertSame('reject',$this->assess(2005,450000,400001)['decision']);
    }

    public static function invalidYears(): array { return [[null],[1989],[2027]]; }

    #[DataProvider('invalidYears')]
    public function testInvalidYear(mixed $year): void
    {
        $this->expectException(ValidationException::class);
        $this->assess($year,450000);
    }
}
