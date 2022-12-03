<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $notification;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user,$notification)
    {
      $this->user = $user; 
      $this->notification = $notification;
    } 

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        info('Broadcasting Chaneel ...........');
        return new Channel('croxx_notifications');
    }

    public function broadcastWith() {
        info(['Broadcasting Data...........', $this->user]);
        return [ 
          'user' =>  $this->user,
          'notification' => $this->notification,
        ]; 
      }
}
