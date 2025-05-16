#ifndef PHOOK_COMPAT_H
#define PHOOK_COMPAT_H

#include "php.h"

/* 
 * Compatibility layer for PHP versions that don't have certain headers 
 * PHP 8.3 already includes zend_hrtime.h and zend_atomic.h
 * Only define these for PHP < 8.3
 */
#if PHP_VERSION_ID < 80300
/* Dummy implementations for zend_hrtime.h */
#ifndef ZEND_HRTIME_H
#define ZEND_HRTIME_H

typedef uint64_t zend_hrtime_t;

static inline zend_hrtime_t zend_hrtime(void) {
    return 0;
}
#endif /* ZEND_HRTIME_H */

/* Dummy implementations for zend_atomic.h */
#ifndef ZEND_ATOMIC_H
#define ZEND_ATOMIC_H

typedef struct _zend_atomic {
    volatile int32_t value;
} zend_atomic_t;

static inline int32_t zend_atomic_fetch_add(zend_atomic_t *atomic, int32_t value) {
    int32_t old_value = atomic->value;
    atomic->value += value;
    return old_value;
}

static inline void zend_atomic_store(zend_atomic_t *atomic, int32_t value) {
    atomic->value = value;
}

static inline int32_t zend_atomic_load(zend_atomic_t *atomic) {
    return atomic->value;
}
#endif /* ZEND_ATOMIC_H */
#endif /* PHP_VERSION_ID < 80300 */

/* 
 * Observer compatibility for PHP 8.4
 * In PHP 8.4, we need to explicitly register handlers using:
 * - zend_observer_add_begin_handler
 * - zend_observer_add_end_handler
 * 
 * For PHP < 8.4, we need to ensure the global handler variables are available
 * but we don't want to redefine them if they're already defined in zend_observer.h
 */
#if PHP_VERSION_ID < 80400
/* 
 * We don't define any variables here since they might conflict with existing definitions.
 * The observer_fcall_init function in phook_observer.c handles the registration
 * of handlers appropriately for each PHP version.
 */
#endif /* PHP_VERSION_ID < 80400 */

#endif /* PHOOK_COMPAT_H */
