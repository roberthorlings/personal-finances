<?php

namespace App\Model;

use Illuminate\Support\Facades\Log;

class RuleEngine
{
    var $categoriesByKey;
    var $rules;

    public function init() {
        $this->categoriesByKey = Category::get()->keyBy('key');
        $this->rules = include 'rules.php';
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

        //Log::debug("Applying rules for new transaction.", ["amount" => $transaction->amount, "description" => $transaction->description, "opposing_account_name" => $transaction->opposing_account_name]);
        $key = $this->getCategoryKey($transaction);
        if($key != null) {
            if($this->categoriesByKey->has($key)) {
                $category = $this->categoriesByKey[$key];

                Log::info("Category found for new transaction.", ["description" => $transaction->description, "key" => $key, "category" => $category->name]);
                $transaction->category_id = $category->id;
            } else {
                Log::warn("Rule applied to new transaction, but category does not exist", ["description" => $transaction->description, "key" => $key]);
            }
        } else {
            Log::debug("No rule applied to new transaction", ["amount" => $transaction->amount, "description" => $transaction->description, "opposing_account_name" => $transaction->opposing_account_name]);
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
                    //Log::debug("Applying rule for new transaction.", ["type" => $type, "paramOption" => $paramOption, "categoryKey" => $categoryKey]);

                    // Each paramOption can be either a scalar or an array with parameters
                    $options = is_array($paramOption) ? $paramOption : [$paramOption];
                    if($this->$type($transaction, ...$options)) {
                        //Log::debug("Rule applied to new transaction.", ["type" => $type, "paramOption" => $paramOption, "categoryKey" => $categoryKey]);
                        return $categoryKey;
                    }
                }
            }
        };

        return null;
    }

    private function descriptionContains($t, $value) {
        return strpos(strtolower($t->description), strtolower($value)) !== false;
    }

    private function opposingAccountContains($t, $value) {
        return strpos(strtolower($t->opposing_account_name), strtolower($value)) !== false;
    }
}
