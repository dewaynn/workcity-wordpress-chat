### Core Features

- **Custom Post Type**: `chat_session` to store chat logs
- **Shortcode Support**: Easily embed chat widget anywhere via `[workcity_chat_widget]`
- **AJAX-Based Real-Time Messaging**: No page reloads
- **Role-Based Access**: Buyer-to-Designer, Buyer-to-Merchant, Buyer-to-Agent
- **WooCommerce Integration**: Product context shown inside chat
- **Read/Unread Messages**: Includes timestamps

### Bonus Features

- Typing indicators  
- Online status detection   
- Dark/Light mode toggle (with localStorage)  
- Responsive, modern interface (mobile-friendly)

---

## Installation & Setup

### 1. Clone Repository

```bash
git clone https://github.com/yourusername/workcity-wordpress-chat.git
```

### 2. download a local server to your system(XAMP)

On the local server click on Apache and MYSQL

Copy the plugin folder to your WordPress installation:

```
/wp-content/plugins/workcity-wordpress-chat/
```

### 3. Activate the Plugin

Go to `Dashboard > Plugins` and activate **Workcity WordPress Chat**.

### 4. Use Shortcode

Add the shortcode `[workcity_chat_widget]` to any **Page**, **Post**, or **Template**.

for woocommerce test product shortcode [workcity_chat_widget product_id=123]

Example:
```php
echo do_shortcode('[workcity_chat_widget]');
```

---

## Requirements

- PHP 7.4 or higher  
- WordPress 8.6  
- WooCommerce 5.0  
- jQuery (comes with WordPress)  
- Authenticated users to access chat  

---


```

---

## Technologies Used

- WordPress Plugin API
- PHP, HTML5, CSS3
- JavaScript (jQuery)
- WooCommerce Integration
- AJAX (for real-time messaging)
- WordPress REST API
- LocalStorage (for theme persistence)


---

## Challenges & Solutions

| Challenge                                 | Solution Implemented                                      |
|------------------------------------------|------------------------------------------------------------|
| Real-time updates without WebSockets     | Used AJAX with polling intervals                          |
| Role-based filtering for chat users      | Dynamically populated user list on role change            |
| Dark/Light mode toggle                   | Controlled via class toggle and `localStorage` persistence |
| WooCommerce product context              | Passed product ID via global and loaded via `wc_get_product()` |
| Typing indicators and online presence    | AJAX polling with backend flags                           |

---

## Future Improvements

- WebSocket integration for true real-time chat  
- Push notifications (via Firebase or Pusher)  
- Admin dashboard for chat monitoring  
- Searchable message history  
- Reactions (etc.)  
- Better file/image preview in chat  

---


---

## License

This project is provided for demonstration purposes for the Workcity assessment.  
It is not licensed for commercial use unless explicitly stated.

---

## Author

**Godwin Aifuwa**   
GitHub: [https://github.com/dewaynn]

---

