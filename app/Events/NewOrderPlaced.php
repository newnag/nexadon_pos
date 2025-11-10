<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOrderPlaced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $order;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('kitchen-channel'),
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'order.placed';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        // Load all necessary relationships for the kitchen display
        $this->order->load([
            'table',
            'user.role',
            'orderItems.menuItem.category',
            'orderItems.modifiers'
        ]);

        return [
            'order_id' => $this->order->id,
            'table' => [
                'id' => $this->order->table->id,
                'number' => $this->order->table->table_number,
            ],
            'waiter' => [
                'id' => $this->order->user->id,
                'name' => $this->order->user->name,
            ],
            'status' => $this->order->status,
            'total_amount' => $this->order->total_amount,
            'items' => $this->order->orderItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'menu_item' => [
                        'id' => $item->menuItem->id,
                        'name' => $item->menuItem->name,
                        'category' => $item->menuItem->category->name,
                    ],
                    'quantity' => $item->quantity,
                    'notes' => $item->notes,
                    'modifiers' => $item->modifiers->map(function ($modifier) {
                        return [
                            'id' => $modifier->id,
                            'name' => $modifier->name,
                        ];
                    }),
                ];
            }),
            'created_at' => $this->order->created_at->toIso8601String(),
        ];
    }
}
