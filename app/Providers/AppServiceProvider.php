<?php

namespace App\Providers;

use App\Events\AnnouncementPublished;
use App\Events\InvoiceIssued;
use App\Events\InvoiceOverdue;
use App\Events\MaintenanceAssigned;
use App\Events\MaintenanceCreated;
use App\Events\MaintenanceSlaBreached;
use App\Events\MaintenanceStatusChanged;
use App\Events\PaymentRejected;
use App\Events\PaymentSubmitted;
use App\Events\PaymentVerified;
use App\Listeners\NotifyAdminsOfPaymentSubmitted;
use App\Listeners\NotifyStaffOfMaintenanceCreated;
use App\Listeners\NotifyStaffOfMaintenanceSla;
use App\Listeners\SendAnnouncementPublishedNotification;
use App\Listeners\SendInvoiceIssuedNotification;
use App\Listeners\SendInvoiceOverdueNotification;
use App\Listeners\SendMaintenanceAssignedNotification;
use App\Listeners\SendMaintenanceStatusNotification;
use App\Listeners\SendPaymentRejectedNotification;
use App\Listeners\SendPaymentVerifiedNotification;
use App\Models\User;
use App\Services\AnnouncementService;
use App\Services\AuditService;
use App\Services\BillingService;
use App\Services\DepositService;
use App\Services\ExportService;
use App\Services\FinancialService;
use App\Services\GlobalSearchService;
use App\Services\LateFeeCalculator;
use App\Services\LeaseService;
use App\Services\MaintenanceService;
use App\Services\OccupancyService;
use App\Services\PaymentService;
use App\Services\ProrationCalculator;
use App\Services\ReminderService;
use App\Services\ReportService;
use App\Services\SettingsService;
use App\Services\TenantService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
        $this->app->singleton(AuditService::class);
        $this->app->singleton(OccupancyService::class);
        $this->app->singleton(TenantService::class);
        $this->app->singleton(DepositService::class);
        $this->app->singleton(LeaseService::class);
        $this->app->singleton(ProrationCalculator::class);
        $this->app->singleton(LateFeeCalculator::class);
        $this->app->singleton(BillingService::class);
        $this->app->singleton(PaymentService::class);
        $this->app->singleton(MaintenanceService::class);
        $this->app->singleton(AnnouncementService::class);
        $this->app->singleton(ReminderService::class);
        $this->app->singleton(FinancialService::class);
        $this->app->singleton(ExportService::class);
        $this->app->singleton(ReportService::class);
        $this->app->singleton(GlobalSearchService::class);
        $this->app->singleton(ExportService::class);
        $this->app->singleton(ReportService::class);
        $this->app->singleton(GlobalSearchService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(InvoiceIssued::class, SendInvoiceIssuedNotification::class);
        Event::listen(InvoiceOverdue::class, SendInvoiceOverdueNotification::class);
        Event::listen(PaymentSubmitted::class, NotifyAdminsOfPaymentSubmitted::class);
        Event::listen(PaymentVerified::class, SendPaymentVerifiedNotification::class);
        Event::listen(PaymentRejected::class, SendPaymentRejectedNotification::class);
        Event::listen(MaintenanceCreated::class, NotifyStaffOfMaintenanceCreated::class);
        Event::listen(MaintenanceAssigned::class, SendMaintenanceAssignedNotification::class);
        Event::listen(MaintenanceStatusChanged::class, SendMaintenanceStatusNotification::class);
        Event::listen(MaintenanceSlaBreached::class, NotifyStaffOfMaintenanceSla::class);
        Event::listen(AnnouncementPublished::class, SendAnnouncementPublishedNotification::class);

        // Strict mode surfaces N+1 queries and silent mass-assignment
        // mistakes during local development without affecting production.
        Model::shouldBeStrict($this->app->environment('local'));

        // Owners have full access to every ability in the application.
        Gate::before(function (User $user, string $ability): ?bool {
            return $user->hasRole('owner') ? true : null;
        });

        Password::defaults(function (): Password {
            $rule = Password::min(8);

            return $this->app->isProduction()
                ? $rule->mixedCase()->numbers()->uncompromised()
                : $rule;
        });

        RateLimiter::for('login', function (Request $request): Limit {
            $key = (string) $request->input('email').'|'.$request->ip();

            return Limit::perMinute(5)->by($key);
        });
    }
}
