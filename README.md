
# ðŸ›’ Quick Kart - Simple E-Commerce Website

Quick Kart is a simple, responsive, mobile-first e-commerce web application built using core web technologies without any external frameworks. It's a beginner-friendly, cleanly structured platform for understanding the basics of full-stack e-commerce web development.

## ðŸš€ Features

- ðŸ” User Authentication (Login/Register)
- ðŸ“¦ Product Listings & Detail Pages
- ðŸ›’ Shopping Cart & Checkout Flow
- ðŸ“‘ Order History & Profile Page
- ðŸ› ï¸ Admin Panel for Product Management
- âš¡ AJAX-powered interactivity (no page reloads)
- ðŸ“± Mobile-First UI using Tailwind CSS
- ðŸ’¾ MySQL database integration
- ðŸ’¡ Clean PHP & modular folder structure

---

## ðŸ§° Technologies Used

| Frontend          | Backend        | Database     | Others         |
|-------------------|----------------|--------------|----------------|
| HTML, TailwindCSS | PHP (No Framework) | MySQL        | AJAX, Font Awesome |

---

## ðŸ“‚ Folder Structure

```plaintext
quick_kart/
â”œâ”€â”€ admin/               # Admin dashboard files
â”œâ”€â”€ common/              # Shared includes/configs
â”œâ”€â”€ cart.php             # Shopping cart
â”œâ”€â”€ checkout.php         # Checkout page
â”œâ”€â”€ index.php            # Homepage
â”œâ”€â”€ install.php          # DB installer/setup
â”œâ”€â”€ login.php            # Login & Registration
â”œâ”€â”€ order.php            # Order processing
â”œâ”€â”€ product.php          # Product listings
â”œâ”€â”€ product_detail.php   # Single product view
â”œâ”€â”€ profile.php          # User profile
â”œâ”€â”€ LICENSE              # Apache License 2.0
â””â”€â”€ README.md            # Project documentation
```

---

## âš™ï¸ Installation

1. **Clone the repo**
   ```bash
   git clone https://github.com/rahultechser/quick_kart.git
   cd quick_kart
   ```

2. **Setup Database**
   - Import the SQL dump (`install.php` may help set this up).
   - Create a MySQL user and database.

3. **Configure Database**
   - Update database credentials in `common/db_config.php` (or equivalent config file).

4. **Run in Localhost**
   - Place the project in `htdocs/` (XAMPP) or your web server's root.
   - Open `http://localhost/quick_kart` in your browser.

---

## ðŸ›¡ License

This project is licensed under the **Apache 2.0 License** - see the [LICENSE](./LICENSE) file for details.

---

## ðŸ™Œ Acknowledgments

- Special thanks to the open-source community.
- Built with ðŸ’» by [@rahultechser](https://github.com/rahultechser)

---

## ðŸ“§ Contact

For suggestions or queries, feel free to reach out via GitHub issues or email.
