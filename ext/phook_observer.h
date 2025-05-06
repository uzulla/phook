
#ifndef PHOOK_OBSERVER_H
#define PHOOK_OBSERVER_H

void phook_observer_init(INIT_FUNC_ARGS);
void observer_globals_init(void);
void observer_globals_cleanup(void);

bool add_observer(zend_string *cn, zend_string *fn, zval *pre_hook,
                  zval *post_hook);

#endif // PHOOK_OBSERVER_H
