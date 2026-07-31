<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureMailFromDatabase();
        $this->configureGoogleOAuthFromDatabase();
    }

    /**
     * Configure mail settings from database if available.
     */
    private function configureMailFromDatabase(): void
    {
        try {
            // Check if table exists to prevent errors during migrations
            if (!$this->tableExists('settings')) {
                return;
            }

            $smtpSettings = Setting::group('smtp');

            // Only update config if there are smtp settings in database
            if (!empty($smtpSettings)) {
                $mailerConfig = config('mail.mailers.smtp', []);

                // Update with database values, falling back to env if not set
                $mailerConfig['host'] = $smtpSettings['smtp_host'] ?? env('MAIL_HOST');
                $mailerConfig['port'] = $smtpSettings['smtp_port'] ?? env('MAIL_PORT');
                $mailerConfig['username'] = $smtpSettings['smtp_username'] ?? env('MAIL_USERNAME');
                $mailerConfig['password'] = $smtpSettings['smtp_password'] ?? env('MAIL_PASSWORD');

                if (isset($smtpSettings['smtp_encryption'])) {
                    // Convert ssl to smtps (Laravel expects smtps, not ssl)
                    $encryption = $smtpSettings['smtp_encryption'];
                    $mailerConfig['scheme'] = $encryption === 'ssl' ? 'smtps' : $encryption;
                }

                config(['mail.mailers.smtp' => $mailerConfig]);

                // Update from address if set
                if (isset($smtpSettings['smtp_from_email']) || isset($smtpSettings['smtp_from_name'])) {
                    config([
                        'mail.from.address' => $smtpSettings['smtp_from_email'] ?? env('MAIL_FROM_ADDRESS'),
                        'mail.from.name' => $smtpSettings['smtp_from_name'] ?? env('MAIL_FROM_NAME'),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Silently fail - use env fallback
            // Log the error if needed
        }
    }

    /**
     * Configure Google Sign-In credentials from the database, if an admin has
     * set them up (GameSet/Admin → Google Sign-In settings) — mirrors
     * configureMailFromDatabase()'s exact pattern so credentials are editable
     * at runtime instead of requiring an .env deploy.
     */
    private function configureGoogleOAuthFromDatabase(): void
    {
        try {
            if (!$this->tableExists('settings')) {
                return;
            }

            $googleSettings = Setting::group('google_oauth');
            if (empty($googleSettings['google_client_id']) || empty($googleSettings['google_client_secret'])) {
                return;
            }

            // 'redirect' is deliberately left unset here (and set explicitly via
            // ->redirectUrl() in GoogleAuthController instead) — computing a
            // route() URL this early in the boot cycle is unnecessary risk for
            // no benefit.
            config([
                'services.google.client_id'     => $googleSettings['google_client_id'],
                'services.google.client_secret'  => $googleSettings['google_client_secret'],
            ]);
        } catch (\Exception $e) {
            // Silently fail — Google Sign-In simply stays unavailable/hidden.
        }
    }

    /**
     * Check if a database table exists.
     */
    private function tableExists(string $table): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasTable($table);
        } catch (\Exception $e) {
            return false;
        }
    }
}
