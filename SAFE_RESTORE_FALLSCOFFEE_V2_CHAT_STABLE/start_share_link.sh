#!/bin/bash
# Check if ngrok is installed via Homebrew
if ! command -v ngrok &> /dev/null; then
    echo "ngrok not found. Installing via Homebrew..."
    brew install ngrok/ngrok/ngrok
fi

echo "Starting ngrok tunnel for port 3000..."
echo "Your public URL will appear below:"
echo "-----------------------------------"
ngrok http 3000
