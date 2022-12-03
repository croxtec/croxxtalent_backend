<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use App\Models\Campaign;

class CampaignPublished extends Mailable
{
    use Queueable, SerializesModels;

    public $cvReference;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "Campaign published";
        return $this->subject($subject)
                    ->view('api.emails.campaign_published')
                    ->text('api.emails.campaign_published_plain')
                    ->with([
                        'name' => $this->campaign->user->display_name,
                        'email' => $this->campaign->user->email,
                        'campaign' => $this->campaign,
                    ]);
    }
}
