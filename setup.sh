#!/bin/bash

# Falls Origin Coffee - Quick Setup Script
# This script helps you get started with the development environment

set -e

echo "🚀 Falls Origin Coffee - Setup Script"
echo "======================================"
echo ""

# Check if Node.js is installed
if ! command -v node &> /dev/null; then
    echo "❌ Node.js is not installed. Please install Node.js 20+ first."
    exit 1
fi

echo "✅ Node.js $(node --version) detected"
echo ""

# Install frontend dependencies
echo "📦 Installing frontend dependencies..."
npm install

# Install backend dependencies
echo "📦 Installing backend dependencies..."
cd server && npm install && cd ..

echo ""
echo "✅ Dependencies installed successfully!"
echo ""

# Check for .env file
if [ ! -f "server/.env" ]; then
    echo "⚠️  No .env file found in server/"
    echo "📝 Creating .env from .env.example..."
    cp server/.env.example server/.env
    echo ""
    echo "⚠️  IMPORTANT: You must configure the following in server/.env:"
    echo "   1. DATABASE_URL - Your PostgreSQL connection string"
    echo "   2. STRIPE_SECRET_KEY - Your Stripe secret key"
    echo "   3. STRIPE_PUBLISHABLE_KEY - Your Stripe publishable key"
    echo "   4. STRIPE_WEBHOOK_SECRET - Your Stripe webhook secret"
    echo "   5. EMAIL_PROVIDER - Choose: sendgrid, mailgun, or ses"
    echo "   6. EMAIL API keys for your chosen provider"
    echo "   7. ADMIN_PASSWORD_HASH - Generate with: node -e \"console.log(require('bcrypt').hashSync('YOUR_PASSWORD', 10))\""
    echo "   8. GEOCODING_PROVIDER - Choose: google or opencage"
    echo "   9. Geocoding API key for your chosen provider"
    echo ""
else
    echo "✅ .env file already exists"
fi

echo ""
echo "📋 Next Steps:"
echo "=============="
echo ""
echo "1. Configure environment variables in server/.env"
echo ""
echo "2. Set up your database:"
echo "   - Create a PostgreSQL database"
echo "   - Run migrations: psql YOUR_DATABASE_URL < server/migrations/001_initial_schema.sql"
echo ""
echo "3. Start the development servers:"
echo "   - Backend:  cd server && npm run dev"
echo "   - Frontend: npm run dev"
echo ""
echo "4. Visit http://localhost:5173 to see the app"
echo ""
echo "5. For deployment to Cloud Run, see DEPLOYMENT.md"
echo ""
echo "✨ Setup complete! Happy coding!"
