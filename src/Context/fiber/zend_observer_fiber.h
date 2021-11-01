#define FFI_SCOPE "OTEL_ZEND_OBSERVER_FIBER"

typedef void (*zend_observer_fiber_init_handler)(intptr_t initializing);
typedef void (*zend_observer_fiber_switch_handler)(intptr_t from, intptr_t to);
typedef void (*zend_observer_fiber_destroy_handler)(intptr_t destroying);

void zend_observer_fiber_init_register(zend_observer_fiber_init_handler handler);
void zend_observer_fiber_switch_register(zend_observer_fiber_switch_handler handler);
void zend_observer_fiber_destroy_register(zend_observer_fiber_destroy_handler handler);
