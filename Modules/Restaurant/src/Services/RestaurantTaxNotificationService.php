<?php

namespace Modules\Restaurant\Services;

use Illuminate\Support\Facades\Log;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\Notifications\Services\NotificationService;
use Modules\Restaurant\Models\Restaurant;
use Throwable;

class RestaurantTaxNotificationService
{
    public function __construct(private NotificationService $notifications) {}

    public function send(Restaurant $restaurant, string $event, array $data): void
    {
        // NotificationService also uses the shared handler, which flashes request input.
        $request = app()->bound('request') ? request() : null;
        $input = $request ? ($request->isJson() ? $request->json() : $request->request) : null;
        $body = $input?->all();
        $query = $request?->query->all();
        foreach (['ssn', 'ein'] as $field) {
            $input?->remove($field);
            $request?->query->remove($field);
        }
        try {
            $owner = $restaurant->admin;
            if (!$owner) {
                return;
            }
            $this->notifications->trigger('Restaurant', $event, $owner, array_merge([
                'restaurant_name' => $restaurant->name,
                'restaurant_id' => $restaurant->id,
            ], $data));
        } catch (Throwable $e) {
            try {
                $this->reportException($e);
            } catch (Throwable $reportingError) {
                Log::warning('Unable to report restaurant tax notification failure.', [
                    'event' => $event, 'exception_class' => get_class($reportingError),
                ]);
            }
        } finally {
            if ($input) {
                $input->replace($body);
                $request->query->replace($query);
            }
        }
    }

    protected function reportException(Throwable $e): void
    {
        ApiExceptionHandler::handle($e);
    }
}
