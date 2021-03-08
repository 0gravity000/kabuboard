# 0gravity0.com/public_html/kabuboard/public/minutly_stocks_check.sh
cd 0gravity0.com/public_html/kabuboard
/usr/bin/php7.3 artisan schedule:run >> /dev/null 2>&1
cd