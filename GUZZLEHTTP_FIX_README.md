# GuzzleHttp Promises Deprecation Warnings Fix

## Problem
When using PHP 8.4 with GuzzleHttp Promises v1.5.3, you may encounter deprecation warnings like:

```
Deprecated: GuzzleHttp\Promise\queue(): Implicitly marking parameter $assign as nullable is deprecated, the explicit nullable type must be used instead in vendor/guzzlehttp/promises/src/functions.php on line 24
```

These warnings occur because:
1. The older GuzzleHttp Promises library (v1.5.3) doesn't explicitly mark nullable parameters
2. PHP 8.1+ requires explicit nullable type declarations
3. The Firebase PHP SDK v5.26.5 constrains GuzzleHttp Promises to v1.x, preventing upgrade to v2.x

## Solution Implemented

We've added a custom error handler that specifically suppresses these deprecation warnings while preserving all other error reporting.

### Files Modified

1. **`/admin/config/config.php`** - Added custom error handler
2. **`/autoload.php`** - Added custom error handler
3. **`/admin/autoload.php`** - Added custom error handler and fixed composer paths

### How It Works

The custom error handler:
- Only suppresses `E_DEPRECATED` errors
- Only targets files in `vendor/guzzlehttp/promises` directory
- Only filters warnings about "Implicitly marking parameter as nullable"
- Allows all other deprecation warnings to be displayed normally

### Code Added

```php
// Custom error handler to suppress GuzzleHttp Promises deprecation warnings
set_error_handler(function ($severity, $message, $file, $line) {
    // Suppress deprecation warnings from GuzzleHttp vendor directory
    if ($severity === E_DEPRECATED &&
        (strpos($file, 'vendor/guzzlehttp/promises') !== false ||
         strpos($file, 'vendor\\guzzlehttp\\promises') !== false) &&
        (strpos($message, 'Implicitly marking parameter') !== false &&
         strpos($message, 'as nullable is deprecated') !== false)) {
        return true; // Don't execute the internal error handler
    }

    // For all other errors, use the default error handler
    return false;
});
```

## Functions Affected

This fix suppresses warnings from:
- `GuzzleHttp\Promise\queue()` - nullable `$assign` parameter
- `GuzzleHttp\Promise\each()` - nullable `$onFulfilled` and `$onRejected` parameters
- `GuzzleHttp\Promise\each_limit()` - nullable `$onFulfilled` and `$onRejected` parameters
- `GuzzleHttp\Promise\each_limit_all()` - nullable `$onFulfilled` parameter

## Testing

Run the test file to verify the fix is working:
```bash
php test_error_handler.php
```

## Future Upgrade Path

When you're ready to upgrade:
1. Update Firebase PHP SDK to v7+ (requires PHP 8.1-8.3)
2. This will allow GuzzleHttp Promises v2.x which fixes the nullable parameter issues
3. Remove the custom error handlers once upgraded

## Notes

- This is a safe, non-intrusive fix that only suppresses specific warnings
- Your application functionality remains unchanged
- All other error reporting continues to work normally
- The warnings were cosmetic and didn't affect functionality
