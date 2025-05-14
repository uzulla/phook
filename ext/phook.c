
#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "ext/standard/info.h"
#include "php_phook.h"
#include "phook_arginfo.h"
#include "phook_observer.h"
#include "stdlib.h"
#include "string.h"
#include "zend_attributes.h"
#include "zend_closures.h"

static int check_conflict(HashTable *registry, const char *extension_name) {
    if (!extension_name || !*extension_name) {
        return 0;
    }
    zend_module_entry *module_entry;
    ZEND_HASH_FOREACH_PTR(registry, module_entry) {
        if (strcmp(module_entry->name, extension_name) == 0) {
            php_error_docref(NULL, E_NOTICE,
                             "Conflicting extension found (%s), Phook "
                             "extension will be disabled",
                             extension_name);
            return 1;
        }
    }
    ZEND_HASH_FOREACH_END();
    return 0;
}

static void check_conflicts() {
    int conflict_found = 0;
    char *input = PHOOK_G(conflicts);

    if (!input || !*input) {
        return;
    }

    HashTable *registry = &module_registry;
    const char *s = NULL, *e = input;
    /** @see https://github.com/php/php-src/blob/php-8.2.9/Zend/zend_API.c#L3324
     */
    while (*e) {
        switch (*e) {
        case ' ':
        case ',':
            if (s) {
                size_t len = e - s;
                char *result = (char *)malloc((len + 1) * sizeof(char));
                strncpy(result, s, len);
                result[len] = '\0'; // null terminate
                if (check_conflict(registry, result)) {
                    conflict_found = 1;
                }
                s = NULL;
            }
            break;
        default:
            if (!s) {
                s = e;
            }
            break;
        }
        e++;
    }
    if (check_conflict(registry, s)) {
        conflict_found = 1;
    }

    PHOOK_G(disabled) = conflict_found;
}

ZEND_DECLARE_MODULE_GLOBALS(phook)

PHP_INI_BEGIN()
// conflicting extensions. a comma-separated list, eg "ext1,ext2"
STD_PHP_INI_ENTRY("phook.conflicts", "", PHP_INI_ALL, OnUpdateString,
                  conflicts, zend_phook_globals, phook_globals)
STD_PHP_INI_ENTRY_EX("phook.validate_hook_functions", "On", PHP_INI_ALL,
                     OnUpdateBool, validate_hook_functions,
                     zend_phook_globals, phook_globals,
                     zend_ini_boolean_displayer_cb)
STD_PHP_INI_ENTRY_EX("phook.allow_stack_extension", "Off", PHP_INI_ALL,
                     OnUpdateBool, allow_stack_extension,
                     zend_phook_globals, phook_globals,
                     zend_ini_boolean_displayer_cb)
STD_PHP_INI_ENTRY_EX("phook.attr_hooks_enabled", "Off", PHP_INI_ALL,
                     OnUpdateBool, attr_hooks_enabled,
                     zend_phook_globals, phook_globals,
                     zend_ini_boolean_displayer_cb)
STD_PHP_INI_ENTRY_EX("phook.display_warnings", "Off", PHP_INI_ALL,
                     OnUpdateBool, display_warnings, zend_phook_globals,
                     phook_globals, zend_ini_boolean_displayer_cb)
STD_PHP_INI_ENTRY("phook.attr_pre_handler_function",
                  "Phook\\WithSpanHandler::pre",
                  PHP_INI_ALL, OnUpdateString, pre_handler_function_fqn,
                  zend_phook_globals, phook_globals)
STD_PHP_INI_ENTRY("phook.attr_post_handler_function",
                  "Phook\\WithSpanHandler::post",
                  PHP_INI_ALL, OnUpdateString, post_handler_function_fqn,
                  zend_phook_globals, phook_globals)
PHP_INI_END()

/**
 * Ensures a hook is a Closure or NULL
 * Returns the hook if valid, NULL otherwise
 */
static zval* ensure_closure_or_null(zval *hook, const char *hook_type, zend_string *class_name, zend_string *function_name) {
    if (hook == NULL) {
        return NULL;
    }
    
    if (Z_TYPE_P(hook) != IS_OBJECT || !instanceof_function(Z_OBJCE_P(hook), zend_ce_closure)) {
        if (PHOOK_G(display_warnings)) {
            php_error_docref(NULL, E_WARNING, 
                "Phook: %s hook must be a Closure or NULL, class=%s function=%s",
                hook_type,
                class_name ? ZSTR_VAL(class_name) : "null",
                ZSTR_VAL(function_name));
        }
        return NULL;
    }
    
    return hook;
}

/**
 * Validates a hook callback and returns NULL if invalid
 * Uses dynamic allocation for params to avoid out-of-bounds access
 */
static zval* validate_hook(zval *hook, const char *hook_type, zend_string *class_name, zend_string *function_name) {
    if (hook == NULL) {
        return NULL;
    }

    zend_fcall_info fci = empty_fcall_info;
    zend_fcall_info_cache fcc = empty_fcall_info_cache;
    
    if (zend_fcall_info_init(hook, 0, &fci, &fcc, NULL, NULL) == SUCCESS) {
        uint32_t param_count = fcc.function_handler->common.num_args;
        
        zval *params = emalloc(sizeof(zval) * param_count);
        
        for (uint32_t i = 0; i < param_count; i++) {
            ZVAL_NULL(&params[i]);
        }
        
        fci.param_count = param_count;
        fci.params = params;
        
        bool is_valid = is_valid_signature(fci, fcc);
        
        efree(params);
        
        if (!is_valid) {
            if (PHOOK_G(display_warnings)) {
                php_error_docref(NULL, E_WARNING, 
                    "Phook: %s hook invalid signature, class=%s function=%s",
                    hook_type,
                    class_name ? ZSTR_VAL(class_name) : "null",
                    ZSTR_VAL(function_name));
                /* Note: Using consistent format for all error messages with class and function context */
            }
            return NULL;
        }
    }
    
    return hook;
}

PHP_FUNCTION(Phook_hook) {
    zend_string *class_name;
    zend_string *function_name;
    zval *pre = NULL;
    zval *post = NULL;

    ZEND_PARSE_PARAMETERS_START(2, 4)
        Z_PARAM_STR_OR_NULL(class_name)
        Z_PARAM_STR(function_name)
        Z_PARAM_OPTIONAL
        Z_PARAM_ZVAL_OR_NULL(pre)
        Z_PARAM_ZVAL_OR_NULL(post)
    ZEND_PARSE_PARAMETERS_END();

    pre = ensure_closure_or_null(pre, "pre", class_name, function_name);
    post = ensure_closure_or_null(post, "post", class_name, function_name);

    pre = validate_hook(pre, "pre", class_name, function_name);
    post = validate_hook(post, "post", class_name, function_name);

    RETURN_BOOL(add_observer(class_name, function_name, pre, post));
}

PHP_RINIT_FUNCTION(phook) {
#if defined(ZTS) && defined(COMPILE_DL_PHOOK)
    ZEND_TSRMLS_CACHE_UPDATE();
#endif

    observer_globals_init();

    return SUCCESS;
}

PHP_RSHUTDOWN_FUNCTION(phook) {
    observer_globals_cleanup();

    return SUCCESS;
}

PHP_MINIT_FUNCTION(phook) {
#if defined(ZTS) && defined(COMPILE_DL_PHOOK)
    ZEND_TSRMLS_CACHE_UPDATE();
#endif

    REGISTER_INI_ENTRIES();

    check_conflicts();

    if (!PHOOK_G(disabled)) {
        phook_observer_init(INIT_FUNC_ARGS_PASSTHRU);
    }

    return SUCCESS;
}

PHP_MSHUTDOWN_FUNCTION(phook) {
    UNREGISTER_INI_ENTRIES();

    return SUCCESS;
}

PHP_MINFO_FUNCTION(phook) {
    php_info_print_table_start();
    php_info_print_table_row(2, "phook hooks",
                             PHOOK_G(disabled) ? "disabled (conflict)"
                                              : "enabled");
    php_info_print_table_row(2, "extension version", PHP_PHOOK_VERSION);
    php_info_print_table_end();
    DISPLAY_INI_ENTRIES();
}

PHP_GINIT_FUNCTION(phook) {
    ZEND_SECURE_ZERO(phook_globals, sizeof(*phook_globals));
}

zend_module_entry phook_module_entry = {
    STANDARD_MODULE_HEADER,
    "phook",
    ext_functions,
    PHP_MINIT(phook),
    PHP_MSHUTDOWN(phook),
    PHP_RINIT(phook),
    PHP_RSHUTDOWN(phook),
    PHP_MINFO(phook),
    PHP_PHOOK_VERSION,
    PHP_MODULE_GLOBALS(phook),
    PHP_GINIT(phook),
    NULL,
    NULL,
    STANDARD_MODULE_PROPERTIES_EX,
};

#ifdef COMPILE_DL_PHOOK
#ifdef ZTS
ZEND_TSRMLS_CACHE_DEFINE()
#endif
ZEND_GET_MODULE(phook)
#endif
