<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\RealtimeSetting;
use App\MatchedHistory;

class MinitlyCheckConditionSatisfied extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(RealtimeSetting $realtime_setting, MatchedHistory $matched_history)
    {
        //
        $this->realtime_setting = $realtime_setting;
        $this->matched_history = $matched_history;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.minitlycheck_conditionsatisfied')
        ->subject('Kabuboard 監視条件成立 '.$this->matched_history->matchtype->detail )
        ->with([
          'code' => $this->realtime_setting->stock->code,
          'name' => $this->realtime_setting->stock->name,
          'upperlimit' => $this->realtime_setting->upperlimit,
          'lowerlimit' => $this->realtime_setting->lowerlimit,
          'checking_price' => $this->realtime_setting->realtime_checking->price,

          'changerate' => $this->realtime_setting->changerate,
          'checking_rate' => $this->realtime_setting->realtime_checking->rate,

          'matchtype_id' => $this->matched_history->matchtype->id,
        ]);
    }
}
