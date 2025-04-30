#!/bin/sh

script_name="init-workers"

# Set default values for Laravel automations
: "${APP_WORKERS_ENABLED:=false}"

if [ "$APP_WORKERS_ENABLED" = "true" ]; then
    touch /etc/s6-overlay/s6-rc.d/user/contents.d/horizon
    touch /etc/s6-overlay/s6-rc.d/user/contents.d/scheduler
fi

return 0
