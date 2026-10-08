<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\ReminderLog;
use App\Models\User;
use App\Notifications\DueReminderNotification;
use App\Notifications\LeaseExpiryNotification;
use App\Notifications\OverdueReminderNotification;

class ReminderService
{
    public function __construct(private readonly SettingsService $settings) {}

    /**
     * Notify tenants whose invoices fall exactly on a configured offset before due.
     *
     * @return int number of distinct notifications created
     */
    public function sendDueReminders(): int
    {
        $offsets = (array) $this->settings->get('reminder.days_before_due', [7, 3, 1, 0]);
        $count = 0;

        foreach ($offsets as $offset) {
            $date = now()->addDays((int) $offset)->toDateString();

            $invoices = Invoice::query()
                ->whereIn('status', ['issued', 'partially_paid'])
                ->whereDate('due_date', $date)
                ->with('tenant.user')
                ->get();

            foreach ($invoices as $invoice) {
                $key = 'invoice_due:'.((int) $offset);

                if (ReminderLog::alreadySent($invoice, $key)) {
                    continue;
                }

                $user = $invoice->tenant?->user;

                if (! $user) {
                    continue;
                }

                $user->notify(new DueReminderNotification($invoice, (int) $offset));

                $this->notifyAdminSummary(new DueReminderNotification($invoice, (int) $offset));

                ReminderLog::markSent($invoice, $key);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Notify tenants whose invoices fell on a configured offset after due.
     */
    public function sendOverdueReminders(): int
    {
        $offsets = (array) $this->settings->get('reminder.overdue_days', [1, 3, 7]);
        $count = 0;

        foreach ($offsets as $offset) {
            $date = now()->subDays((int) $offset)->toDateString();

            $invoices = Invoice::query()
                ->whereIn('status', ['overdue', 'issued', 'partially_paid'])
                ->whereDate('due_date', $date)
                ->with('tenant.user')
                ->get();

            foreach ($invoices as $invoice) {
                $key = 'invoice_overdue:'.((int) $offset);

                if (ReminderLog::alreadySent($invoice, $key)) {
                    continue;
                }

                $user = $invoice->tenant?->user;

                if (! $user) {
                    continue;
                }

                $user->notify(new OverdueReminderNotification($invoice, (int) $offset));
                $this->notifyAdminSummary(new OverdueReminderNotification($invoice, (int) $offset));
                ReminderLog::markSent($invoice, $key);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Notify tenants (and admin digest) whose leases expire within a configured offset.
     */
    public function sendLeaseExpiryReminders(): int
    {
        $offsets = (array) $this->settings->get('reminder.lease_expiry_days', [90, 30, 14, 7]);
        $count = 0;

        foreach ($offsets as $offset) {
            $date = now()->addDays((int) $offset)->toDateString();

            $leases = Lease::query()
                ->whereIn('status', ['active', 'expiring'])
                ->whereDate('end_date', $date)
                ->with('tenant.user')
                ->get();

            foreach ($leases as $lease) {
                $key = 'lease_expiry:'.((int) $offset);

                if (ReminderLog::alreadySent($lease, $key)) {
                    continue;
                }

                $user = $lease->tenant?->user;

                if ($user) {
                    $user->notify(new LeaseExpiryNotification($lease, (int) $offset));
                    $this->notifyAdminSummary(new LeaseExpiryNotification($lease, (int) $offset));
                    $count++;
                }

                Lease::query()->whereKey($lease->id)->update(['status' => 'expiring']);
                ReminderLog::markSent($lease, $key);
            }
        }

        return $count;
    }

    private function notifyAdminSummary($notification): void
    {
        User::query()
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['owner', 'admin', 'finance']))
            ->get()
            ->each(fn (User $user) => $user->notify($notification));
    }
}
