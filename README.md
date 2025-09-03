# Retail Management System

A modular Laravel application for retail management with POS, E-commerce, and Loyalty modules.

## Features

- **Modular Architecture**: Independent, optional modules
- **Point of Sale (POS)**: Complete POS system with register management
- **E-commerce**: Product catalog, order management, inventory
- **Loyalty Program**: Points system with tier management
- **Event-Driven Communication**: Modules communicate via events
- **Database Independence**: No foreign keys between modules

## Installation

```bash
git clone <repository>
cd retail-system
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

## Module Management

```bash
# Check module status
php artisan module:status list

# Check module health
php artisan module:status health

# Install new module
php artisan module:install ModuleName
```

## API Endpoints

### System
- GET `/api/system/modules` - List available modules
- GET `/api/system/health` - Module health check
- GET `/api/system/metrics` - System metrics

### POS Module
- POST `/api/pos/sales` - Create sale
- GET `/api/pos/sales/{id}` - Get sale details
- GET `/api/pos/registers` - List registers
- POST `/api/pos/registers/{id}/open` - Open register

### E-commerce Module
- GET `/api/ecommerce/products` - List products
- POST `/api/ecommerce/products` - Create product
- GET `/api/ecommerce/orders` - List orders
- POST `/api/ecommerce/orders` - Create order

### Loyalty Module
- GET `/api/loyalty/customers/{id}/points` - Get customer points
- POST `/api/loyalty/customers/{id}/redeem` - Redeem points
- GET `/api/loyalty/tiers` - List loyalty tiers

## Testing

```bash
# Run all tests
php artisan test

# Run module-specific tests
php artisan test --filter=POS
php artisan test --filter=Ecommerce
php artisan test --filter=Loyalty
```

## Configuration

### Environment Variables
```
MODULE_ECOMMERCE_ENABLED=true
MODULE_POS_ENABLED=true
MODULE_LOYALTY_ENABLED=true
```

### Disabling Modules
To disable a module:
1. Set environment variable to `false`
2. Or remove the module directory entirely
3. Application will continue without errors

## Architecture

The system uses:
- Event-driven communication between modules
- Null object pattern for missing modules
- Polymorphic relationships for cross-module references
- Safe service interfaces with fallbacks
- Independent database schemas per module

## Adding New Modules

1. Use the module generator: `php artisan module:install ModuleName`
2. Implement the ModuleServiceInterface
3. Create migrations in the module directory
4. Register event listeners in the service provider
5. Add routes to the module routes file