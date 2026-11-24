<?php

namespace Modules\Activitylog\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterActivitylogPermissions
{
    public function handle(RegisterPermissions $event): void
    {
        $event->registerArray([
            'name' => 'access_control',
            'code' => 'activity-log',
            'guard' => ['admin'],
            'key' => [
                'activity-log.advance-logging' => 'View Advance Activity Logs',
                'activity-log.advance-logging.show' => 'View Activity Log Detail',
                'activity-log.advance-logging.block-ip' => 'Block IP From Activity Log',
                'activity-log.advance-logging.candlestick-chart' => 'View Activity Candlestick Chart',
                'activity-log.advance-logging.candlestick-data' => 'View Activity Candlestick Data',
                'activity-log.advance-logging.heatmap-data' => 'View Activity Heatmap Data',
                'activity-log.blocked-ips' => 'View Blocked IPs',
                'activity-log.access-requests' => 'View Admin Access Requests',
                'activity-log.access-requests.approve-form' => 'Approve Access Request Form',
                'activity-log.access-requests.approve' => 'Approve Access Request',
                'activity-log.access-requests.reject' => 'Reject Access Request',
                'activity-log.access-requests.revoke' => 'Revoke Access Request',
                'activity-log.access-requests.toggle-status' => 'Bulk Toggle Access Requests',
                'activity-log.is_blocked' => 'Toggle IP Blocking',
            ],
        ]);
    }
}
