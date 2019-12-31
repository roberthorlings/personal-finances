<?php
// Rename this file to 'rules.php' to add your own rules
// Please note that each key in this array matches a method in the RuleEngine
return [
    // Add rules here to match on the transaction description
    // The key is the 'category key' for the category in your database. It can be anything.
    // The value is an array of matches to be found. In this case, every transaction with 'albert heijn' or 'jumbo' in the
    // description, will be categorized as 'supermarkt'
    'descriptionContains' => [
        'supermarkt' => ['albert heijn', 'jumbo'],
    ],
    'opposingAccountContains' => [
        // Add rules here to match on the transaction opposing account name
        // The key is the 'category key' for the category in your database. It can be anything.
        // The value is an array of matches to be found. In this case, every transaction with 'VattenFall' or 'GREENCHOICE' in the
        // opposing account name, will be categorized as 'vaste lasten'
        'vaste lasten' => ['VattenFall', 'GREENCHOICE']
    ]
];
