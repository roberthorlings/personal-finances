<?php

namespace App\Providers;

use App\Model\Category;
use App\Model\RuleEngine;
use App\Model\Transaction;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Log;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    protected $ruleEngine;

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // Initialize rule engine
        $this->ruleEngine = new RuleEngine();
        $this->ruleEngine->init();

        // Apply rule engine when creating a transaction
        Transaction::creating([$this->ruleEngine, 'applyRules']);

        // Make sure the category key is being set when not provided
        Category::creating(function($category) {
            if(!$category->key) {
                $category->key = strtolower($category->name);
            }
            return true;
        });

    }
}
