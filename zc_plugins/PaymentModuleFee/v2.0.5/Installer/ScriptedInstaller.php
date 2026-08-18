<?php
// ----
// Admin-level installation script for the "encapsulated" Payment Module Fee plugin for Zen Cart, by oldngrey.
// Copyright (C) 2026, Zen Cart team, OldNGrey BMH.
//
// Last updated: v2.0.5 (new)
// 2026-07-25 correctly remove non-plugin files and continue if they do not exist
//
use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    private string $configGroupTitle = 'Payment Module Fee';

    protected function executeInstall()
    {
        if (!$this->purgeOldFiles()) {
            return false;
        }

        return true;
    }

    // -----
    // Not used, initially, but included for the possibility of future upgrades!
    //
    protected function executeUpgrade($oldVersion)
    {
    }

    protected function executeUninstall()
    {
        parent::executeUninstall();
    }

    protected function purgeOldFiles(): bool
    {
        // -----
        // First, look for and remove the non-encapsulated version's 
        // files.
        //
        $template_dir = DIR_WS_TEMPLATE;
                           
        $files_to_check = [
            DIR_FS_CATALOG . 'includes/languages/english/modules/order_total/lang.ot_paymentmodulefee.php',
            DIR_FS_CATALOG . 'includes/languages/english/modules/order_total/ot_paymentmodulefee.php',
            
            DIR_FS_CATALOG . 'includes/modules/order_total/ot_paymentmodulefee.php',
            
            DIR_FS_CATALOG . $template_dir . "auto_loaders/loader_ot_paymentmodulefee.php",
            DIR_FS_CATALOG . $template_dir . "jscript/jquery/jquery_ot_paymentmodulefee.js",
            
            DIR_FS_CATALOG . 'includes/templates/template_default/auto_loaders/loader_ot_paymentmodulefee.php',
            DIR_FS_CATALOG . 'includes/templates/template_default/jscript/jquery/jquery_ot_paymentmodulefee.js',
            
            
        ];

        // Loop through and delete the files
        foreach ($files_to_check as $file) {
            if (file_exists($file ?? '') && is_writable($file ?? '')) {
                unlink($file);
            }
        }
        return true;
    }
}
