<?php

namespace Tests\Unit;

use App\Model\Category;
use App\Model\Statistics\CategoryStatsGenerator;
use App\Model\Transaction;
use Illuminate\Support\Collection;
use Tests\TestCase;

class CategoryStatsGeneratorTest extends TestCase
{
    public function testStatsGeneration()
    {
        $generator = new CategoryStatsGenerator();

        $categories = Collection::make([
            $this->category(1, 'no-children'),
            $this->category(2, 'multiple-children', [
                $this->category(3, 'child1'),
                $this->category(4, 'child2'),
            ]),
            $this->category(5, 'nested-children', [
                $this->category(6, 'childa', [
                    $this->category(7, 'childb')
                ])
            ]),
            $this->category(8, 'nested-children2', [
                $this->category(9, 'child2a'),
            ]),
            $this->category(10, 'completely-empty'),
        ]);

        $transactions = Collection::make([
            new Transaction(['date' => '2019-01-02', 'amount' => 1.01, 'category_id' => 1]),
            new Transaction(['date' => '2019-01-03', 'amount' => -2.02, 'category_id' => 1]),
            new Transaction(['date' => '2019-01-02', 'amount' => 3.03, 'category_id' => 3]),
            new Transaction(['date' => '2019-01-02', 'amount' => 4.04, 'category_id' => 4]),

            new Transaction(['date' => '2019-01-02', 'amount' => 5.05, 'category_id' => 5]),
            new Transaction(['date' => '2019-01-02', 'amount' => 6.06, 'category_id' => 6]),
            new Transaction(['date' => '2019-01-02', 'amount' => 7.07, 'category_id' => 7]),

            new Transaction(['date' => '2019-01-02', 'amount' => 9.09, 'category_id' => 9]),

            new Transaction(['date' => '2018-12-02', 'amount' => 10.10, 'category_id' => 10]),
            new Transaction(['date' => '2019-01-02', 'amount' => 11.11, 'category_id' => null]),
        ]);

        $stats = $generator->convertToStats($transactions, $categories);

        // Convert numbers to strings for proper comparison
        $stats = $this->convertAmountsToStrings($stats);

        // Category 1 has a two transactions of 1 each and no children
        $this->assertContains([
            'category_id' => 1,
            'year' => 2019,
            'month' => 1,
            'amount' => '-1.01',
            'grand_total' => '-1.01'
        ], $stats);

        // Category 2 has a no transactions and children with transactions 3 and 4
        $this->assertContains([
            'category_id' => 2,
            'year' => 2019,
            'month' => 1,
            'amount' => '0.00',
            'grand_total' => '7.07'
        ], $stats);

        // Category 3 has one transaction and no children
        // Category 4 is the same testcase
        $this->assertContains([
            'category_id' => 3,
            'year' => 2019,
            'month' => 1,
            'amount' => '3.03',
            'grand_total' => '3.03'
        ], $stats);

        // Category 5 has one transaction and its descendants have one as well
        $this->assertContains([
            'category_id' => 5,
            'year' => 2019,
            'month' => 1,
            'amount' => '5.05',
            'grand_total' => '18.18'
        ], $stats);

        // Category 6 has one transaction and its descendants have one as well
        $this->assertContains([
            'category_id' => 6,
            'year' => 2019,
            'month' => 1,
            'amount' => '6.06',
            'grand_total' => '13.13'
        ], $stats);

        // Category 8 has no transactions, but its descendants do
        $this->assertContains([
            'category_id' => 8,
            'year' => 2019,
            'month' => 1,
            'amount' => '0.00',
            'grand_total' => '9.09'
        ], $stats);

        // Category 10 has only a transaction in another month
        $this->assertContains([
            'category_id' => 10,
            'year' => 2018,
            'month' => 12,
            'amount' => '10.10',
            'grand_total' => '10.10'
        ], $stats);

        // 'No category' has an entry as well
        $this->assertContains([
            'category_id' => null,
            'year' => 2019,
            'month' => 1,
            'amount' => '11.11',
            'grand_total' => '11.11'
        ], $stats);

        // Verify that category 10 does not have an entry in 2019/01
        $this->assertTrue($stats->filter(function($stat) { return $stat['category_id'] === 10 && $stat['year'] === 2019 && $stat['month'] === 1; })->isEmpty());
    }

    private function category(int $id, string $name, array $children = []): Category
    {
        $category = new Category(['name' => $name]);
        $category->id = $id;
        $category->children = Collection::make($children);

        return $category;
    }

    private function convertAmountsToStrings(Collection $stats): Collection
    {
        return $stats->map(function($stat) {
            return array_merge(
                $stat,
                [
                    'amount' => number_format($stat['amount'], 2),
                    'grand_total' => number_format($stat['grand_total'], 2)
                ]
            );
        });
    }

}
