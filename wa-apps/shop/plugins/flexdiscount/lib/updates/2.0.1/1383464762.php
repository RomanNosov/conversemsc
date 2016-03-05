<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
// Чистка мусора
$files = array(
    wa()->getAppPath("plugins/flexdiscount/lib/actions/shopFlexdiscountPluginSettings.action.php", "shop"),
    wa()->getAppPath("plugins/flexdiscount/wa-apps/", "shop"),
    wa()->getAppPath("plugins/flexdiscount/README.txt", "shop"),
);

try {
    foreach ($files as $file) {
        if (file_exists($file)) {
            waFiles::delete($file, true);
        }
    }
} catch (Exception $e) {
    
}