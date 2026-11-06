#!/bin/sh
cd "$(dirname "$0")/../.." || exit 1

echo ""
echo "  ============================================"
echo "   ارتقاء برنامه شهریه به نسخه جدید"
echo "  ============================================"
echo ""

if ! command -v php >/dev/null 2>&1; then
    echo "  PHP پیدا نشد؛ از پشتیبان بخواهید کمک کند."
    read -r _
    exit 1
fi

php artisan upgrade:from-v1

echo ""
echo "  ----- پایان -----"
read -r _
