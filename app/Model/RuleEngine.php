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
                'supermarkt' => [ 'heijn', 'jumbo', 'plus', 'hoogvliet', 'AH Oude Dorp', 'Supermarkt' ],
                'speciaalzaak' => [ 'castellum houten', 'slagerij', 'bakkerij', 'kaasspecialist', 'gelderblom', 'Pour Certain', 'Peterse Kaas', 'PINDAKAASWINKEL', 'CCV*D. Bijl', 'CCV D. Bijl', 'Robert van Twillert', 'Robertsnoten', 'Vishandel', 'Sterpoelier', 'P.Ultee', 'Natuurwinkel'],
                'drogist' => [ 'etos', 'kruidvat', 'rituals', 'Drogist'],
                'parfumerie' => [ 'Douglas', 'ICI Paris', 'parfumerie'],
                'hellofresh' => ['hellofresh'],
                'huishouden' => ['bigbazar'],
                'eten buitenshuis' => ['kookschool', 'barista', 'Kiosk', 'Rhijnauwen', 'STARBUCKS', 'Broodje Ben', 'Coffee Star', 'CAFETARIA', 'restaurant', 'GRANDCAFE', 'La place', 'HAJE ', 'VELDKEUKEN'],
                'huisdieren' => ['dierspecialist', 'dierenkliniek'],
                'persoonlijke verzorging' => ['barbershop', 'CCV*MAX B.V.', 'Hizi Hair', 'Boots Houten'],
                'bloemen/planten' => ['Bloemen', 'DE SCHOUW', 'Intratuin'],
                'lezen' => ['Bruna', 'Readshop', 'Read Shop', ' AKO ', 'Boekhandel', 'Broese'],
                'muziek' => ['Sjofar'],
                'geldopnames' => ['geldautomaat', 'GEA '],

                'inrichting' => [ 'ikea', 'action', 'blokker', 'hema', 'jysk', 'kwantum', 'xenos', 'Dille&Kamille' ],
                'klussen' => [ 'praxis', 'gamma', 'karwei' ],
                'electronica' => [ 'coolblue', 'robbbshop', 'MediaMarkt'],

                'kleding' => ['we fashion', 'We Europe', 'miss etam', 'zeeman', 'Hunkemoeller', 'H & M', 'H EN M', 'H&M', 'hm.com', 'C&A', 'C & A', 'Bristol', 'Jeans Centre', 'kledingreparatie'],
                'schoenen' => ['vanHaren', 'van Haren'],

                'benzine' => ['esso', 'tango', 'shell', 'total', 'BP'],

                'autoverzekering' => ['Polis 914788'],
                'wegenbelasting' => ['Incasso algemeen doorlopend 49-XD-XB'],
                'auto' => ['SOS Pechhulp'],
                'onderhoud auto' => ['Auto Totaal Houten'],
                'parkeren' => ['parkeer', 'parkeren', 'parking'],

                'rendement' => ['creditrente'],

                'spotify' => ['spotify'],
                'netflix' => ['netflix'],
                'internet' => ['xs4all'],

                'tandarts' => ['infomedics', 'famed'],

                'niet-anbi' => ['Broer op de Fil'],
                'anbi' => ['collectebonnen', 'EO Donateurs'],

                'oppas' => ['oppas'],
                'kinderopvangtoeslag' => ['VOORSCHOT KINDEROPVANG'],
                'speelgoed' => ['Intertoys'],
                'brillen/lenzen' => ['OPTIEK VAN DE VECHT', 'Hans Anders'],

                'bankkosten' => ['BetaalGemak'],
                'zwemles' => ['zwemles'],

                'uitstapjes' => ['theater', 'cinelounge']
            ],
            'opposingAccountContains' => [
                'verwarming, water, electra' => ['ENECO', 'GREENCHOICE', 'VITENS NV'],
                'verzekering' => ['Voogd Verzekering', 'AnderZorg NV', 'DAZURE'],
                'mobiele telefoon' => ['SIMYO'],
                'hypotheek' => ['ABN AMRO BANK NV'],

                'anbi' => ['NGK Houten', 'OPEN DOORS', 'STICHTING COMPASSION', 'Stichting de Navigators', 'IFES NEderland', 'Stichting Alpha', 'Ongeboren Kind', 'collectebonnen'],

                'kinderdagverblijf' => ['Kinderopvang Plezier'],
                'hobbies' => ['pianoles', 'Pianoservice'],
                'sport' => ['Racketcentrum'],
                'kleding' => ['WE Fashion', 'H & M', 'H EN M', 'H&M', 'C & A '],

                'timon' => ['timon'],
                'siriz' => ['siriz'],
                'isdat' => ['isdat'],
                'oppas' => ['Luuk van Vulpen', 'van de Plas', 'Daffe'],
                'ns' => ['NS REIZIGERS'],
                'ov' => ['OV-Chipkaart'],

                'kinderbijslag' => ['SVB Utrecht'],
                'school' => ['Fluenta']
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
