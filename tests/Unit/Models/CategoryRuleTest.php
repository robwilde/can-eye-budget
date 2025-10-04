<?php

declare(strict_types=1);

use App\Models\CategoryRule;

covers(CategoryRule::class);

describe('CategoryRule Model', function () {
    describe('Description Matching', function () {
        test('contains operator matches substring', function () {
            $rule = new CategoryRule([
                'field'    => 'description',
                'operator' => 'contains',
                'value'    => 'Real Living',
            ]);

            expect($rule->matches('Ext Tfr Real Living WV', 100.0))->toBeTrue();
            expect($rule->matches('Something else entirely', 100.0))->toBeFalse();
        });

        test('contains operator is case insensitive', function () {
            $rule = new CategoryRule([
                'field'    => 'description',
                'operator' => 'contains',
                'value'    => 'netflix',
            ]);

            expect($rule->matches('NETFLIX SUBSCRIPTION', 15.99))->toBeTrue();
            expect($rule->matches('Netflix Monthly', 15.99))->toBeTrue();
            expect($rule->matches('NetFlix Premium', 15.99))->toBeTrue();
        });

        test('contains operator handles Unicode characters', function () {
            $rule = new CategoryRule([
                'field'    => 'description',
                'operator' => 'contains',
                'value'    => 'café',
            ]);

            expect($rule->matches('Payment to Café Central', 25.50))->toBeTrue();
            expect($rule->matches('CAFÉ CENTRAL RECEIPT', 25.50))->toBeTrue();
        });

        test('contains operator handles extra whitespace', function () {
            $rule = new CategoryRule([
                'field'    => 'description',
                'operator' => 'contains',
                'value'    => '  Real Living  ',
            ]);

            expect($rule->matches('  Ext Tfr Real Living WV  ', 100.0))->toBeTrue();
        });

        test('equals operator matches exact strings', function () {
            $rule = new CategoryRule([
                'field'    => 'description',
                'operator' => 'equals',
                'value'    => 'SMS Alert Fee',
            ]);

            expect($rule->matches('SMS Alert Fee', 9.36))->toBeTrue();
            expect($rule->matches('SMS Alert Fee Extra', 9.36))->toBeFalse();
            expect($rule->matches('Extra SMS Alert Fee', 9.36))->toBeFalse();
        });

        test('equals operator is case insensitive', function () {
            $rule = new CategoryRule([
                'field'    => 'description',
                'operator' => 'equals',
                'value'    => 'sms alert fee',
            ]);

            expect($rule->matches('SMS Alert Fee', 9.36))->toBeTrue();
            expect($rule->matches('SMS ALERT FEE', 9.36))->toBeTrue();
        });

        test('starts_with operator matches beginning of string', function () {
            $rule = new CategoryRule([
                'field'    => 'description',
                'operator' => 'starts_with',
                'value'    => 'Direct Debit',
            ]);

            expect($rule->matches('Direct Debit GOLDEN INSURANCE', 70.17))->toBeTrue();
            expect($rule->matches('Payment Direct Debit', 70.17))->toBeFalse();
        });

        test('starts_with operator is case insensitive', function () {
            $rule = new CategoryRule([
                'field'    => 'description',
                'operator' => 'starts_with',
                'value'    => 'direct debit',
            ]);

            expect($rule->matches('DIRECT DEBIT INSURANCE', 70.17))->toBeTrue();
            expect($rule->matches('Direct Debit MCF', 70.17))->toBeTrue();
        });

        test('ends_with operator matches end of string', function () {
            $rule = new CategoryRule([
                'field'    => 'description',
                'operator' => 'ends_with',
                'value'    => 'Queen Street',
            ]);

            expect($rule->matches('Real Living WV WBC - 260 Queen Street', 500.0))->toBeTrue();
            expect($rule->matches('Queen Street Property', 500.0))->toBeFalse();
        });

        test('ends_with operator is case insensitive', function () {
            $rule = new CategoryRule([
                'field'    => 'description',
                'operator' => 'ends_with',
                'value'    => 'queen street',
            ]);

            expect($rule->matches('Real Living WV - 260 QUEEN STREET', 500.0))->toBeTrue();
            expect($rule->matches('Payment to 123 Queen Street', 500.0))->toBeTrue();
        });

        test('unknown operator returns false', function () {
            $rule = new CategoryRule([
                'field'    => 'description',
                'operator' => 'unknown_operator',
                'value'    => 'test',
            ]);

            expect($rule->matches('test description', 100.0))->toBeFalse();
        });
    });

    describe('Amount Matching', function () {
        test('equals operator matches exact amounts', function () {
            $rule = new CategoryRule([
                'field'    => 'amount',
                'operator' => 'equals',
                'value'    => '9.36',
            ]);

            expect($rule->matches('SMS Alert Fee', 9.36))->toBeTrue();
            expect($rule->matches('SMS Alert Fee', 9.35))->toBeFalse();
            expect($rule->matches('SMS Alert Fee', 9.37))->toBeFalse();
        });

        test('equals operator handles float precision', function () {
            $rule = new CategoryRule([
                'field'    => 'amount',
                'operator' => 'equals',
                'value'    => '9.36',
            ]);

            // Test amounts that are very close but not exactly equal due to floating point
            expect($rule->matches('Test', 9.3600001))->toBeTrue(); // Within 0.001 tolerance
            expect($rule->matches('Test', 9.3605))->toBeTrue(); // Within 0.001 tolerance
            expect($rule->matches('Test', 9.362))->toBeFalse(); // Outside 0.001 tolerance
        });

        test('greater_than operator works correctly', function () {
            $rule = new CategoryRule([
                'field'    => 'amount',
                'operator' => 'greater_than',
                'value'    => '100.00',
            ]);

            expect($rule->matches('Large Payment', 150.00))->toBeTrue();
            expect($rule->matches('Large Payment', 100.01))->toBeTrue();
            expect($rule->matches('Small Payment', 100.00))->toBeFalse();
            expect($rule->matches('Small Payment', 50.00))->toBeFalse();
        });

        test('less_than operator works correctly', function () {
            $rule = new CategoryRule([
                'field'    => 'amount',
                'operator' => 'less_than',
                'value'    => '50.00',
            ]);

            expect($rule->matches('Small Payment', 25.00))->toBeTrue();
            expect($rule->matches('Small Payment', 49.99))->toBeTrue();
            expect($rule->matches('Large Payment', 50.00))->toBeFalse();
            expect($rule->matches('Large Payment', 75.00))->toBeFalse();
        });

        test('negative amounts work correctly', function () {
            $rule = new CategoryRule([
                'field'    => 'amount',
                'operator' => 'less_than',
                'value'    => '0.00',
            ]);

            expect($rule->matches('Refund', -25.00))->toBeTrue();
            expect($rule->matches('Payment', 25.00))->toBeFalse();
        });

        test('amount operators handle string values', function () {
            $rule = new CategoryRule([
                'field'    => 'amount',
                'operator' => 'equals',
                'value'    => 'not_a_number',
            ]);

            // Should handle non-numeric rule values gracefully
            expect($rule->matches('Test', 100.0))->toBeFalse();
        });

        test('unknown amount operator returns false', function () {
            $rule = new CategoryRule([
                'field'    => 'amount',
                'operator' => 'unknown_operator',
                'value'    => '100.00',
            ]);

            expect($rule->matches('Test Transaction', 100.00))->toBeFalse();
        });
    });

    describe('Edge Cases', function () {
        test('empty description matches empty rule value with equals', function () {
            $rule = new CategoryRule([
                'field'    => 'description',
                'operator' => 'equals',
                'value'    => '',
            ]);

            expect($rule->matches('', 100.0))->toBeTrue();
            expect($rule->matches('Not empty', 100.0))->toBeFalse();
        });

        test('empty description with contains operator', function () {
            $rule = new CategoryRule([
                'field'    => 'description',
                'operator' => 'contains',
                'value'    => 'test',
            ]);

            expect($rule->matches('', 100.0))->toBeFalse();
        });

        test('empty rule value with contains operator', function () {
            $rule = new CategoryRule([
                'field'    => 'description',
                'operator' => 'contains',
                'value'    => '',
            ]);

            // Empty string is contained in any string
            expect($rule->matches('Any description', 100.0))->toBeTrue();
            expect($rule->matches('', 100.0))->toBeTrue();
        });

        test('special characters in descriptions', function () {
            $rule = new CategoryRule([
                'field'    => 'description',
                'operator' => 'contains',
                'value'    => 'McDonald\'s',
            ]);

            expect($rule->matches('Payment to McDonald\'s Restaurant', 12.50))->toBeTrue();
        });

        test('very long descriptions', function () {
            $longDescription = str_repeat('Very long transaction description ', 100);
            $rule = new CategoryRule([
                'field'    => 'description',
                'operator' => 'contains',
                'value'    => 'long transaction',
            ]);

            expect($rule->matches($longDescription, 100.0))->toBeTrue();
        });

        test('zero amounts', function () {
            $rule = new CategoryRule([
                'field'    => 'amount',
                'operator' => 'equals',
                'value'    => '0.00',
            ]);

            expect($rule->matches('Zero amount transaction', 0.0))->toBeTrue();
            expect($rule->matches('Small amount transaction', 0.01))->toBeFalse();
        });
    });
});
