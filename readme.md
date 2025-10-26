# LankaPrice

A comprehensive classified ads and price comparison platform for Sri Lanka, built with Laravel and Bootstrap.

## Overview

LankaPrice is a powerful classified ads web application designed specifically for the Sri Lankan market. It enables users to buy, sell, and compare prices on a wide range of products and services across the country.

## Features

### Core Functionality
- 📝 **Post Classified Ads** - List products and services for sale
- 🔍 **Advanced Search & Filters** - Find exactly what you're looking for
- 📍 **Location-Based Listings** - Search by district and city
- 📈 **Price Comparison** - Compare prices across multiple sellers
- 📸 **Image Galleries** - Multiple photos per listing
- ⭐ **Ratings & Reviews** - Build trust through user feedback
- 🔔 **Email Notifications** - Stay updated on new listings
- 📱 **Responsive Design** - Works seamlessly on all devices

### Categories
- Electronics & Computers
- Vehicles (Cars, Motorcycles, Trucks)
- Property (Houses, Land, Commercial)
- Jobs & Services
- Home & Garden
- Fashion & Lifestyle
- And many more...

## Tech Stack

- **Framework**: Laravel (PHP)
- **Frontend**: Bootstrap, jQuery
- **Database**: MySQL / MariaDB
- **Cache**: Redis (optional)
- **Search**: Elasticsearch (optional for advanced search)

## Requirements

- PHP >= 7.4
- MySQL >= 5.7 or MariaDB >= 10.2
- Composer
- Apache or Nginx web server

## Installation

```bash
# Clone the repository
git clone https://github.com/sakibian/lankaprice.git
cd lankaprice

# Install dependencies
composer install
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database in .env
# Then run migrations
php artisan migrate --seed

# Build frontend assets
npm run dev

# Start the development server
php artisan serve
```

## Configuration

Update your `.env` file with the following settings:

```env
APP_NAME="LankaPrice"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lankaprice
DB_USERNAME=your_username
DB_PASSWORD=your_password

MAIL_MAILER=smtp
MAIL_HOST=your_mail_host
MAIL_PORT=587
```

## Features Roadmap

- [ ] Mobile applications (iOS & Android)
- [ ] Advanced analytics dashboard
- [ ] Payment gateway integration
- [ ] SMS notifications
- [ ] Multi-language support (Sinhala, Tamil, English)
- [ ] Featured listings
- [ ] Membership plans

## Documentation

Detailed documentation is available in the `documentation/` folder, including:
- Installation guide
- User manual
- Admin panel guide
- API documentation
- Customization guide

## License

This software is furnished under a license and may be used and copied only in accordance with the terms of such license.

If purchased from CodeCanyon, please read the full license from here: https://codecanyon.net/licenses/standard

## Support

For support and questions, please refer to the documentation or contact the support team.

## Contributing

Contributions, issues, and feature requests are welcome!

---

**Built with ❤️ for the Sri Lankan community**
