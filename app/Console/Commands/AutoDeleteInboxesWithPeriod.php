<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Email;
use App\Statistic;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AutoDeleteInboxesWithPeriod extends Command
{
    protected $signature = 'mailcare:deleteinboxeswithperiod
							{emailKeyword=qa_automation_api : email addresses to be deleted} 
							{daysToKeepEmails=7} : number of days to keep emails in system';

    protected $description = 'Automatically soft delete emails for inboxes matched keyword';

    public function handle()
    {
        $this->line("--------------------------------------------------");
        $this->line("AutoDelete from automationtest command executed at ".Carbon::now());
		
		$emailKeyword = $this->argument('emailKeyword');
		$daysToKeepEmails = $this->argument('daysToKeepEmails');
		$size = 0;
		$counter = 0;

		$this->line("Deleting email for inboxes contain keyword $emailKeyword received before last $daysToKeepEmails days");


		Email::select('emails.*')
				->join('inboxes', 'emails.inbox_id', '=', 'inboxes.id')
				->where('favorite', false)			
				->where('email', 'like', "%$emailKeyword%")
				->where('emails.created_at','<',Carbon::now()->subDays($daysToKeepEmails))
				->oldest()
				->chunkById(100, function ($emails) use (&$size, &$counter) {

			foreach ($emails as $email) {
				 
				$size = $size + $email->size_in_bytes;
				$counter = $counter + 1;
				$email->delete();
				
			}			
			
			$this->line("$counter emails deleted ($size bytes)");
			
		}, 'emails.id');
		
		
    }
}
