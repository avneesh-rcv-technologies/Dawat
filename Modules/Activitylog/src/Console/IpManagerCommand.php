<?php

namespace Modules\Activitylog\Console;

use Illuminate\Console\Command;
use Modules\Activitylog\Models\AllowedIp;
use Modules\Activitylog\Models\BlockedIp;


class IpManagerCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'ip:manage
		{action : add-allowed|block|unblock}
		{ip : The IP address}
		{--note= : Note for allowed IP}
		{--reason= : Reason for blocking}
		{--expires= : Expiry date (Y-m-d H:i:s)}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Manage allowed and blocked IPs (add allowed, block, unblock)';

	public function handle()
	{
		$action = $this->argument('action');
		$ip = $this->argument('ip');

		if (!filter_var($ip, FILTER_VALIDATE_IP)) {
			$this->error('Invalid IP address.');
			return 1;
		}

		switch ($action) {
			case 'add-allowed':
				$this->addAllowed($ip);
				break;
			case 'block':
				$this->blockIp($ip);
				break;
			case 'unblock':
				$this->unblockIp($ip);
				break;
			default:
				$this->error('Unknown action. Use add-allowed, block, or unblock.');
				return 1;
		}
		return 0;
	}

	protected function addAllowed($ip)
	{
		$note = $this->option('note');
		$expires = $this->option('expires');

		AllowedIp::updateOrCreate(
			['ip_address' => $ip],
			[
				'note' => $note,
				'is_active' => true,
				'expires_at' => $expires,
				'approved_by_id' => null,
				'approved_by_type' => null,
			]
		);
		$this->info("Allowed IP added: {$ip}");
	}

	protected function blockIp($ip)
	{
		$reason = $this->option('reason');
		$expires = $this->option('expires');

		BlockedIp::updateOrCreate(
			['ip_address' => $ip],
			[
				'reason' => $reason,
				'is_active' => true,
				'blocked_by_id' => null,
				'blocked_by_type' => null,
				'expires_at' => $expires,
			]
		);
		$this->info("Blocked IP: {$ip}");
	}

	protected function unblockIp($ip)
	{
		$blocked = BlockedIp::where('ip_address', $ip)->first();
		if ($blocked) {
			$blocked->is_active = false;
			$blocked->save();
			$this->info("Unblocked IP: {$ip}");
		} else {
			$this->warn("IP not found in blocked list: {$ip}");
		}
	}
}
