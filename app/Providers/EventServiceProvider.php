<?php

namespace App\Providers;

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

        Transaction::creating([$this->ruleEngine, 'applyRules']);
    }
}
