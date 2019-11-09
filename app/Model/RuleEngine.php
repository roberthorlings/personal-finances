<?php

namespace App\Model;

use Illuminate\Support\Facades\Log;

class RuleEngine
{
    var $categoriesByKey;
    var $rules;

    public function init() {
        $this->categoriesByKey = Category::get()->keyBy('key');
        $this->rules = [
            'descriptionContains' => [
                'supermarkt' => [ 'heijn', 'jumbo', 'plus', 'hoogvliet' ],
                'drogist' => [ 'etos', 'kruidvat' ],
                'parfumerie' => [ 'Douglas', 'ICI Paris' ],
                'inrichting' => [ 'ikea', 'action', 'blokker', 'hema', 'jysk', 'kwantum', 'xenos' ],
                'klussen' => [ 'praxis', 'gamma', 'karwei' ],
                'electronica' => [ 'coolblue', 'robbbshop'    ],
                'speciaalzaken' => [ 'castellum houten', 'slagerij', 'bakkerij', 'kaasspecialist', 'gelderblom', 'Pour Certain'],
                'hellofresh' => ['hellofresh'],
                'benzine' => ['esso', 'tango', 'shell', 'total', 'BP'],
                'rendement' => ['creditrente']
            ]
        ];
    }

    /**
     * @param Transaction $transaction
     * @return bool True if the transaction should be saved, false otherwise
     * @throws \Exception
     */
    public function applyRules(Transaction $transaction): bool {
        if($transaction->category_id) {
            Log::debug("Category is already set for new transaction. Skip rule engine for this transaction.", ["description" => $transaction->description, "category_id" => $transaction->category_id]);
            return true;
        }

        Log::debug("Applying rules for new transaction.", ["amount" => $transaction->amount, "description" => $transaction->description]);
        $key = $this->getCategoryKey($transaction);
        if($key != null) {
            $category = $this->categoriesByKey[$key];

            if ($category) {
                Log::info("Rules applied to new transaction.", ["description" => $transaction->description, "key" => $key, "category" => $category->name]);
                $transaction->category_id = $category->id;
            } else {
                Log::warn("Rules applied to new transaction, but category does not exist", ["description" => $transaction->description, "key" => $key]);
            }
        }

        return true;
    }

    private function getCategoryKey(Transaction $transaction): ?string {
        foreach($this->rules as $type => $rules) {
            if(!method_exists($this, $type)) {
                throw new \Exception("The rule type $type is not supported");
            }

            foreach($rules as $categoryKey => $paramOptions) {
                foreach($paramOptions as $paramOption) {
                    Log::debug("Applying rule for new transaction.", ["type" => $type, "paramOption" => $paramOption, "categoryKey" => $categoryKey]);

                    // Each paramOption can be either a scalar or an array with parameters
                    $options = is_array($paramOption) ? $paramOption : [$paramOption];
                    if($this->$type($transaction, ...$options)) {
                        Log::debug("Rule applied to new transaction.", ["type" => $type, "paramOption" => $paramOption, "categoryKey" => $categoryKey]);
                        return $categoryKey;
                    }
                }
            }
        };

        return null;
    }

    private function descriptionContains($t, $value) {
        return strpos($t->description, $value) !== false;
    }
}
