
#ifndef PHP_PHOOK_H
#define PHP_PHOOK_H

extern zend_module_entry phook_module_entry;
#define phpext_phook_ptr &phook_module_entry

ZEND_BEGIN_MODULE_GLOBALS(phook)
    HashTable *observer_class_lookup;
    HashTable *observer_function_lookup;
    HashTable *observer_aggregates;
    int validate_hook_functions;
    char *conflicts;
    int disabled; // module disabled? (eg due to conflicting extension loaded)
    int allow_stack_extension;
    int attr_hooks_enabled; // attribute hooking enabled?
    int display_warnings;
    char *pre_handler_function_fqn;
    char *post_handler_function_fqn;
ZEND_END_MODULE_GLOBALS(phook)

ZEND_EXTERN_MODULE_GLOBALS(phook)

#define PHOOK_G(v) ZEND_MODULE_GLOBALS_ACCESSOR(phook, v)

#define PHP_PHOOK_VERSION "1.0.0"

#if defined(ZTS) && defined(COMPILE_DL_PHOOK)
ZEND_TSRMLS_CACHE_EXTERN()
#endif

#endif /* PHP_PHOOK_H */
