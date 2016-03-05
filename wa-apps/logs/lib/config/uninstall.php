<?php

wa('logs');
if (method_exists('logsHelper', 'setPhpLogSetting')) {
    logsHelper::setPhpLogSetting(false);
}
