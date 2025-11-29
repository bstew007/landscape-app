#!/bin/bash

echo "Recalculating all estimate totals..."
php artisan estimates:recalculate
echo "Done."
