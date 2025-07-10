# 🛒 Quick Kart - Simple E-Commerce Website

Quick Kart is a simple, responsive, mobile-first e-commerce web application built using core web technologies without any external frameworks. It's a beginner-friendly, cleanly structured platform for understanding the basics of full-stack e-commerce web development.

## 🚀 Features

- 🔐 User Authentication (Login/Register)
- 📦 Product Listings & Detail Pages
- 🛒 Shopping Cart & Checkout Flow
- 📑 Order History & Profile Page
- 🛠️ Admin Panel for Product Management
- ⚡ AJAX-powered interactivity (no page reloads)
- 📱 Mobile-First UI using Tailwind CSS
- 💾 MySQL database integration
- 💡 Clean PHP & modular folder structure

---

## 🧰 Technologies Used

| Frontend          | Backend        | Database     | Others         |
|-------------------|----------------|--------------|----------------|
| HTML, TailwindCSS | PHP (No Framework) | MySQL        | AJAX, Font Awesome |

---

## 📂 Folder Structure

```plaintext
quick_kart/
├── admin/                # Admin dashboard files
├── common/               # Shared includes/configs
├── cart.php              # Shopping cart
├── checkout.php          # Checkout page
├── index.php             # Homepage
├── install.php           # DB installer/setup
├── login.php             # Login & Registration
├── order.php             # Order processing
├── product.php           # Product listings
├── product_detail.php    # Single product view
├── profile.php           # User profile
├── LICENSE               # Apache License 2.0
└── README.md             # Project documentation
⚙️ Installation

1. Clone the repo

git clone https://github.com/rahultechser/quick_kart.git
cd quick_kart


2. Setup Database

Import the SQL dump (install.php may help set this up).

Create a MySQL user and database.



3. Configure Database

Update database credentials in common/db_config.php (or equivalent config file).



4. Run in Localhost

Place the project in htdocs/ (XAMPP) or your web server's root.

Open http://localhost/quick_kart in your browser.





---

🛡 License

This project is licensed under the Apache 2.0 License - see the LICENSE file for details.


---

🙌 Acknowledgments

Special thanks to the open-source community.

Built with 💻 by @rahultechser



---

📧 Contact

For suggestions or queries, feel free to reach out via GitHub issues or email.
