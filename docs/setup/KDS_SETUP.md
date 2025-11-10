# Real-time Kitchen Display System (KDS) Setup Documentation

## Overview

The Kitchen Display System (KDS) provides real-time order notifications to kitchen staff using Laravel Broadcasting. When a new order is placed, it is instantly broadcasted to all connected kitchen displays, ensuring timely order preparation.

## Architecture

### Components

1. **NewOrderPlaced Event** - Laravel event that broadcasts order data
2. **Private Channel** - Secure `kitchen-channel` for authorized users only
3. **Broadcasting Driver** - WebSocket connection (Reverb, Pusher, or Soketi)
4. **Frontend Listener** - React/Vue component that listens for new orders

### Broadcasting Flow

```
Customer places order
         ↓
OrderController creates order
         ↓
NewOrderPlaced event dispatched
         ↓
Event broadcasts to kitchen-channel
         ↓
Kitchen display receives real-time update
         ↓
Kitchen staff sees new order instantly
```

---

## Configuration

### 1. Broadcasting Driver Options

Laravel supports multiple broadcasting drivers. Choose based on your needs:

#### Option A: Laravel Reverb (Recommended - Built-in)

**Advantages:**
- Official Laravel package
- No third-party dependencies
- Free and self-hosted
- Easy to set up

**Installation:**
```bash
composer require laravel/reverb
php artisan reverb:install
```

**Environment variables (.env):**
```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http
```

**Start Reverb server:**
```bash
php artisan reverb:start
```

#### Option B: Pusher (Cloud-based)

**Advantages:**
- Hosted solution (no server management)
- Scalable
- Free tier available

**Installation:**
```bash
composer require pusher/pusher-php-server
```

**Environment variables (.env):**
```env
BROADCAST_CONNECTION=pusher

PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1
```

**Sign up at:** https://pusher.com

#### Option C: Soketi (Self-hosted Pusher alternative)

**Advantages:**
- Open-source Pusher protocol compatible
- Self-hosted (free)
- Uses same client libraries as Pusher

**Installation:**
```bash
# Install Soketi globally
npm install -g @soketi/soketi

# Or using Docker
docker run -p 6001:6001 quay.io/soketi/soketi:latest
```

**Environment variables (.env):**
```env
BROADCAST_CONNECTION=pusher

PUSHER_APP_ID=app-id
PUSHER_APP_KEY=app-key
PUSHER_APP_SECRET=app-secret
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
```

**Start Soketi server:**
```bash
soketi start
```

---

### 2. Queue Configuration

For optimal performance, configure a queue driver to handle broadcasting asynchronously.

**Environment variables (.env):**
```env
QUEUE_CONNECTION=database
# Or use redis for better performance: redis
```

**Run migrations for queue:**
```bash
php artisan queue:table
php artisan migrate
```

**Start queue worker:**
```bash
php artisan queue:work
```

---

## Event Details

### NewOrderPlaced Event

**Location:** `app/Events/NewOrderPlaced.php`

**Channel:** `kitchen-channel` (Private)

**Event Name:** `order.placed`

**Broadcasted Data:**
```json
{
  "order_id": 45,
  "table": {
    "id": 1,
    "number": "T-001"
  },
  "waiter": {
    "id": 3,
    "name": "Sarah Johnson"
  },
  "status": "pending",
  "total_amount": "450.00",
  "items": [
    {
      "id": 102,
      "menu_item": {
        "id": 5,
        "name": "Iced Coffee",
        "category": "Beverages"
      },
      "quantity": 2,
      "notes": "No ice",
      "modifiers": [
        {
          "id": 1,
          "name": "Extra Shot"
        }
      ]
    }
  ],
  "created_at": "2024-01-15T10:30:00Z"
}
```

---

## Channel Authorization

### Kitchen Channel

**Channel Name:** `kitchen-channel`

**Authorization:** Private channel - Only authenticated users with specific roles can access

**Authorized Roles:**
- Admin
- Manager

**Location:** `routes/channels.php`

```php
Broadcast::channel('kitchen-channel', function ($user) {
    return in_array($user->role->name, ['Admin', 'Manager']);
});
```

### Modifying Authorization

To allow other roles (e.g., Kitchen Staff):

```php
Broadcast::channel('kitchen-channel', function ($user) {
    return in_array($user->role->name, ['Admin', 'Manager', 'Kitchen Staff']);
});
```

---

## Frontend Integration

### 1. Install Laravel Echo and Pusher

```bash
npm install --save laravel-echo pusher-js
```

### 2. Configure Laravel Echo

**resources/js/echo.js:**
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    wsHost: import.meta.env.VITE_PUSHER_HOST ?? `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
        },
    },
});
```

**For Laravel Reverb:**
```javascript
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

### 3. Environment Variables

**Add to .env:**
```env
VITE_BROADCAST_DRIVER="${BROADCAST_CONNECTION}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

### 4. React Kitchen Display Component

```jsx
import { useEffect, useState } from 'react';
import Echo from '@/echo';

export default function KitchenDisplay() {
    const [orders, setOrders] = useState([]);
    const [notification, setNotification] = useState(null);

    useEffect(() => {
        // Subscribe to kitchen channel
        const channel = Echo.private('kitchen-channel');

        // Listen for new orders
        channel.listen('.order.placed', (event) => {
            console.log('New order received:', event);
            
            // Add new order to the top of the list
            setOrders(prevOrders => [event, ...prevOrders]);
            
            // Show notification
            setNotification(`New order #${event.order_id} from Table ${event.table.number}`);
            
            // Play sound (optional)
            playNotificationSound();
            
            // Clear notification after 5 seconds
            setTimeout(() => setNotification(null), 5000);
        });

        // Cleanup on unmount
        return () => {
            channel.stopListening('.order.placed');
            Echo.leave('kitchen-channel');
        };
    }, []);

    const playNotificationSound = () => {
        const audio = new Audio('/sounds/notification.mp3');
        audio.play().catch(e => console.error('Error playing sound:', e));
    };

    const markAsPreparing = async (orderId) => {
        try {
            await fetch(`/api/orders/${orderId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    status: 'preparing',
                    order_items: [] // Required by validation but empty for status update
                }),
                credentials: 'include',
            });
            
            // Update local state
            setOrders(orders.filter(o => o.order_id !== orderId));
        } catch (error) {
            console.error('Error updating order:', error);
        }
    };

    return (
        <div className="kitchen-display">
            <h1>Kitchen Display System</h1>
            
            {notification && (
                <div className="notification">
                    {notification}
                </div>
            )}
            
            <div className="orders-grid">
                {orders.map((order) => (
                    <div key={order.order_id} className="order-card">
                        <div className="order-header">
                            <h2>Order #{order.order_id}</h2>
                            <span className="table">Table {order.table.number}</span>
                            <span className="time">{new Date(order.created_at).toLocaleTimeString()}</span>
                        </div>
                        
                        <div className="order-items">
                            {order.items.map((item, index) => (
                                <div key={index} className="item">
                                    <div className="item-header">
                                        <span className="quantity">{item.quantity}x</span>
                                        <span className="name">{item.menu_item.name}</span>
                                        <span className="category">{item.menu_item.category}</span>
                                    </div>
                                    
                                    {item.modifiers.length > 0 && (
                                        <div className="modifiers">
                                            {item.modifiers.map(mod => (
                                                <span key={mod.id} className="modifier">+ {mod.name}</span>
                                            ))}
                                        </div>
                                    )}
                                    
                                    {item.notes && (
                                        <div className="notes">
                                            <strong>Note:</strong> {item.notes}
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>
                        
                        <div className="order-footer">
                            <span className="waiter">Waiter: {order.waiter.name}</span>
                            <button 
                                onClick={() => markAsPreparing(order.order_id)}
                                className="btn-accept"
                            >
                                Start Preparing
                            </button>
                        </div>
                    </div>
                ))}
                
                {orders.length === 0 && (
                    <div className="no-orders">
                        <p>No pending orders</p>
                        <p className="text-muted">Waiting for new orders...</p>
                    </div>
                )}
            </div>
        </div>
    );
}
```

### 5. Vue Kitchen Display Component

```vue
<template>
  <div class="kitchen-display">
    <h1>Kitchen Display System</h1>
    
    <div v-if="notification" class="notification">
      {{ notification }}
    </div>
    
    <div class="orders-grid">
      <div v-for="order in orders" :key="order.order_id" class="order-card">
        <div class="order-header">
          <h2>Order #{{ order.order_id }}</h2>
          <span class="table">Table {{ order.table.number }}</span>
          <span class="time">{{ formatTime(order.created_at) }}</span>
        </div>
        
        <div class="order-items">
          <div v-for="(item, index) in order.items" :key="index" class="item">
            <div class="item-header">
              <span class="quantity">{{ item.quantity }}x</span>
              <span class="name">{{ item.menu_item.name }}</span>
              <span class="category">{{ item.menu_item.category }}</span>
            </div>
            
            <div v-if="item.modifiers.length > 0" class="modifiers">
              <span v-for="mod in item.modifiers" :key="mod.id" class="modifier">
                + {{ mod.name }}
              </span>
            </div>
            
            <div v-if="item.notes" class="notes">
              <strong>Note:</strong> {{ item.notes }}
            </div>
          </div>
        </div>
        
        <div class="order-footer">
          <span class="waiter">Waiter: {{ order.waiter.name }}</span>
          <button @click="markAsPreparing(order.order_id)" class="btn-accept">
            Start Preparing
          </button>
        </div>
      </div>
      
      <div v-if="orders.length === 0" class="no-orders">
        <p>No pending orders</p>
        <p class="text-muted">Waiting for new orders...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import Echo from '@/echo';

const orders = ref([]);
const notification = ref(null);
let channel = null;

onMounted(() => {
  // Subscribe to kitchen channel
  channel = Echo.private('kitchen-channel');
  
  // Listen for new orders
  channel.listen('.order.placed', (event) => {
    console.log('New order received:', event);
    
    // Add new order to the top of the list
    orders.value = [event, ...orders.value];
    
    // Show notification
    notification.value = `New order #${event.order_id} from Table ${event.table.number}`;
    
    // Play sound
    playNotificationSound();
    
    // Clear notification after 5 seconds
    setTimeout(() => notification.value = null, 5000);
  });
});

onUnmounted(() => {
  if (channel) {
    channel.stopListening('.order.placed');
    Echo.leave('kitchen-channel');
  }
});

const formatTime = (timestamp) => {
  return new Date(timestamp).toLocaleTimeString();
};

const playNotificationSound = () => {
  const audio = new Audio('/sounds/notification.mp3');
  audio.play().catch(e => console.error('Error playing sound:', e));
};

const markAsPreparing = async (orderId) => {
  try {
    const response = await fetch(`/api/orders/${orderId}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({
        status: 'preparing',
        order_items: []
      }),
      credentials: 'include',
    });
    
    if (response.ok) {
      // Remove order from list
      orders.value = orders.value.filter(o => o.order_id !== orderId);
    }
  } catch (error) {
    console.error('Error updating order:', error);
  }
};
</script>
```

---

## Testing

### 1. Test Channel Authorization

```bash
# Start your application
php artisan serve

# Start broadcasting server (if using Reverb)
php artisan reverb:start

# Start queue worker
php artisan queue:work
```

### 2. Test Event Broadcasting

**Using Tinker:**
```bash
php artisan tinker
```

```php
// Create a test order
$order = \App\Models\Order::with(['table', 'user.role', 'orderItems.menuItem.category', 'orderItems.modifiers'])->first();

// Broadcast the event
broadcast(new \App\Events\NewOrderPlaced($order));
```

### 3. Monitor Broadcasting

**Check logs:**
```bash
tail -f storage/logs/laravel.log
```

**Reverb debug mode:**
```bash
php artisan reverb:start --debug
```

### 4. Frontend Testing

**Open browser console:**
```javascript
// Check if Echo is initialized
console.log(window.Echo);

// Manual channel subscription
window.Echo.private('kitchen-channel')
    .listen('.order.placed', (e) => {
        console.log('Order received:', e);
    });
```

---

## Troubleshooting

### Issue: Events not broadcasting

**Solutions:**
1. Check `.env` file has correct broadcasting driver
2. Ensure queue worker is running: `php artisan queue:work`
3. Verify broadcasting server is running (Reverb/Soketi)
4. Check `config/broadcasting.php` is properly configured
5. Clear config cache: `php artisan config:clear`

### Issue: Frontend not receiving events

**Solutions:**
1. Check browser console for WebSocket connection errors
2. Verify CSRF token is included in Echo configuration
3. Ensure user is logged in and authorized for the channel
4. Check network tab for `/broadcasting/auth` requests
5. Verify environment variables are loaded in Vite: `VITE_*`

### Issue: "403 Forbidden" on channel subscription

**Solutions:**
1. Verify user has correct role (Admin/Manager)
2. Check `routes/channels.php` authorization logic
3. Ensure user relationship with role is loaded
4. Verify session is valid

### Issue: Queue not processing

**Solutions:**
1. Check queue connection in `.env`: `QUEUE_CONNECTION`
2. Run migrations: `php artisan queue:table && php artisan migrate`
3. Start queue worker: `php artisan queue:work`
4. Check failed jobs: `php artisan queue:failed`
5. Retry failed jobs: `php artisan queue:retry all`

---

## Production Deployment

### 1. Use Supervisor for Queue Workers

**Create supervisor config:** `/etc/supervisor/conf.d/laravel-worker.conf`
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/worker.log
stopwaitsecs=3600
```

### 2. Use PM2 for Reverb Server

```bash
npm install -g pm2

# Start Reverb with PM2
pm2 start --name reverb "php artisan reverb:start"

# Save PM2 configuration
pm2 save

# Setup PM2 to start on boot
pm2 startup
```

### 3. Use SSL/TLS

Update `.env` for production:
```env
REVERB_SCHEME=https
PUSHER_SCHEME=https
```

### 4. Optimize for Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## Security Considerations

1. **Private Channels:** Always use private channels for sensitive data
2. **Authorization:** Implement proper role-based authorization in `channels.php`
3. **CSRF Protection:** Ensure CSRF token is sent with broadcasting auth requests
4. **SSL/TLS:** Use encrypted connections in production
5. **Rate Limiting:** Consider rate limiting broadcasting authentication endpoint

---

## Performance Optimization

1. **Use Redis Queue:** Better performance than database queue
2. **Queue Workers:** Run multiple queue workers for high traffic
3. **Broadcasting Queue:** Events are queued by default with `ShouldBroadcast`
4. **Load Balancing:** Use multiple Reverb/Soketi servers with load balancer
5. **Caching:** Cache authorized channel checks if needed

---

## Related Documentation

- [Order Management API Documentation](./ORDER_API.md)
- [API Authentication Documentation](./API_AUTHENTICATION.md)
- [Laravel Broadcasting Documentation](https://laravel.com/docs/11.x/broadcasting)
- [Laravel Echo Documentation](https://laravel.com/docs/11.x/broadcasting#client-side-installation)
- [Laravel Reverb Documentation](https://laravel.com/docs/11.x/reverb)

---

## Summary

The KDS system provides:
- ✅ Real-time order notifications to kitchen staff
- ✅ Secure private channel with role-based authorization
- ✅ Comprehensive order details for efficient preparation
- ✅ Multiple broadcasting driver options
- ✅ Production-ready configuration
- ✅ Frontend integration examples for React and Vue

Kitchen staff can now see orders instantly as they are placed, improving kitchen efficiency and reducing order preparation time!
